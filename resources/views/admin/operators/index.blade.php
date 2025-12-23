@extends('layouts.admin')

@section('title', 'إدارة المشغلين')

@php
    $breadcrumbTitle = 'إدارة المشغلين';
@endphp

@section('content')
    <div class="container-fluid">
        <div class="card border-0 shadow-lg">
            <!-- كارد هيدر بتصميم جميل -->
            <div class="card-header border-0" style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); padding: 1.25rem 1.5rem;">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    @can('create', App\Models\Operator::class)
                        <a href="{{ route('admin.operators.create') }}" class="btn btn-light btn-sm rounded-pill shadow-sm order-2 order-md-1">
                            <i class="bi bi-plus-circle me-2"></i>إضافة مشغل جديد
                        </a>
                    @endcan
                    <div class="d-flex align-items-center order-1 order-md-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                             style="width: 45px; height: 45px; background: rgba(255,255,255,0.2);">
                            <i class="bi bi-building text-white fs-5"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold text-white">إدارة المشغلين</h5>
                            <p class="mb-0 text-white-50 small d-none d-md-block">عرض وإدارة جميع المشغلين في النظام</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-nowrap">اسم المشغل</th>
                                <th class="text-nowrap d-none d-md-table-cell">البريد الإلكتروني</th>
                                <th class="text-nowrap d-none d-lg-table-cell">الهاتف</th>
                                <th class="text-nowrap d-none d-lg-table-cell">صاحب المشغل</th>
                                <th class="text-nowrap">عدد المولدات</th>
                                <th class="text-nowrap d-none d-xl-table-cell">تاريخ الإنشاء</th>
                                <th class="text-nowrap">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($operators as $operator)
                                <tr>
                                    <td class="text-nowrap">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle me-2">{{ substr($operator->name, 0, 1) }}</div>
                                            <div>
                                                <span class="fw-semibold d-block">{{ $operator->name }}</span>
                                                <small class="text-muted d-md-none">{{ $operator->email ?? '-' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="d-none d-md-table-cell">{{ $operator->email ?? '-' }}</td>
                                    <td class="d-none d-lg-table-cell">{{ $operator->phone ?? '-' }}</td>
                                    <td class="d-none d-lg-table-cell">
                                        @if($operator->owner)
                                            <span class="badge bg-primary">{{ $operator->owner->name }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-nowrap">
                                        <span class="badge bg-info">{{ $operator->generators->count() }} مولد</span>
                                    </td>
                                    <td class="d-none d-xl-table-cell">
                                        <small class="text-muted">{{ $operator->created_at->format('Y-m-d') }}</small>
                                    </td>
                                    <td class="text-nowrap">
                                        <div class="d-flex gap-2">
                                            @can('view', $operator)
                                                <a href="{{ route('admin.operators.show', $operator) }}" class="btn btn-sm btn-outline-info">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            @endcan
                                            @can('update', $operator)
                                                <a href="{{ route('admin.operators.edit', $operator) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            @endcan
                                            @can('delete', $operator)
                                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $operator->id }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>

                                @can('delete', $operator)
                                    <div class="modal fade" id="deleteModal{{ $operator->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">تأكيد الحذف</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>هل أنت متأكد من حذف المشغل <strong>{{ $operator->name }}</strong>؟</p>
                                                    <p class="text-danger"><small>سيتم حذف حساب المستخدم المرتبط أيضاً. هذا الإجراء لا يمكن التراجع عنه</small></p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                    <form action="{{ route('admin.operators.destroy', $operator) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">حذف</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endcan
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        لا توجد مشغلين
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($operators->hasPages())
                <div class="card-footer bg-white border-top">
                    {{ $operators->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

@push('styles')
<style>
    /* تحسين الكارد */
    .card {
        border-radius: 1rem;
        overflow: hidden;
    }
    
    /* تحسين الجدول */
    .table {
        margin-bottom: 0;
    }
    
    .table thead th {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        color: #495057;
        font-weight: 600;
        border-bottom: 2px solid #dee2e6;
        padding: 1rem 0.75rem;
    }
    
    .table tbody tr {
        transition: all 0.3s ease;
    }
    
    .table tbody tr:hover {
        background-color: #f8f9fa;
        transform: scale(1.01);
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    
    /* Avatar */
    .avatar-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
        color: white;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 1rem;
    }
    
    /* تحسين الـ badges */
    .badge {
        padding: 0.375rem 0.75rem;
        border-radius: 0.375rem;
        font-weight: 600;
    }
    
    /* تحسين الأزرار */
    .btn {
        transition: all 0.3s ease;
    }
    
    .btn-sm {
        padding: 0.375rem 0.875rem;
    }
    
    .btn-light:hover {
        background-color: rgba(255,255,255,0.9);
        transform: scale(1.05);
    }
    
    .btn-info:hover, .btn-warning:hover, .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    /* تحسين الاستجابة */
    @media (max-width: 768px) {
        .card-header {
            padding: 1rem !important;
        }
        
        .card-header h5 {
            font-size: 1rem;
        }
        
        .table {
            font-size: 0.875rem;
        }
        
        .avatar-circle {
            width: 35px;
            height: 35px;
            font-size: 0.875rem;
        }
        
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
    }
    
    /* تحسين الجدول للشاشات الصغيرة */
    .table-responsive {
        -webkit-overflow-scrolling: touch;
    }
    
    .text-nowrap {
        white-space: nowrap;
    }
</style>
@endpush
