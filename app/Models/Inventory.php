<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_name',
        'category',
        'quantity',
        'min_threshold',
        'latest_issue_type'
    ];

    public function logs()
    {
        return $this->hasMany(InventoryLog::class);
    }
}
