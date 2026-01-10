<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOperatorApproved
{
    /**
     * Handle an incoming request.
     * يمنع الوصول لبعض الصفحات إذا كان المشغل غير معتمد
     * يسمح بالوصول لصفحات: profile, generation-units, generators (لإضافتها)
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403);
        }

        // SuperAdmin و Admin دائماً مسموح لهم
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return $next($request);
        }

        // الصفحات المسموح بها حتى بدون اعتماد أو حتى لو كان المشغل معطل
        $allowedRoutes = [
            'admin.operators.profile',
            'admin.operators.profile.update',
            'admin.generation-units.index',
            'admin.generation-units.create',
            'admin.generation-units.store',
            'admin.generation-units.show',
            'admin.generation-units.edit',
            'admin.generation-units.update',
            'admin.generators.index',
            'admin.generators.create',
            'admin.generators.store',
            'admin.generators.show',
            'admin.generators.edit',
            'admin.generators.update',
            'admin.dashboard',
            // Notifications routes - should be accessible even if operator is not approved
            'admin.notifications.index',
            'admin.notifications.read',
            'admin.notifications.read-all',
            'admin.notifications.destroy',
            // Messages routes - should be accessible even if operator is not approved
            'admin.messages.unread-count',
            'admin.messages.recent',
            'admin.messages.index',
            'admin.messages.show',
            'admin.messages.create',
            'admin.messages.store',
            'admin.messages.edit',
            'admin.messages.update',
            'admin.messages.destroy',
            'admin.messages.mark-read',
            // Profile routes
            'admin.profile.show',
            'admin.profile.change-password',
        ];

        $routeName = $request->route()?->getName();

        // إذا كانت الصفحة مسموح بها، اتركه يمر (حتى لو كان المشغل معطل)
        if ($routeName && in_array($routeName, $allowedRoutes)) {
            return $next($request);
        }

        // التحقق من أن المشغل مفعل (للبقية الصفحات)
        if ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator && $operator->status === 'inactive') {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'حساب المشغل معطل. يرجى التواصل مع سلطة الطاقة.',
                    ], 403);
                }
                return redirect()->route('admin.operators.profile')
                    ->with('error', 'حساب المشغل معطل. يرجى التواصل مع سلطة الطاقة.');
            }
        }

        if ($user->isEmployee() || $user->isTechnician()) {
            $hasActiveOperator = $user->operators()
                ->where('status', 'active')
                ->exists();
            
            if (!$hasActiveOperator) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'المشغل المرتبط بحسابك معطل. يرجى التواصل مع الإدارة.',
                    ], 403);
                }
                return redirect()->route('admin.dashboard')
                    ->with('error', 'المشغل المرتبط بحسابك معطل. يرجى التواصل مع الإدارة.');
            }
        }

        // إذا كان المشغل معتمد ومفعل، اتركه يمر
        if ($user->hasApprovedOperator()) {
            return $next($request);
        }

        // إذا كان يحاول الوصول لصفحة محظورة، أرسله لصفحة الملف الشخصي مع رسالة
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'حسابك في انتظار الاعتماد من سلطة الطاقة. يمكنك إضافة وحدات التوليد والمولدات فقط حتى يتم اعتماد حسابك.',
            ], 403);
        }

        return redirect()->route('admin.operators.profile')
            ->with('warning', 'حسابك في انتظار الاعتماد من سلطة الطاقة. يمكنك إضافة وحدات التوليد والمولدات فقط حتى يتم اعتماد حسابك.');
    }
}

