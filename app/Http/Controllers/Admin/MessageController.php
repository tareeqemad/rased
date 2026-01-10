<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMessageRequest;
use App\Models\Message;
use App\Models\Operator;
use App\Models\User;
use App\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * ============================================
 * MessageController - كنترولر إدارة الرسائل
 * ============================================
 * 
 * هذا الكنترولر مسؤول عن إدارة جميع الرسائل في النظام
 * 
 * الأدوار الرئيسية:
 * ------------------
 * 1. السوبر أدمن (SuperAdmin):
 *    - يرى جميع الرسائل في النظام
 *    - يمكنه إرسال رسائل لجميع المشغلين أو لمشغل معين
 *    - لديه كنترول كامل على الرسائل
 * 
 * 2. سلطة الطاقة (Admin) - دور رئيسي في النظام:
 *    - يرى جميع الرسائل في النظام
 *    - يمكنه إرسال رسائل لجميع المشغلين أو لمشغل معين
 *    - لديه كنترول كامل على الرسائل
 *    - يمكنه التواصل مع جميع المشغلين والموظفين
 * 
 * 3. المشغل (CompanyOwner):
 *    - يرى الرسائل المرسلة منه
 *    - يرى الرسائل الموجهة له (من أدمن أو مشغلين آخرين)
 *    - يرى الرسائل الموجهة لموظفيه
 *    - يمكنه إرسال رسائل لموظفيه أو لمشغلين آخرين
 * 
 * 4. الموظف/الفني (Employee/Technician):
 *    - يرى الرسائل الموجهة له
 *    - يرى الرسائل الموجهة لجميع موظفي المشغل
 *    - يمكنه إرسال رسائل للمشغل
 * 
 * ============================================
 */
