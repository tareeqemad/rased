@extends('layouts.admin')

@section('title', 'الشكاوى والمقترحات')

@php
    $breadcrumbTitle = 'الشكاوى والمقترحات';
    $isSuperAdmin = auth()->user()->isSuperAdmin();
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/data-table-loading.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/complaints-suggestions.css') }}">
@endpush

@section('content')
<div class="complaints-page" id="complaintsPage" data-index-url="{{ route('admin.complaints-suggestions.index') }}">
    <div class="row g-3">
        <div class="col-12">
            <div class="complaints-card">
                <div class="complaints-card-header">
                    <div>
                        <h5 class="complaints-title">
                            <i class="bi bi-chat-left-text me-2"></i>
                            الشكاوى والمقترحات
                        </h5>
                        <div class="complaints-subtitle">
                            إدارة الشكاوى والمقترحات الواردة من المواطنين
                        </div>
                    </div>
                </div>

                <div class="card-body pb-4">
                    @if(!$isSuperAdmin)
                        <div class="alert alert-info mb-4">
                            <i class="bi bi-info-circle me-2"></i>
                            أنت ترى فقط الشكاوى والمقترحات المرتبطة بمولدات مشغلك.
                        </div>
                    @endif

                    <div class="complaints-stats mb-4">
                        <div class="complaints-stat">
                            <div class="label">الإجمالي</div>
                            <div class="value" id="statTotal">{{ $stats['total'] }}</div>
                        </div>
                        <div class="complaints-stat">
                            <div class="label">شكاوى</div>
                            <div class="value" id="statComplaints">{{ $stats['complaints'] }}</div>
                        </div>
                        <div class="complaints-stat">
                            <div class="label">مقترحات</div>
                            <div class="value" id="statSuggestions">{{ $stats['suggestions'] }}</div>
                        </div>
                        <div class="complaints-stat">
                            <div class="label">قيد الانتظار</div>
                            <div class="value" id="statPending">{{ $stats['pending'] }}</div>
                        </div>
                        <div class="complaints-stat">
                            <div class="label">قيد المعالجة</div>
                            <div class="value" id="statInProgress">{{ $stats['in_progress'] }}</div>
                        </div>
                        <div class="complaints-stat">
                            <div class="label">تم الحل</div>
                            <div class="value" id="statResolved">{{ $stats['resolved'] }}</div>
                        </div>
                    </div>

                    <form method="GET" action="{{ route('admin.complaints-suggestions.index') }}" id="searchForm">
                        <div class="row g-2 align-items-end">
                            <div class="col-lg-5">
                                <label class="form-label fw-semibold">بحث</label>
                                <div class="complaints-search">
                                    <i class="bi bi-search"></i>
                                    <input type="text" name="search" id="complaintsSearch" class="form-control" 
                                           placeholder="اسم / هاتف / رمز التتبع..." 
                                           value="{{ request('search') }}" autocomplete="off">
                                    @if(request('search'))
                                        <button type="button" class="complaints-clear" id="btnClearSearch" title="إلغاء البحث">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <label class="form-label fw-semibold">النوع</label>
                                <select name="type" id="typeFilter" class="form-select">
                                    <option value="">الكل</option>
                                    <option value="complaint" {{ request('type') == 'complaint' ? 'selected' : '' }}>شكوى</option>
                                    <option value="suggestion" {{ request('type') == 'suggestion' ? 'selected' : '' }}>مقترح</option>
                                </select>
                            </div>
                            <div class="col-lg-2">
                                <label class="form-label fw-semibold">الحالة</label>
                                <select name="status" id="statusFilter" class="form-select">
                                    <option value="">الكل</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>قيد المعالجة</option>
                                    <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>تم الحل</option>
                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>مرفوض</option>
                                </select>
                            </div>
                            <div class="col-lg-3">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary flex-grow-1" id="btnSearch">
                                        <i class="bi bi-search me-1"></i>
                                        بحث
                                    </button>
                                    @if(request('search') || request('type') || request('status'))
                                        <a href="{{ route('admin.complaints-suggestions.index') }}" class="btn btn-outline-secondary" id="btnResetFilters">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i>
                                            تصفير
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </form>

                    <hr class="my-3">

                    <div class="table-responsive" id="complaintsTableContainer">
                        <table class="table table-hover align-middle mb-0 complaints-table">
                            <thead>
                                <tr>
                                    <th style="min-width:120px;">رمز التتبع</th>
                                    <th>النوع</th>
                                    <th>الاسم</th>
                                    <th>الهاتف</th>
                                    <th class="d-none d-md-table-cell">المولد</th>
                                    <th class="d-none d-lg-table-cell">المشغل</th>
                                    <th class="text-center">الحالة</th>
                                    <th class="d-none d-xl-table-cell">التاريخ</th>
                                    <th style="min-width:140px;" class="text-center">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody id="complaintsTbody">
                                @forelse($complaintsSuggestions as $item)
                                    <tr>
                                        <td class="text-nowrap">
                                            <code class="text-primary fw-bold">{{ $item->tracking_code }}</code>
                                        </td>
                                        <td>
                                            <span class="badge-type-{{ $item->type }}">
                                                {{ $item->type_label }}
                                            </span>
                                        </td>
                                        <td class="text-nowrap">
                                            <span class="fw-semibold">{{ $item->name }}</span>
                                        </td>
                                        <td class="text-nowrap">{{ $item->phone }}</td>
                                        <td class="d-none d-md-table-cell">
                                            @if($item->generator)
                                                <span class="fw-semibold">{{ $item->generator->name }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="d-none d-lg-table-cell">
                                            @if($item->generator && $item->generator->operator)
                                                <span class="fw-semibold">{{ $item->generator->operator->name }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge-status-{{ $item->status }}">
                                                {{ $item->status_label }}
                                            </span>
                                        </td>
                                        <td class="d-none d-xl-table-cell">
                                            <small class="text-muted">
                                                {{ $item->created_at->format('Y-m-d H:i') }}
                                            </small>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex gap-2 justify-content-center">
                                                <a href="{{ route('admin.complaints-suggestions.show', $item) }}" 
                                                   class="btn btn-sm btn-outline-primary" title="عرض والرد">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                @if($isSuperAdmin)
                                                    <a href="{{ route('admin.complaints-suggestions.edit', $item) }}" 
                                                       class="btn btn-sm btn-outline-warning" title="تعديل">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <form action="{{ route('admin.complaints-suggestions.destroy', $item) }}" 
                                                          method="POST" 
                                                          class="d-inline"
                                                          onsubmit="return confirm('هل أنت متأكد من حذف هذا الطلب؟');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                            @if(request('search') || request('type') || request('status'))
                                                لا توجد نتائج للبحث
                                            @else
                                                لا توجد شكاوى أو مقترحات
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($complaintsSuggestions->hasPages())
                        <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 gap-2">
                            <div class="small text-muted">
                                @if($complaintsSuggestions->total() > 0)
                                    عرض {{ $complaintsSuggestions->firstItem() }} - {{ $complaintsSuggestions->lastItem() }} من {{ $complaintsSuggestions->total() }}
                                @else
                                    —
                                @endif
                            </div>
                            <div>
                                {{ $complaintsSuggestions->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/admin/libs/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/data-table-loading.js') }}"></script>
<script>
    (function($) {
        $(document).ready(function() {
            const $search = $('#complaintsSearch');
            const $clearSearch = $('#btnClearSearch');
            const $form = $('#searchForm');
            const $container = $('#complaintsTableContainer');

            if ($clearSearch.length) {
                $clearSearch.on('click', function() {
                    $search.val('');
                    $form.submit();
                });
            }

            $search.on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $form.submit();
                }
            });

            $form.on('submit', function() {
                if (window.DataTableLoading) {
                    window.DataTableLoading.show($container);
                }
            });
        });
    })(jQuery);
</script>
@endpush
