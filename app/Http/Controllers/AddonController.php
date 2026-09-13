<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Addon;
use Illuminate\Support\Facades\Cache;

class AddonController extends Controller
{
    public function index(Request $request)
    {
        $query = Addon::query();
        if ($request->has('category') && $request->category) {
            $cat = strtolower(trim($request->category));
            $query->where(function($q) use ($cat) {
                $q->where('category', $cat)->orWhere('category', 'all');
            });
        }
        $addons = $query->orderBy('name', 'asc')->get();
        return response()->json($addons);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'category' => 'nullable|string|in:food,drinks,all',
        ]);

        if (empty($validated['category'])) {
            $validated['category'] = 'drinks';
        }

        $addon = Addon::create($validated);
        Cache::forget('pos_addons');

        return response()->json([
            'success' => true,
            'message' => 'Add-on created successfully',
            'addon' => $addon
        ]);
    }

    public function update(Request $request, $id)
    {
        $addon = Addon::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'category' => 'nullable|string|in:food,drinks,all',
        ]);

        if (empty($validated['category'])) {
            $validated['category'] = $addon->category ?: 'drinks';
        }

        $addon->update($validated);
        Cache::forget('pos_addons');

        return response()->json([
            'success' => true,
            'message' => 'Add-on updated successfully',
            'addon' => $addon
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
