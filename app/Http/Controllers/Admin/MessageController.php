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
use Illuminate\View\View;

class MessageController extends Controller
{
    /**
     * Display a listing of messages.
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', Message::class);

        $user = auth()->user();
        $query = Message::with(['sender', 'receiver', 'operator']);

        // فلترة حسب نوع المستخدم
        if ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                // الرسائل المرسلة من المشغل
                // الرسائل الموجهة للمشغل
                // الرسائل الموجهة لموظفيه
                $query->where(function ($q) use ($user, $operator) {
                    $q->where('sender_id', $user->id)
                      ->orWhere(function ($subQ) use ($operator) {
                          // رسائل موجهة للمشغل (من أدمن)
                          $subQ->where('type', 'admin_to_operator')
                               ->where('operator_id', $operator->id);
                      })
                      ->orWhere(function ($subQ) use ($operator) {
                          // رسائل موجهة لجميع المشغلين
                          $subQ->where('type', 'admin_to_all')
                               ->whereNull('operator_id');
                      })
                      ->orWhere(function ($subQ) use ($operator) {
                          // رسائل موجهة لموظفي المشغل
                          $subQ->where('type', 'operator_to_staff')
                               ->where('operator_id', $operator->id);
                      })
                      ->orWhere('receiver_id', $user->id);
                });
            } else {
                $query->where(function ($q) use ($user) {
                    $q->where('sender_id', $user->id)
                      ->orWhere('receiver_id', $user->id);
                });
            }
        } elseif ($user->isEmployee() || $user->isTechnician()) {
            // الموظف/الفني يشوف الرسائل الموجهة له أو المرسلة منه
            $query->where(function ($q) use ($user) {
                $q->where('sender_id', $user->id)
                  ->orWhere('receiver_id', $user->id)
                  ->orWhere(function ($subQ) use ($user) {
                      // رسائل موجهة لجميع موظفي المشغل
                      $operatorIds = $user->operators->pluck('id');
                      $subQ->where('type', 'operator_to_staff')
                           ->whereIn('operator_id', $operatorIds);
                  });
            });
        }
        // السوبر أدمن والأدمن يشوفون كل الرسائل

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

        // جلب المستخدمين والمشغلين للإنشاء
        $users = collect();
        $operators = collect();

        if ($user->isSuperAdmin() || $user->isAdmin()) {
            $users = User::whereIn('role', [Role::CompanyOwner, Role::Employee, Role::Technician])
                ->orderBy('name')
                ->get(['id', 'name', 'username', 'role']);
            $operators = Operator::orderBy('name')->get(['id', 'name', 'unit_number']);
        } elseif ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                // موظفوه وفنيوه
                $users = User::whereHas('operators', function ($q) use ($operator) {
                    $q->where('operators.id', $operator->id);
                })->whereIn('role', [Role::Employee, Role::Technician])
                  ->orderBy('name')
                  ->get(['id', 'name', 'username', 'role']);
                
                // المشغلين الآخرين
                $operators = Operator::where('id', '!=', $operator->id)
                    ->orderBy('name')
                    ->get(['id', 'name', 'unit_number']);
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

        if ($user->isSuperAdmin() || $user->isAdmin()) {
            $users = User::whereIn('role', [Role::CompanyOwner, Role::Employee, Role::Technician])
                ->orderBy('name')
                ->get(['id', 'name', 'username', 'role']);
            $operators = Operator::orderBy('name')->get(['id', 'name', 'unit_number']);
        } elseif ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                $users = User::whereHas('operators', function ($q) use ($operator) {
                    $q->where('operators.id', $operator->id);
                })->whereIn('role', [Role::Employee, Role::Technician])
                  ->orderBy('name')
                  ->get(['id', 'name', 'username', 'role']);
                
                $operators = Operator::where('id', '!=', $operator->id)
                    ->orderBy('name')
                    ->get(['id', 'name', 'unit_number']);
            }
        }

        return view('admin.messages.create', compact('users', 'operators'));
    }

    /**
     * Store a newly created message.
     */
    public function store(StoreMessageRequest $request): RedirectResponse|JsonResponse
    {
        $this->authorize('create', Message::class);

        $user = auth()->user();
        $data = $request->validated();

        // تحديد نوع الرسالة
        $type = 'operator_to_operator';
        if ($user->isSuperAdmin() || $user->isAdmin()) {
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

        $message = Message::create([
            'sender_id' => $user->id,
            'receiver_id' => $data['receiver_id'] ?? null,
            'operator_id' => $data['operator_id'] ?? null,
            'subject' => $data['subject'],
            'body' => $data['body'],
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
            } elseif ($user->isEmployee() || $user->isTechnician()) {
                $operatorIds = $user->operators->pluck('id');
                $q->where(function ($subQ) use ($user, $operatorIds) {
                    $subQ->where('receiver_id', $user->id)
                         ->orWhere(function ($q2) use ($operatorIds) {
                             $q2->where('type', 'operator_to_staff')
                                ->whereIn('operator_id', $operatorIds)
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
                } elseif ($user->isEmployee() || $user->isTechnician()) {
                    $operatorIds = $user->operators->pluck('id');
                    $q->where(function ($subQ) use ($user, $operatorIds) {
                        $subQ->where('receiver_id', $user->id)
                             ->orWhere(function ($q2) use ($operatorIds) {
                                 $q2->where('type', 'operator_to_staff')
                                    ->whereIn('operator_id', $operatorIds);
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
