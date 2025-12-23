<?php

namespace App\Models;

use App\Governorate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplaintSuggestion extends Model
{
    protected $table = 'complaints_suggestions';

    protected $fillable = [
        'type',
        'name',
        'phone',
        'email',
        'governorate',
        'generator_id',
        'subject',
        'message',
        'image',
        'status',
        'response',
        'responded_by',
        'responded_at',
        'tracking_code',
    ];

    protected function casts(): array
    {
        return [
            'responded_at' => 'datetime',
            'governorate' => Governorate::class,
        ];
    }

    /**
     * المستخدم الذي رد على الطلب
     */
    public function responder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responded_by');
    }

    /**
     * المولد المرتبط بالطلب
     */
    public function generator(): BelongsTo
    {
        return $this->belongsTo(Generator::class);
    }

    /**
     * إنشاء رمز تتبع فريد
     */
    public static function generateTrackingCode(): string
    {
        do {
            $code = 'CS-'.strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
        } while (self::where('tracking_code', $code)->exists());

        return $code;
    }

    /**
     * الحصول على حالة الطلب بالعربية
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'قيد الانتظار',
            'in_progress' => 'قيد المعالجة',
            'resolved' => 'تم الحل',
            'rejected' => 'مرفوض',
            default => 'غير معروف',
        };
    }

    /**
     * الحصول على نوع الطلب بالعربية
     */
    public function getTypeLabelAttribute(): string
    {
        return $this->type === 'complaint' ? 'شكوى' : 'مقترح';
    }

    /**
     * الحصول على اسم المحافظة
     */
    public function getGovernorateLabel(): ?string
    {
        return $this->governorate?->label();
    }
}
