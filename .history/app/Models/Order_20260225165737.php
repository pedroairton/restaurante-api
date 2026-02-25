<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'table_id',
        'status',
        'observations',
        'total',
    ];

    protected function casts(): array {
        return [
            'total' => 'decimal:2'
        ];
    }

    public function table(){
        return $this->belongsTo(Table::class);
    }

    public function items(){
        return $this->hasMany(OrderItem::class);
    }

    // scopes
    public function scopePaid($query){
        return $query->where('status', 'paid');
    }

    public function scopeNotCancelled($query){
        return $query->where('status', '!=', 'cancelled');
    }

    public function scopeInMonth($query, int $month, int $year){
        return $query->whereMonth('created_at', $month)->whereYear('created_at', $year);
    }

    public function scopeInPeriod($query, string $startDate, string $endDate){
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    // methods
    public function recalculateTotal(): void {
        $this->total = $this->items()->sum('total');
        $this->save();
    }
}
