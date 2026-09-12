<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ShiftNoteController extends Controller
{
    public function index()
    {
        $notes = \App\Models\ShiftNote::orderBy('created_at', 'desc')->get();
        return view('shift-notes', compact('notes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'note' => 'required|string',
            'category' => 'required|string|in:General,Equipment,Complaint,Task'
        ]);

        $cashierName = $request->input('cashier_name');
        if (empty($cashierName)) {
            $cashierName = 'Cashier';
        }

        \App\Models\ShiftNote::create([
            'note' => $request->note,
            'cashier_name' => $cashierName,
            'category' => $request->category,
            'is_done' => false
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
                'id' => $note->id
            ]);
        }

        return redirect()->back();
    }
}
