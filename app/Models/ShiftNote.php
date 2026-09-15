<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'note',
        'cashier_id',   // replaces cashier_name (3NF fix)
        'category_id',  // replaces category string (3NF fix)
        'is_done',
    ];

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function categoryRecord()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Backward-compat accessor: $note->cashier_name
     */
    public function getCashierNameAttribute(): string
    {
        return $this->cashier->name ?? 'Staff';
    }

    /**
     * Backward-compat mutator: $note->cashier_name = 'Juan'
     */
    public function setCashierNameAttribute(?string $value): void
    {
        if (!empty($value) && !$this->cashier_id) {
            $user = User::whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($value))])->first();
            if ($user) {
                $this->attributes['cashier_id'] = $user->id;
            }
        }
    }

    /**
     * Backward-compat accessor: $note->category
     */
    public function getCategoryAttribute(): string
    {
        return $this->categoryRecord->name ?? 'General';
    }

    /**
     * Backward-compat mutator: $note->category = 'General'
     */
    public function setCategoryAttribute(?string $value): void
    {
        if (!empty($value)) {
            $cat = Category::findOrCreate($value, 'shift_note');
            $this->attributes['category_id'] = $cat->id;
        }
    }
}
