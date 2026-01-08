<?php

namespace App\Policies;

use App\Models\Message;
use App\Models\User;

class MessagePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() 
            || $user->isAdmin() 
            || $user->isCompanyOwner() 
            || $user->isEmployee() 
            || $user->isTechnician();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Message $message): bool
    {
        // SuperAdmin و Admin يمكنهما رؤية جميع الرسائل
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return true;
        }

        return $message->canBeViewedBy($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isSuperAdmin() 
            || $user->isAdmin() 
            || $user->isCompanyOwner();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Message $message): bool
    {
        // فقط المرسل يمكنه تحديث رسالته (قبل الإرسال - لكن نحن لا نستخدم update)
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Message $message): bool
    {
        // المرسل أو المستقبل يمكنهما حذف الرسالة
        return $message->sender_id === $user->id 
            || ($message->receiver_id === $user->id);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Message $message): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Message $message): bool
    {
        return $user->isSuperAdmin();
    }
}
