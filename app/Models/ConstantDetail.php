<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConstantDetail extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'constant_master_id',
        'parent_detail_id',
        'label',
        'code',
        'value',
        'notes',
        'is_active',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function master(): BelongsTo
    {
        return $this->belongsTo(ConstantMaster::class, 'constant_master_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ConstantDetail::class, 'parent_detail_id');
    }

    public function children(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ConstantDetail::class, 'parent_detail_id')->where('is_active', true)->orderBy('order');
    }

    /**
     * الحصول على تفاصيل ثابت معين
     */
    public static function getByConstantNumber(int $constantNumber): \Illuminate\Support\Collection
    {
        $master = ConstantMaster::findByNumber($constantNumber);

        if (! $master) {
            return collect();
        }

        return $master->details;
    }
}
