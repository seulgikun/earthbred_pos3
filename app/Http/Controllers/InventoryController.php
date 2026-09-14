<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inventory;
use App\Models\InventoryLog;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Cache;

class InventoryController extends Controller
{
    /**
     * Display a listing of the inventory items.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $items = Inventory::all();
        
        // Calculate status dynamically for safety/consistency
        $items->map(function ($item) {
            if ($item->quantity == 0) {
                $item->status_label = 'Out of stock';
                $item->status_class = 'status-out-of-stock';
            } elseif ($item->quantity <= $item->min_threshold) {
                $item->status_label = 'Low Stock';
                $item->status_class = 'status-low-stock';
            } else {
                $item->status_label = 'In Stock';
                $item->status_class = 'status-in-stock';
            }
            return $item;
        });

        return response()->json($items);
    }

    /**
     * Add stock to an existing item (+Qty).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function addStock(Request $request, $id)
    {
        $validated = $request->validate([
            'quantity_added' => 'required|integer|min:1',
        ]);

        $item = Inventory::findOrFail($id);
        $oldQty = $item->quantity;
        $added = $validated['quantity_added'];
        $newQty = $oldQty + $added;

        $item->quantity = $newQty;
        $item->latest_issue_type = 'Restocked';
        $item->save();

        InventoryLog::create([
            'inventory_id' => $item->id,
            'quantity_changed' => $added,
            'issue_type' => 'Restocked',
            'notes' => 'Stock added manually'
        ]);

        // Clear POS cache so stock status updates on next page load
        Cache::forget('pos_products');

        // Audit Log for owner tracking
        AuditLog::record(
            'INVENTORY_UPDATE',
            "Stock restocked for '{$item->item_name}': {$oldQty} → {$newQty} (+{$added} units).",
            "Inventory: {$item->item_name}",
            $request
        );

        return response()->json([
            'success' => true,
            'message' => 'Stock added successfully!',
            'item' => $item
        ]);
    }

    /**
     * Edit/correct stock quantity and set custom issue type.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function editStock(Request $request, $id)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0',
            'issue_type' => 'required|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        $item = Inventory::findOrFail($id);
        $oldQty = $item->quantity;
        $newQty = $validated['quantity'];
        $diff = $newQty - $oldQty;

        // Save new values
        $item->quantity = $newQty;
        $item->latest_issue_type = $validated['issue_type'];
        $item->save();

        // Clear POS cache so stock status updates on next page load
        Cache::forget('pos_products');

        // Create audit log entry
        InventoryLog::create([
            'inventory_id' => $item->id,
            'quantity_changed' => $diff,
            'issue_type' => $validated['issue_type'],
            'notes' => $validated['notes'] ?? 'Manual stock correction'
        ]);

        // Audit Log for owner tracking
        $changeStr = $diff >= 0 ? "+{$diff}" : "{$diff}";
        AuditLog::record(
            'INVENTORY_UPDATE',
            "Stock edited for '{$item->item_name}' [{$validated['issue_type']}]: {$oldQty} → {$newQty} ({$changeStr} units). Notes: " . ($validated['notes'] ?? 'Manual stock correction'),
            "Inventory: {$item->item_name}",
            $request
        );

        return response()->json([
            'success' => true,
            'message' => 'Stock updated successfully!',
            'item' => $item
        ]);
    }

    /**
     * Retrieve transaction logs.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLogs()
    {
        $logs = InventoryLog::with('inventory')
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get();
            
        return response()->json($logs);
    }

    /**
     * Store a newly created item in the inventory.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeItem(Request $request)
    {
        $validated = $request->validate([
            'item_name' => 'required|string|max:255|unique:inventories,item_name',
            'category' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
            'min_threshold' => 'required|integer|min:1',
        ]);

        $item = Inventory::create([
            'item_name' => $validated['item_name'],
            'category' => $validated['category'],
            'quantity' => $validated['quantity'],
            'min_threshold' => $validated['min_threshold'],
            'latest_issue_type' => 'Restocked'
        ]);

        InventoryLog::create([
            'inventory_id' => $item->id,
            'quantity_changed' => $item['quantity'],
            'issue_type' => 'Restocked',
            'notes' => 'Initial setup of new inventory item'
        ]);

        // Audit Log for owner tracking
        AuditLog::record(
            'INVENTORY_UPDATE',
            "New inventory item created: '{$item->item_name}' (Category: {$item->category}) with initial quantity {$item->quantity} (Min threshold: {$item->min_threshold}).",
            "Inventory: {$item->item_name}",
            $request
        );

        return response()->json([
            'success' => true,
            'message' => 'New item added successfully!',
            'item' => $item
        ]);
    }
    public function destroy($id)
    {
        $item = Inventory::findOrFail($id);
        $itemName = $item->item_name;
        $item->delete(); // Soft delete into archive

        // Audit Log for owner tracking
        AuditLog::record(
            'INVENTORY_ARCHIVE',
            "Inventory item '{$itemName}' was archived (soft deleted).",
            "Inventory: {$itemName}",
            request()
        );

        return response()->json([
            'success' => true,
            'message' => "Item '{$itemName}' moved to archive successfully!"
        ]);
    }

    /**
     * Retrieve all soft-deleted / archived inventory items.
     */
    public function getArchived()
    {
        $archived = Inventory::onlyTrashed()->orderBy('deleted_at', 'desc')->get();
        return response()->json([
            'success' => true,
            'items' => $archived
        ]);
    }

    /**
     * Restore an archived inventory item.
     */
    public function restoreItem($id)
    {
        $item = Inventory::onlyTrashed()->findOrFail($id);
        $itemName = $item->item_name;
        $item->restore();

        // Audit Log for owner tracking
        AuditLog::record(
            'INVENTORY_RESTORE',
            "Archived inventory item '{$itemName}' was restored to active inventory.",
            "Inventory: {$itemName}",
            request()
        );

        return response()->json([
            'success' => true,
            'message' => "Item '{$itemName}' restored successfully!"
        ]);
    }
}
