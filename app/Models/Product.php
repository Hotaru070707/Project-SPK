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
        'slug',
        'barcode',
        'description',
        'cost_price',
        'sell_price',
        'stock',
        'min_stock',
        'supplier_lead_time_days',
        'unit',
        'image',
        'is_active',
    ];

    protected $appends = [
        'image_url',
    ];

    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'sell_price' => 'decimal:2',
            'stock' => 'integer',
            'min_stock' => 'integer',
            'supplier_lead_time_days' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    // Helper attribute margin
    public function getMarginAttribute(): float
    {
        if ($this->sell_price <= 0) {
            return 0;
        }

        return round((($this->sell_price - $this->cost_price) / $this->sell_price) * 100, 1);
    }

    // Is low stock?
    public function getIsLowStockAttribute(): bool
    {
        return $this->stock <= $this->min_stock;
    }

    // Accessor for product image URL
    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }

        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }

        return asset('storage/'.$this->image);
    }
}
