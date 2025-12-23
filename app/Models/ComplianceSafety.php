<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ComplianceSafety extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'operator_id',
        'safety_certificate_status',
        'last_inspection_date',
        'inspection_authority',
        'inspection_result',
        'violations',
    ];

    protected function casts(): array
    {
        return [
            'last_inspection_date' => 'date',
        ];
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class);
    }
}
