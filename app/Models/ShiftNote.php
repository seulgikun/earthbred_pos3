<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftNote extends Model
{
    use HasFactory;

    protected $fillable = ['note', 'cashier_name', 'category', 'is_done'];
}
