<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuthorizedPhone extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'phone',
        'name',
        'notes',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * التحقق من أن الرقم مصرح به
     */
    public static function isAuthorized(string $phone): bool
    {
        // تنظيف الرقم من المسافات والرموز
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        
        return static::where('phone', $cleanPhone)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * الحصول على معلومات الرقم المصرح به
     */
    public static function getAuthorizedPhone(string $phone): ?self
    {
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        
        return static::where('phone', $cleanPhone)
            ->where('is_active', true)
            ->first();
    }
}
