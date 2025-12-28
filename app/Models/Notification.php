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
     * Create a new notification for the specified user
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
     * Send notification to operator owner and all employees and technicians
     */
    public static function notifyOperatorUsers(\App\Models\Operator $operator, string $type, string $title, string $message, ?string $link = null): void
    {
        if ($operator->owner_id) {
            self::createNotification($operator->owner_id, $type, $title, $message, $link);
        }

        $operator->users()
            ->whereIn('role', [\App\Role::Employee, \App\Role::Technician])
            ->each(fn($user) => self::createNotification($user->id, $type, $title, $message, $link));
    }

    /**
     * Send notification to all super admins in the system
     */
    public static function notifySuperAdmins(string $type, string $title, string $message, ?string $link = null): void
    {
        \App\Models\User::where('role', \App\Role::SuperAdmin)
            ->each(fn($user) => self::createNotification($user->id, $type, $title, $message, $link));
    }

    /**
     * Mark notification as read and set read timestamp
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
     * Get icon class based on notification type
     */
    public function getIconAttribute(): string
    {
        return match($this->type) {
            'maintenance_needed' => 'bi-tools',
            'complaint_unanswered' => 'bi-chat-left-text',
            'compliance_expiring' => 'bi-shield-exclamation',
            'generator_added' => 'bi-lightning-charge-fill',
            'generator_updated' => 'bi-lightning-charge-fill',
            'operation_log_added' => 'bi-journal-text',
            'maintenance_added' => 'bi-tools',
            'maintenance_updated' => 'bi-tools',
            'fuel_efficiency_added' => 'bi-fuel-pump',
            'fuel_efficiency_updated' => 'bi-fuel-pump',
            'compliance_added' => 'bi-shield-check',
            'compliance_updated' => 'bi-shield-check',
            'operator_added' => 'bi-building',
            'user_added' => 'bi-person-plus',
            default => 'bi-bell',
        };
    }

    /**
     * Get color class based on notification type
     */
    public function getColorAttribute(): string
    {
        return match($this->type) {
            'maintenance_needed' => 'warning',
            'complaint_unanswered' => 'info',
            'compliance_expiring' => 'danger',
            'generator_added' => 'success',
            'generator_updated' => 'info',
            'operation_log_added' => 'primary',
            'maintenance_added' => 'warning',
            'maintenance_updated' => 'warning',
            'fuel_efficiency_added' => 'success',
            'fuel_efficiency_updated' => 'success',
            'compliance_added' => 'primary',
            'compliance_updated' => 'primary',
            'operator_added' => 'success',
            'user_added' => 'primary',
            default => 'primary',
        };
    }
}