class MessageController extends Controller
{
    /**
     * عرض قائمة الرسائل
     * 
     * ============================================
     * سياسة عرض الرسائل حسب الدور:
     * ============================================
     * 
     * 1. السوبر أدمن (SuperAdmin):
     *    - يرى جميع الرسائل في النظام
     * 
     * 2. سلطة الطاقة (EnergyAuthority):
     *    - يرى جميع الرسائل في النظام
     *    - يمكنه إرسال رسائل لجميع المشغلين أو لمشغل معين
     *    - لديه كنترول كامل على الرسائل
     * 
     * 3. المشغل (CompanyOwner):
     *    - يرى الرسائل المرسلة منه
     *    - يرى الرسائل الموجهة له (من أدمن أو مشغلين آخرين)
     *    - يرى الرسائل الموجهة لموظفيه
     *    - يمكنه إرسال رسائل لموظفيه أو لمشغلين آخرين
     * 
     * 4. الموظف/الفني (Employee/Technician):
     *    - يرى الرسائل الموجهة له
     *    - يرى الرسائل الموجهة لجميع موظفي المشغل
     *    - يمكنه إرسال رسائل للمشغل
     * 
     * ============================================
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', Message::class);

        $user = auth()->user();
        $query = Message::with(['sender', 'receiver', 'operator']);

        // Filter messages: Each user can only see messages they sent or received
        if ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                $query->where(function ($q) use ($user, $operator) {
                    // Messages sent by this user
                    $q->where('sender_id', $user->id)
                      // Messages received by this user (including welcome messages)
                      ->orWhere('receiver_id', $user->id)
                      // Messages sent to this operator (from admin/energy authority) - even if receiver_id is set, this ensures operator messages are visible
                      ->orWhere(function ($subQ) use ($operator) {
                          $subQ->where('type', 'admin_to_operator')
                               ->where('operator_id', $operator->id);
                      })
                      // Messages broadcast to all operators
                      ->orWhere(function ($subQ) {
                          $subQ->where('type', 'admin_to_all')
                               ->whereNull('operator_id');
                      })
                      // Messages broadcast to all staff of this operator
                      ->orWhere(function ($subQ) use ($operator) {
                          $subQ->where('type', 'operator_to_staff')
                               ->where('operator_id', $operator->id);
                      });
                });
            } else {
                $query->where(function ($q) use ($user) {
                    $q->where('sender_id', $user->id)
                      ->orWhere('receiver_id', $user->id);
                });
            }
        } elseif ($user->hasOperatorLinkedCustomRole()) {
            // Users with custom roles linked to operator
            $operatorId = $user->roleModel->operator_id;
            $query->where(function ($q) use ($user, $operatorId) {
                $q->where('sender_id', $user->id)
                  ->orWhere('receiver_id', $user->id)
                  ->orWhere(function ($subQ) use ($operatorId) {
                      // Messages broadcast to all staff of this operator
                      $subQ->where('type', 'operator_to_staff')
                           ->where('operator_id', $operatorId);
                  });
            });
        } else {
            // Regular users (or users with general custom roles): only sent/received messages
            $query->where(function ($q) use ($user) {
                $q->where('sender_id', $user->id)
                  ->orWhere('receiver_id', $user->id);
            });
        }
        // Note: SuperAdmin and EnergyAuthority can only see their own messages (sent/received)

        // فلترة بالبحث
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('body', 'like', "%{$search}%")
                  ->orWhereHas('sender', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('username', 'like', "%{$search}%");
                  })
                  ->orWhereHas('receiver', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('username', 'like', "%{$search}%");
                  });
            });
        }

        // فلترة بنوع الرسالة
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        // فلترة بالحالة (مقروء/غير مقروء)
        if ($request->filled('is_read')) {
            $query->where('is_read', $request->boolean('is_read'));
        }

        // AJAX request
        if ($request->ajax() || $request->has('ajax')) {
            $messages = $query->orderBy('created_at', 'desc')->paginate(20);
            
            $html = view('admin.messages.partials.tbody-rows', ['messages' => $messages])->render();
            $pagination = '';
            if ($messages->hasPages()) {
                $pagination = view('admin.messages.partials.pagination', ['messages' => $messages])->render();
            }
            
            return response()->json([
                'success' => true,
                'html' => $html,
                'pagination' => $pagination,
                'count' => $messages->total(),
            ]);
        }

        $messages = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get users and operators for creating messages
        $users = collect();
        $operators = collect();

        if ($user->isSuperAdmin() || $user->isEnergyAuthority()) {
            // Get all users with custom roles (excluding system roles)
            $users = User::whereHas('roleModel', function ($q) {
                $q->where('is_system', false);
            })->orWhere('role', Role::CompanyOwner)
              ->orderBy('name')
              ->get(['id', 'name', 'username', 'role', 'role_id']);
            $operators = Operator::orderBy('name')->get(['id', 'name', 'unit_number']);
        } elseif ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                // Get staff users (custom roles linked to this operator)
                $users = User::whereHas('roleModel', function ($q) use ($operator) {
                    $q->where('is_system', false)
                      ->where('operator_id', $operator->id);
                })->orderBy('name')
                  ->get(['id', 'name', 'username', 'role', 'role_id']);
                
                // Other operators
                $operators = Operator::where('id', '!=', $operator->id)
                    ->orderBy('name')
                    ->get(['id', 'name', 'unit_number']);
            }
        } else {
            // Regular users with custom roles can only message their operator owner
            if ($user->hasOperatorLinkedCustomRole()) {
                $operator = $user->roleModel->operator;
                if ($operator && $operator->owner_id) {
                    $users = User::where('id', $operator->owner_id)->get(['id', 'name', 'username', 'role', 'role_id']);
                }
            }
        }

        return view('admin.messages.index', compact('messages', 'users', 'operators'));
    }

    /**
     * Show the form for creating a new message.
     */
    public function create(Request $request): View
    {
        $this->authorize('create', Message::class);

        $user = auth()->user();
        $users = collect();
        $operators = collect();

        if ($user->isSuperAdmin() || $user->isEnergyAuthority()) {
            // Get all users with custom roles (excluding system roles)
            $users = User::whereHas('roleModel', function ($q) {
                $q->where('is_system', false);
            })->orWhere('role', Role::CompanyOwner)
              ->orderBy('name')
              ->get(['id', 'name', 'username', 'role', 'role_id']);
            $operators = Operator::orderBy('name')->get(['id', 'name', 'unit_number']);
        } elseif ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                // Get staff users (custom roles linked to this operator)
                $users = User::whereHas('roleModel', function ($q) use ($operator) {
                    $q->where('is_system', false)
                      ->where('operator_id', $operator->id);
                })->orderBy('name')
                  ->get(['id', 'name', 'username', 'role', 'role_id']);
                
                $operators = Operator::where('id', '!=', $operator->id)
                    ->orderBy('name')
                    ->get(['id', 'name', 'unit_number']);
            }
        } else {
            // Regular users with custom roles can only message their operator owner
            if ($user->hasOperatorLinkedCustomRole()) {
                $operator = $user->roleModel->operator;
                if ($operator && $operator->owner_id) {
                    $users = User::where('id', $operator->owner_id)->get(['id', 'name', 'username', 'role', 'role_id']);
                }
            }
        }

        return view('admin.messages.create', compact('users', 'operators'));
    }

    /**
     * إنشاء رسالة جديدة
     * 
     * ============================================
     * أنواع الرسائل:
     * ============================================
     * 
     * 1. admin_to_all: رسالة من أدمن/سوبر أدمن لجميع المشغلين
     * 2. admin_to_operator: رسالة من أدمن/سوبر أدمن لمشغل معين
     * 3. operator_to_operator: رسالة من مشغل لمشغل آخر
     * 4. operator_to_staff: رسالة من مشغل لموظفيه
     * 5. user_to_user: رسالة من مستخدم لمستخدم آخر
     * 
     * ============================================
     */
    public function store(StoreMessageRequest $request): RedirectResponse|JsonResponse
    {
        $this->authorize('create', Message::class);

        $user = auth()->user();
        $data = $request->validated();

        // تحديد نوع الرسالة حسب المرسل والمستقبل
        $type = 'operator_to_operator';
        
        // السوبر أدمن وسلطة الطاقة (EnergyAuthority): يمكنهما إرسال رسائل لجميع المشغلين أو لمشغل معين
        if ($user->isSuperAdmin() || $user->isEnergyAuthority()) {
            if ($data['send_to'] === 'all_operators') {
                $type = 'admin_to_all';
                $data['operator_id'] = null;
                $data['receiver_id'] = null;
            } elseif (isset($data['operator_id'])) {
                $type = 'admin_to_operator';
                $data['receiver_id'] = null;
            } elseif (isset($data['receiver_id'])) {
                $type = 'operator_to_operator';
                $data['operator_id'] = null;
            }
        } elseif ($user->isCompanyOwner()) {
            if ($data['send_to'] === 'my_staff') {
                $type = 'operator_to_staff';
                $operator = $user->ownedOperators()->first();
                $data['operator_id'] = $operator?->id;
                $data['receiver_id'] = null;
            } elseif (isset($data['operator_id'])) {
                $type = 'operator_to_operator';
                $data['receiver_id'] = null;
            } elseif (isset($data['receiver_id'])) {
                $type = 'operator_to_operator';
                $data['operator_id'] = null;
            }
        }

        // Handle attachment upload
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentPath = $file->store('messages/attachments', 'public');
        }

        $message = Message::create([
            'sender_id' => $user->id,
            'receiver_id' => $data['receiver_id'] ?? null,
            'operator_id' => $data['operator_id'] ?? null,
            'subject' => $data['subject'],
            'body' => $data['body'],
            'attachment' => $attachmentPath,
            'type' => $type,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم إرسال الرسالة بنجاح',
            ]);
        }

        return redirect()->route('admin.messages.index')
            ->with('success', 'تم إرسال الرسالة بنجاح')
            ->with('message_sent', true); // Flag for JavaScript event
    }

    /**
     * Display the specified message.
     */
    public function show(Message $message): View
    {
        $this->authorize('view', $message);

        // تحديد الرسالة كمقروءة
        if (!$message->is_read && $message->receiver_id === auth()->id()) {
            $message->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }

        $message->load(['sender', 'receiver', 'operator']);

        return view('admin.messages.show', compact('message'));
    }

    /**
     * Mark message as read.
     */
    public function markAsRead(Message $message): JsonResponse
    {
        $this->authorize('view', $message);

        if (!$message->is_read) {
            $message->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Delete the specified message.
     */
    public function destroy(Message $message): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $message);

        // Delete attachment file if exists
        if ($message->attachment && Storage::disk('public')->exists($message->attachment)) {
            Storage::disk('public')->delete($message->attachment);
        }

        $message->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم حذف الرسالة بنجاح',
            ]);
        }

        return redirect()->route('admin.messages.index')
            ->with('success', 'تم حذف الرسالة بنجاح');
    }

    /**
     * Get unread messages count (AJAX).
     */
    public function getUnreadCount(): JsonResponse
    {
        $user = auth()->user();
        
        $count = Message::where(function ($q) use ($user) {
            if ($user->isCompanyOwner()) {
                $operator = $user->ownedOperators()->first();
                if ($operator) {
                    $q->where(function ($subQ) use ($user, $operator) {
                        $subQ->where('receiver_id', $user->id)
                             ->orWhere(function ($q2) use ($operator) {
                                 $q2->where('type', 'admin_to_operator')
                                    ->where('operator_id', $operator->id)
                                    ->where('is_read', false);
                             })
                             ->orWhere(function ($q2) use ($operator) {
                                 $q2->where('type', 'admin_to_all')
                                    ->whereNull('operator_id')
                                    ->where('is_read', false);
                             })
                             ->orWhere(function ($q2) use ($operator) {
                                 $q2->where('type', 'operator_to_staff')
                                    ->where('operator_id', $operator->id)
                                    ->where('is_read', false);
                             });
                    });
                } else {
                    $q->where('receiver_id', $user->id);
                }
            } elseif ($user->hasOperatorLinkedCustomRole()) {
                $operatorId = $user->roleModel->operator_id;
                $q->where(function ($subQ) use ($user, $operatorId) {
                    $subQ->where('receiver_id', $user->id)
                         ->orWhere(function ($q2) use ($operatorId) {
                             $q2->where('type', 'operator_to_staff')
                                ->where('operator_id', $operatorId)
                                ->where('is_read', false);
                         });
                });
            } else {
                $q->where('receiver_id', $user->id);
            }
        })->where('is_read', false)
          ->where('sender_id', '!=', $user->id)
          ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Get recent messages (AJAX) for dropdown.
     */
    public function getRecentMessages(): JsonResponse
    {
        $user = auth()->user();
        
        $messages = Message::with(['sender', 'receiver', 'operator'])
            ->where(function ($q) use ($user) {
                if ($user->isCompanyOwner()) {
                    $operator = $user->ownedOperators()->first();
                    if ($operator) {
                        $q->where(function ($subQ) use ($user, $operator) {
                            $subQ->where('receiver_id', $user->id)
                                 ->orWhere(function ($q2) use ($operator) {
                                     $q2->where('type', 'admin_to_operator')
                                        ->where('operator_id', $operator->id);
                                 })
                                 ->orWhere(function ($q2) use ($operator) {
                                     $q2->where('type', 'admin_to_all')
                                        ->whereNull('operator_id');
                                 })
                                 ->orWhere(function ($q2) use ($operator) {
                                     $q2->where('type', 'operator_to_staff')
                                        ->where('operator_id', $operator->id);
                                 });
                        });
                    } else {
                        $q->where('receiver_id', $user->id);
                    }
                } elseif ($user->hasOperatorLinkedCustomRole()) {
                    $operatorId = $user->roleModel->operator_id;
                    $q->where(function ($subQ) use ($user, $operatorId) {
                        $subQ->where('receiver_id', $user->id)
                             ->orWhere(function ($q2) use ($operatorId) {
                                 $q2->where('type', 'operator_to_staff')
                                    ->where('operator_id', $operatorId);
                             });
                    });
                } else {
                    $q->where('receiver_id', $user->id);
                }
            })
            ->where('sender_id', '!=', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json(['messages' => $messages]);
    }
}
