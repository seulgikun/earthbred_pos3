<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Display a listing of the audit logs.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = AuditLog::query()->orderBy('created_at', 'desc');

        // Tab Category Filtering
        $category = strtolower(trim($request->input('category', 'all')));
        if ($category === 'inventory') {
            $query->where(function ($q) {
                $q->where('action', 'like', 'INVENTORY%')
                  ->orWhere('action', 'like', '%STOCK%');
            });
        } elseif ($category === 'security') {
            $query->where(function ($q) {
                $q->where('action', 'like', '%PIN%')
                  ->orWhere('action', 'like', '%PASSWORD%')
                  ->orWhere('action', 'like', '%AUTH%')
                  ->orWhere('action', 'like', '%SECURITY%')
                  ->orWhere('action', 'like', '%LOGIN%');
            });
        } elseif ($category === 'transactions') {
            $query->where(function ($q) {
                $q->where('action', 'VOID_OVERRIDE')
                  ->orWhere('action', 'like', '%DISCOUNT%')
                  ->orWhere('action', 'like', '%ORDER%')
                  ->orWhere('action', 'like', '%TRANSACTION%')
                  ->orWhere('action', 'like', '%PRICE%')
                  ->orWhere('action', 'like', '%PRODUCT%');
            });
        }

        // Specific Action Dropdown Filter
        if ($request->has('action') && $request->action !== 'ALL' && !empty($request->action)) {
            $query->where('action', $request->action);
        }

        // Search Filter
        if ($request->has('search') && !empty($request->search)) {
            $search = '%' . trim($request->search) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('details', 'like', $search)
                  ->orWhere('target', 'like', $search)
                  ->orWhere('manager_name', 'like', $search)
                  ->orWhere('manager_id', 'like', $search)
                  ->orWhere('action', 'like', $search);
            });
        }

        // Server-Side Pagination (Default: 10 per page)
        $perPage = (int) $request->input('per_page', 10);
        if ($perPage <= 0) $perPage = 10;
        $page = (int) $request->input('page', 1);

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        $items = collect($paginator->items())->map(function ($log) {
            if (!$log->manager_name || in_array($log->manager_name, ['Store Manager', 'Earthbred Owner', 'Earthbred Manager', 'null'])) {
                if ($log->manager_role === 'owner') {
                    $owner = \App\Models\User::where('role', 'owner')->first();
                    $log->manager_name = $owner ? $owner->name : 'Christopher Lim';
                } elseif ($log->manager_role === 'manager') {
                    $mgr = \App\Models\User::where('role', 'manager')->latest()->first();
                    $log->manager_name = $mgr ? $mgr->name : 'junric limpangog';
                }
            }
            return $log;
        });

        return response()->json([
            'success' => true,
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'from' => $paginator->firstItem() ?: 0,
            'to' => $paginator->lastItem() ?: 0,
            'count' => $paginator->count(),
            'logs' => $items,
            'data' => $items
        ]);
    }
}
