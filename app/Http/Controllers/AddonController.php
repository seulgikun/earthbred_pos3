<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Addon;
use App\Models\Category;
use Illuminate\Support\Facades\Cache;

class AddonController extends Controller
{
    public function index(Request $request)
    {
        $query = Addon::with('categoryRecord');

        if ($request->has('category') && $request->category) {
            $cat = strtolower(trim($request->category));
            // Filter by category name via the categories relationship, or 'all' type
            $query->whereHas('categoryRecord', function ($q) use ($cat) {
                $q->whereRaw('LOWER(TRIM(name)) = ?', [$cat]);
            })->orWhereHas('categoryRecord', function ($q) {
                $q->whereRaw('LOWER(TRIM(name)) = ?', ['all']);
            });
        }

        $addons = $query->orderBy('name', 'asc')->get();
        return response()->json($addons);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'price'    => 'required|numeric|min:0',
            'category' => 'nullable|string|in:food,drinks,all',
        ]);

        if (empty($validated['category'])) {
            $validated['category'] = 'drinks';
        }

        // category mutator in Addon model handles category → category_id resolution
        $addon = new Addon();
        $addon->name     = $validated['name'];
        $addon->price    = $validated['price'];
        $addon->category = $validated['category'];
        $addon->save();

        Cache::forget('pos_addons');

        return response()->json([
            'success' => true,
            'message' => 'Add-on created successfully',
            'addon'   => $addon->fresh()->load('categoryRecord')
        ]);
    }

    public function update(Request $request, $id)
    {
        $addon = Addon::findOrFail($id);

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'price'    => 'required|numeric|min:0',
            'category' => 'nullable|string|in:food,drinks,all',
        ]);

        if (empty($validated['category'])) {
            $validated['category'] = $addon->category ?: 'drinks';
        }

        $addon->name     = $validated['name'];
        $addon->price    = $validated['price'];
        $addon->category = $validated['category'];
        $addon->save();

        Cache::forget('pos_addons');

        return response()->json([
            'success' => true,
            'message' => 'Add-on updated successfully',
            'addon'   => $addon->fresh()->load('categoryRecord')
        ]);
    }

    public function destroy($id)
    {
        $addon = Addon::findOrFail($id);
        $addon->delete();
        Cache::forget('pos_addons');

        return response()->json([
            'success' => true,
            'message' => 'Add-on removed successfully'
        ]);
    }
}
