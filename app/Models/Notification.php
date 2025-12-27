<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'link',
        'read',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * إنشاء إشعار جديد
     */
    public static function createNotification(int $userId, string $type, string $title, string $message, ?string $link = null): self
    {
        return self::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'read' => false,
        ]);
    }

    /**
     * تعليم الإشعار كمقروء
     */
    public function markAsRead(): void
    {
        if (!$this->read) {
            $this->update([
                'read' => true,
                'read_at' => now(),
            ]);
        }
    }

    /**
     * الحصول على أيقونة حسب النوع
     */
    public function getIconAttribute(): string
    {
        return match($this->type) {
            'maintenance_needed' => 'bi-tools',
            'complaint_unanswered' => 'bi-chat-left-text',
            'compliance_expiring' => 'bi-shield-exclamation',
            'generator_added' => 'bi-lightning-charge-fill',
            'operation_log_added' => 'bi-journal-text',
            default => 'bi-bell',
        };
    }

    /**
     * الحصول على لون حسب النوع
     */
    public function getColorAttribute(): string
    {
        return match($this->type) {
            'maintenance_needed' => 'warning',
            'complaint_unanswered' => 'info',
            'compliance_expiring' => 'danger',
            'generator_added' => 'success',
            'operation_log_added' => 'primary',
            default => 'primary',
        };
    }
}
