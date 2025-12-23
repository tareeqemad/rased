<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FuelTank extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'generator_id',
        'capacity',
        'location',
        'filtration_system_available',
        'condition',
        'material',
        'usage',
        'measurement_method',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'filtration_system_available' => 'boolean',
        ];
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(Generator::class);
    }
}
