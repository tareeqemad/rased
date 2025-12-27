<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * جلب الإشعارات للمستخدم الحالي
     * النظام يقرأ الإشعارات الخاصة بالمستخدم فقط (آمن)
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json(['error' => 'غير مصرح'], 401);
        }
        
        // جلب الإشعارات الخاصة بالمستخدم الحالي فقط (آمن - لا يحتاج فحص صلاحيات إضافي)
        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        $unreadCount = Notification::where('user_id', $user->id)
            ->where('read', false)
            ->count();

        return response()->json([
            'notifications' => $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'link' => $notification->link,
                    'read' => $notification->read,
                    'icon' => $notification->icon,
                    'color' => $notification->color,
                    'created_at' => $notification->created_at->diffForHumans(),
                    'created_at_full' => $notification->created_at->format('Y-m-d H:i'),
                ];
            }),
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * تعليم إشعار كمقروء
     */
    public function markAsRead(Request $request, Notification $notification): JsonResponse
    {
        if ($notification->user_id !== auth()->id()) {
            return response()->json(['error' => 'غير مصرح'], 403);
        }

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * تعليم جميع الإشعارات كمقروء
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        Notification::where('user_id', $user->id)
            ->where('read', false)
            ->update([
                'read' => true,
                'read_at' => now(),
            ]);

        return response()->json(['success' => true]);
    }

    /**
     * حذف إشعار
     */
    public function destroy(Notification $notification): JsonResponse
    {
        if ($notification->user_id !== auth()->id()) {
            return response()->json(['error' => 'غير مصرح'], 403);
        }

        $notification->delete();

        return response()->json(['success' => true]);
    }
}
