<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TopsisCriterion extends Model
{
    use HasFactory;

    protected $table = 'topsis_criteria';

    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'weight',
        'data_source',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:4',
            'is_active' => 'boolean',
        ];
    }
}
