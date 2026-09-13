<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::all();
        return view('manager-products', compact('products'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'price' => 'required|numeric',
            'discounted_price' => 'nullable|numeric',
            'picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        try {
            $data = $request->except('picture');

            if ($request->hasFile('picture')) {
                $file = $request->file('picture');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('images'), $filename);
                $data['picture'] = $filename;
            }

            $newProduct = Product::create($data);

            // Audit Log entry
            AuditLog::record(
                'PRODUCT_CREATE',
                "Added new menu product '{$newProduct->name}' (Category: {$newProduct->category}, Price: ₱" . number_format($newProduct->price, 2) . ")",
                "Product #{$newProduct->id}: {$newProduct->name}",
                $request
            );

            \Illuminate\Support\Facades\Cache::forget('pos_products');

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'price' => 'required|numeric',
            'discounted_price' => 'nullable|numeric',
            'picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        try {
            $product = Product::findOrFail($id);

            $data = $request->except('picture');

            if ($request->hasFile('picture')) {
                // Delete old picture if needed, but keeping it simple for now
                $file = $request->file('picture');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('images'), $filename);
                $data['picture'] = $filename;
            }

            $oldPrice = (float)$product->price;
            $oldDiscounted = $product->discounted_price !== null ? (float)$product->discounted_price : null;
            $newPrice = (float)$request->price;
            $newDiscounted = $request->discounted_price !== null && $request->discounted_price !== '' ? (float)$request->discounted_price : null;

            $product->update($data);

            // Track price update changes for Audit Log
            $changes = [];
            if ($oldPrice != $newPrice) {
                $changes[] = "Base price adjusted from ₱" . number_format($oldPrice, 2) . " to ₱" . number_format($newPrice, 2);
            }
            if ($oldDiscounted != $newDiscounted) {
                $oldDiscText = $oldDiscounted !== null ? '₱' . number_format($oldDiscounted, 2) : 'None';
                $newDiscText = $newDiscounted !== null ? '₱' . number_format($newDiscounted, 2) : 'None';
                $changes[] = "Discounted price changed from {$oldDiscText} to {$newDiscText}";
            }

            $action = !empty($changes) ? 'PRICE_UPDATE' : 'PRODUCT_UPDATE';
            $details = !empty($changes) 
                ? "Updated pricing for '{$product->name}': " . implode(', ', $changes)
                : "Updated product details for '{$product->name}' (Category: {$product->category})";

            AuditLog::record(
                $action,
                $details,
                "Product #{$product->id}: {$product->name}",
                $request
            );

            \Illuminate\Support\Facades\Cache::forget('pos_products');

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $productName = $product->name;
        $productCat = $product->category;

        $product->delete();

        \Illuminate\Support\Facades\Cache::forget('pos_products');

        AuditLog::record(
            'PRODUCT_DELETE',
            "Deleted menu product '{$productName}' (Category: {$productCat})",
            "Product #{$id}: {$productName}",
            $request
        );

        return response()->json(['success' => true, 'message' => 'Product deleted successfully']);
    }
}
