<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Addon;
use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        
        $orders = Order::with('items')
            ->whereDate('created_at', $today)
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Calculate total sales for today (completed and pending, exclude void)
        $totalSales = $orders->whereIn('status', ['pending', 'completed'])->sum('total');

        return view('queue', compact('orders', 'totalSales'));
    }

    /**
     * Live queue polling endpoint — returns today's orders as JSON.
     */
    public function liveQueue()
    {
        $today = Carbon::today();
        $orders = Order::with('items')
            ->whereDate('created_at', $today)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($order) {
                return [
                    'id'               => $order->id,
                    'status'           => $order->status,
                    'subtotal'         => (float) $order->subtotal,
                    'discount_percent' => (int) ($order->discount_percent ?? 0),
                    'discount_amount'  => (float) ($order->discount_amount ?? 0),
                    'total'            => (float) $order->total,
                    'payment_method'   => strtoupper($order->payment_method),
                    'cashier_name'     => $order->cashier_name ?: 'Earthbred Staff',
                    'created_at'       => $order->created_at->format('h:i A'),
                    'created_at_full'  => $order->created_at->format('M d, Y h:i A'),
                    'items'            => $order->items->map(fn($i) => [
                        'product_name'  => $i->product_name,
                        'customer_name' => $i->customer_name,
                        'quantity'      => $i->quantity,
                        'price'         => (float) ($i->price ?? 0),
                        'item_total'    => (float) $i->item_total,
                        'addons'        => is_array($i->addons) ? (isset($i->addons[0]['name']) ? array_column($i->addons, 'name') : $i->addons) : [],
                    ])->toArray(),
                ];
            });

        $totalSales = $orders->whereIn('status', ['pending', 'completed'])->sum('total');

        return response()->json([
            'success'     => true,
            'orders'      => $orders->values(),
            'total_sales' => (float) $totalSales,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.customer_name' => 'nullable|string|max:255',
            'items.*.product_name' => 'required|string|max:255',
            'items.*.price' => 'nullable|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.addons' => 'nullable|array',
            'items.*.addons_total' => 'nullable|numeric|min:0',
            'items.*.item_total' => 'nullable|numeric|min:0',
            'subtotal' => 'nullable|numeric|min:0',
            'discount_percent' => 'required|integer|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'total' => 'nullable|numeric|min:0',
            'payment_method' => 'required|string|in:cash,gcash',
            'cashier_id' => 'nullable|integer',
            'cashier_name' => 'nullable|string|max:255',
        ]);

        // Identify authenticated cashier / user
        $authUser = Auth::user();
        $cashierId = $authUser ? $authUser->id : ($validated['cashier_id'] ?? session('user_id'));
        $cashierName = $authUser ? $authUser->name : ($validated['cashier_name'] ?? session('user_name', 'Cashier'));

        // Preload DB products and addons for authoritative server-side price calculation
        $productNames = collect($validated['items'])->pluck('product_name')->unique();
        $products = Product::whereIn('name', $productNames)->get()->keyBy('name');
        $allAddons = Addon::all()->keyBy('name');

        $calculatedSubtotal = 0;
        $orderItemsData = [];

        foreach ($validated['items'] as $item) {
            $product = $products->get($item['product_name']);
            $isFood = false;

            if ($product) {
                $category = strtolower(trim((string)$product->category));
                if (in_array($category, ['foods', 'food'])) {
                    $isFood = true;
                }
                // Determine unit price from DB record
                $unitPrice = (float) ($product->discounted_price > 0 ? $product->discounted_price : $product->price);
            } else {
                $unitPrice = (float) ($item['price'] ?? 0);
            }

            // Calculate addons strictly from database prices
            $addonsTotal = 0;
            $processedAddons = [];

            if (!$isFood && !empty($item['addons']) && is_array($item['addons'])) {
                foreach ($item['addons'] as $addonRaw) {
                    $addonName = is_array($addonRaw) ? ($addonRaw['name'] ?? '') : (string) $addonRaw;
                    $dbAddon = $allAddons->get($addonName);
                    $addonPrice = $dbAddon ? (float) $dbAddon->price : (float) (is_array($addonRaw) ? ($addonRaw['price'] ?? 0) : 0);
                    $addonsTotal += $addonPrice;
                    $processedAddons[] = [
                        'name' => $addonName,
                        'price' => $addonPrice
                    ];
                }
            }

            $qty = (int) $item['quantity'];
            $itemTotal = round(($unitPrice + $addonsTotal) * $qty, 2);
            $calculatedSubtotal += $itemTotal;

            $orderItemsData[] = [
                'customer_name' => $item['customer_name'] ?? null,
                'product_name' => $item['product_name'],
                'price' => $unitPrice,
                'quantity' => $qty,
                'addons' => $isFood ? [] : $processedAddons,
                'addons_total' => $isFood ? 0 : $addonsTotal,
                'item_total' => $itemTotal,
            ];
        }

        $discountPercent = (int) $validated['discount_percent'];
        $calculatedDiscountAmount = round(($calculatedSubtotal * $discountPercent) / 100, 2);
        $calculatedTotal = max(0, round($calculatedSubtotal - $calculatedDiscountAmount, 2));

        $order = DB::transaction(function () use ($calculatedSubtotal, $discountPercent, $calculatedDiscountAmount, $calculatedTotal, $validated, $cashierId, $cashierName, $orderItemsData) {
            $createdOrder = Order::create([
                'subtotal' => $calculatedSubtotal,
                'discount_percent' => $discountPercent,
                'discount_amount' => $calculatedDiscountAmount,
                'total' => $calculatedTotal,
                'payment_method' => $validated['payment_method'],
                'status' => 'pending',
                'cashier_id' => $cashierId,
                'cashier_name' => $cashierName,
            ]);

            foreach ($orderItemsData as $itemData) {
                $itemData['order_id'] = $createdOrder->id;
                OrderItem::create($itemData);
            }

            return $createdOrder;
        });

        return response()->json([
            'success' => true,
            'message' => 'Order processed successfully!',
            'order_id' => $order->id,
            'total' => $order->total,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:pending,completed,void',
            'pin' => 'nullable|string|digits:4'
        ]);

        $order = Order::findOrFail($id);

        // Strictly verify Void PIN if attempting to void an order
        if ($validated['status'] === 'void') {
            $pin = trim((string) $request->input('pin', ''));
            if (empty($pin)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Void authorization PIN is required to void this order.'
                ], 422);
            }

            $owner = \App\Models\User::where('role', 'owner')->first();
            if (!$owner || !$owner->void_pin || !\Illuminate\Support\Facades\Hash::check($pin, $owner->void_pin)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Void Authorization PIN. Action denied.'
                ], 403);
            }
        }

        $order->status = $validated['status'];
        $order->save();

        // Audit Log entry when an order is voided
        if ($validated['status'] === 'void') {
            $itemsCount = $order->items()->count();
            $cashier = $order->cashier_name ?: 'Cashier';
            $details = "Void override executed for Order #{$order->id} (Amount: ₱" . number_format($order->total, 2) . ", Items: {$itemsCount}, Payment: " . strtoupper($order->payment_method) . ", Originating Cashier: {$cashier}).";

            AuditLog::record(
                'VOID_OVERRIDE',
                $details,
                "Order #{$order->id}",
                $request
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully!',
            'status' => $order->status
        ]);
    }
}

