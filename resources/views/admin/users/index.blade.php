@extends('layouts.admin')

@section('title', 'إدارة المستخدمين')

@php
    $breadcrumbTitle = 'إدارة المستخدمين';
@endphp

@section('content')
    <div class="container-fluid">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">
                    <i class="bi bi-people-fill me-2"></i>
                    إدارة المستخدمين
                </h5>
                @can('create', App\Models\User::class)
                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>
                        إضافة مستخدم جديد
                    </a>
                @endcan
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>الاسم</th>
                                <th>اسم المستخدم</th>
                                <th>البريد الإلكتروني</th>
                                <th>الصلاحية</th>
                                <th>المشغلون</th>
                                <th>تاريخ الإنشاء</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle me-2">{{ substr($user->name, 0, 1) }}</div>
                                            <span class="fw-semibold">{{ $user->name }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $user->username }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @if($user->isSuperAdmin())
                                            <span class="badge bg-danger">مدير النظام</span>
                                        @elseif($user->isCompanyOwner())
                                            <span class="badge bg-primary">صاحب شركة</span>
                                        @else
                                            @if($user->isEmployee())
                                                <span class="badge bg-success">موظف</span>
                                            @elseif($user->isTechnician())
                                                <span class="badge bg-warning">فني</span>
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        @if($user->isSuperAdmin())
                                            <span class="text-muted">كل المشغلين</span>
                                        @elseif($user->isCompanyOwner())
                                            <span class="badge bg-info">{{ $user->ownedOperators->count() }} مشغل</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $user->operators->count() }} مشغل</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $user->created_at->format('Y-m-d') }}</small>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            @can('view', $user)
                                                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline-info">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            @endcan
                                            @can('update', $user)
                                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            @endcan
                                            @can('delete', $user)
                                                @if($user->id !== auth()->id())
                                                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $user->id }}">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                @endif
                                            @endcan
                                        </div>
                                    </td>
                                </tr>

                                @can('delete', $user)
                                    @if($user->id !== auth()->id())
                                        <div class="modal fade" id="deleteModal{{ $user->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">تأكيد الحذف</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>هل أنت متأكد من حذف المستخدم <strong>{{ $user->name }}</strong>؟</p>
                                                        <p class="text-danger"><small>هذا الإجراء لا يمكن التراجع عنه</small></p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger">حذف</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endcan
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        لا توجد مستخدمين
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($users->hasPages())
                <div class="card-footer bg-white border-top">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

