<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'price',
        'description',
        'is_active'
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean'
        ];
    }

    public function category(){
        return $this->belongsTo(Category::class);
    }
    
    public function orderItems(){
        return $this->hasMany(OrderItem::class);
    }

    // scopes
    public function scopeActive($query){
        return $query->where('is_active', true);
    }
}
