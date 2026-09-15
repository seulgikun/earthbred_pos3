<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class ShiftNoteController extends Controller
{
    public function index()
    {
        $notes = \App\Models\ShiftNote::with(['cashier', 'categoryRecord'])
            ->orderBy('created_at', 'desc')
            ->get();
        return view('shift-notes', compact('notes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'note'        => 'required|string',
            'category'    => 'required|string|in:General,Equipment,Complaint,Task',
            'cashier_id'  => 'nullable|integer|exists:users,id',
        ]);

        // Resolve cashier: prefer authenticated user, then request cashier_id, then session
        $authUser = Auth::user();
        $cashierId = $authUser ? $authUser->id : ($request->input('cashier_id') ?? session('user_id'));

        // Resolve category → category_id in the categories lookup table
        $categoryName = $request->category;
        $category = Category::findOrCreate($categoryName, 'shift_note');

        \App\Models\ShiftNote::create([
            'note'        => $request->note,
            'cashier_id'  => $cashierId,
            'category_id' => $category->id,
            'is_done'     => false,
        ]);

        return redirect()->back();
    }

    public function markDone(Request $request, $id)
    {
        $note = \App\Models\ShiftNote::findOrFail($id);
        $note->is_done = true;
        $note->save();

        if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Shift note resolved successfully.',
                'id'      => $note->id,
            ]);
        }

        return redirect()->back();
    }
}
