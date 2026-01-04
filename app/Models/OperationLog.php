<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OperationLog extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'generator_id',
        'operator_id',
        'sequence',
        'operation_date',
        'start_time',
        'end_time',
        'load_percentage',
        'fuel_meter_start',
        'fuel_meter_end',
        'fuel_consumed',
        'energy_meter_start',
        'energy_meter_end',
        'energy_produced',
        'electricity_tariff_price',
        'operational_notes',
        'malfunctions',
    ];

    protected function casts(): array
    {
        return [
            'operation_date' => 'date',
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'load_percentage' => 'decimal:2',
            'fuel_meter_start' => 'decimal:2',
            'fuel_meter_end' => 'decimal:2',
            'fuel_consumed' => 'decimal:2',
            'energy_meter_start' => 'decimal:2',
            'energy_meter_end' => 'decimal:2',
            'energy_produced' => 'decimal:2',
            'electricity_tariff_price' => 'decimal:4',
        ];
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(Generator::class);
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class);
    }
}
