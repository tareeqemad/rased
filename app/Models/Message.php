<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'operator_id',
        'subject',
        'body',
        'type',
        'is_read',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'read_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class);
    }

    /**
     * تحديد ما إذا كانت الرسالة موجهة لجميع موظفي المشغل
     */
    public function isBroadcastToStaff(): bool
    {
        return $this->type === 'operator_to_staff' && $this->receiver_id === null;
    }

    /**
     * تحديد ما إذا كانت الرسالة موجهة لجميع المشغلين
     */
    public function isBroadcastToOperators(): bool
    {
        return $this->type === 'admin_to_all' && $this->operator_id === null;
    }

    /**
     * تحديد ما إذا كان المستخدم يمكنه رؤية الرسالة
     */
    public function canBeViewedBy(User $user): bool
    {
        // SuperAdmin و Admin يمكنهما رؤية جميع الرسائل
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return true;
        }

        // المرسل يمكنه رؤية رسالته
        if ($this->sender_id === $user->id) {
            return true;
        }

        // إذا كانت موجهة لمستخدم محدد
        if ($this->receiver_id === $user->id) {
            return true;
        }

        // إذا كانت موجهة لجميع موظفي مشغل معين
        if ($this->isBroadcastToStaff() && $this->operator_id) {
            if ($user->isCompanyOwner()) {
                return $user->ownedOperators()->where('id', $this->operator_id)->exists();
            }
            if ($user->isEmployee() || $user->isTechnician()) {
                return $user->operators()->where('operators.id', $this->operator_id)->exists();
            }
        }

        // إذا كانت موجهة لمشغل معين (من أدمن)
        if ($this->type === 'admin_to_operator' && $this->operator_id) {
            if ($user->isCompanyOwner()) {
                return $user->ownedOperators()->where('id', $this->operator_id)->exists();
            }
        }

        // إذا كانت موجهة لجميع المشغلين (من أدمن)
        if ($this->isBroadcastToOperators()) {
            return $user->isCompanyOwner() || $user->isSuperAdmin() || $user->isAdmin();
        }

        return false;
    }

    /**
     * تحديد ما إذا كان المستخدم يمكنه الرد على الرسالة
     */
    public function canBeRepliedBy(User $user): bool
    {
        if (!$this->canBeViewedBy($user)) {
            return false;
        }

        // لا يمكن الرد على الرسائل الموجهة للجميع
        if ($this->isBroadcastToStaff() || $this->isBroadcastToOperators()) {
            return false;
        }

        // المرسل يمكنه الرد
        if ($this->sender_id === $user->id) {
            return true;
        }

        // المستقبل يمكنه الرد
        if ($this->receiver_id === $user->id) {
            return true;
        }

        return false;
    }
}
