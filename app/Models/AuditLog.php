<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditLog extends Model
{
    use HasFactory;

    protected $table = 'audit_logs';

    protected $fillable = [
        'user_id',          // 3NF fix: direct FK to users table
        'manager_id',
        'manager_name',
        'manager_role',
        'action',
        'target',
        'details',
        'ip_address',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Record a sensitive store action in the audit log.
     *
     * @param string $action       e.g. 'VOID_OVERRIDE', 'PRICE_UPDATE', 'PRODUCT_CREATE', 'VOID_PIN_UPDATE'
     * @param string $details      Human-readable description of change
     * @param string|null $target  Subject of the action, e.g. 'Order #15', 'Product: Americano'
     * @param Request|null $request Active HTTP request to extract actor identity & IP
     * @return static
     */
    public static function record(string $action, string $details, ?string $target = null, ?Request $request = null): self
    {
        $managerId = null;
        $managerName = null;
        $managerRole = null;
        $userId = null;
        $ip = '127.0.0.1';

        if ($request) {
            $ip = $request->ip() ?: '127.0.0.1';
            
            // Extract from custom headers sent by client, request body, or session
            $managerId = $request->header('X-User-Id') ?: $request->input('user_id') ?: session('user_id');
            $managerName = $request->header('X-User-Name') ?: $request->input('user_name') ?: session('user_name');
            $managerRole = $request->header('X-User-Role') ?: $request->input('user_role') ?: session('user_role');
        }

        // If legacy placeholder or missing, resolve fresh from DB
        if ($managerId && is_numeric($managerId)) {
            $u = User::find($managerId);
            if ($u) {
                $userId = $u->id;
                $managerName = $u->name;
                $managerRole = $u->role;
            }
        }

        if (!$managerName || in_array($managerName, ['Store Manager', 'Earthbred Owner', 'Earthbred Manager', 'null'])) {
            if ($managerRole === 'owner') {
                $owner = User::where('role', 'owner')->first();
                if ($owner) {
                    $userId = $owner->id;
                    $managerName = $owner->name;
                    $managerId = (string) $owner->id;
                }
            } elseif ($managerRole === 'manager') {
                $mgr = User::where('role', 'manager')->latest()->first();
                if ($mgr) {
                    $userId = $mgr->id;
                    $managerName = $mgr->name;
                    $managerId = (string) $mgr->id;
                }
            } else {
                $owner = User::where('role', 'owner')->first();
                if ($owner) {
                    $userId = $owner->id;
                    $managerName = $owner->name;
                    $managerRole = 'owner';
                    $managerId = (string) $owner->id;
                } else {
                    $managerName = 'Earthbred Staff';
                    $managerRole = 'staff';
                    $managerId = '1';
                }
            }
        }

        return self::create([
            'user_id' => $userId ?: (is_numeric($managerId) ? (int)$managerId : null),
            'manager_id' => $managerId ?: '1',
            'manager_name' => $managerName,
            'manager_role' => $managerRole ?: 'staff',
            'action' => $action,
            'target' => $target,
            'details' => $details,
            'ip_address' => $ip,
        ]);
    }
}
