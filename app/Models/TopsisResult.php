<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TopsisResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'period_start',
        'period_end',
        'generated_by',
        'criteria_snapshot',
        'decision_matrix',
        'normalized_matrix',
        'weighted_matrix',
        'ideal_positive',
        'ideal_negative',
        'distances',
        'preferences',
    ];

    protected function casts(): array
    {
        return [
            'criteria_snapshot' => 'array',
            'decision_matrix' => 'array',
            'normalized_matrix' => 'array',
            'weighted_matrix' => 'array',
            'ideal_positive' => 'array',
            'ideal_negative' => 'array',
            'distances' => 'array',
            'preferences' => 'array',
        ];
    }

    public function generator()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
