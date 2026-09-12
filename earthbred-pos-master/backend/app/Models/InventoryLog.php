<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_id',
        'quantity_changed',
        'issue_type',
        'notes'
    ];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
}
