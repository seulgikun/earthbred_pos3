<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\AuditLog;
use Carbon\Carbon;

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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.customer_name' => 'nullable|string|max:255',
            'items.*.product_name' => 'required|string|max:255',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.addons' => 'nullable|array',
            'items.*.addons_total' => 'nullable|numeric|min:0',
            'items.*.item_total' => 'required|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
            'discount_percent' => 'required|integer|min:0|max:100',
            'discount_amount' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'payment_method' => 'required|string|in:cash,gcash',
            'cashier_id' => 'nullable|integer',
            'cashier_name' => 'nullable|string|max:255',
        ]);

        $order = Order::create([
            'subtotal' => $validated['subtotal'],
            'discount_percent' => $validated['discount_percent'],
            'discount_amount' => $validated['discount_amount'],
            'total' => $validated['total'],
            'payment_method' => $validated['payment_method'],
            'status' => 'pending',
            'cashier_id' => $validated['cashier_id'] ?? null,
            'cashier_name' => $validated['cashier_name'] ?? null,
        ]);

        foreach ($validated['items'] as $item) {
            $isFood = false;
            $product = Product::where('name', $item['product_name'])->first();
            if ($product && in_array(strtolower(trim($product->category)), ['foods', 'food'])) {
                $isFood = true;
            }

            OrderItem::create([
                'order_id' => $order->id,
                'customer_name' => $item['customer_name'] ?? null,
                'product_name' => $item['product_name'],
                'price' => $item['price'],
                'quantity' => $item['quantity'],
                'addons' => $isFood ? [] : ($item['addons'] ?? []),
                'addons_total' => $isFood ? 0 : ($item['addons_total'] ?? 0),
                'item_total' => $item['item_total'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order processed successfully!',
            'order_id' => $order->id,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:pending,completed,void'
        ]);

        $order = Order::findOrFail($id);
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

