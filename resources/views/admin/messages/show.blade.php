@extends('layouts.admin')

@section('title', 'عرض الرسالة')

@php
    $breadcrumbTitle = 'عرض الرسالة';
    $breadcrumbParent = 'الرسائل';
    $breadcrumbParentUrl = route('admin.messages.index');
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/permissions.css') }}">
    <style>
        .messages-page {
            --perm-primary: #667eea;
            --perm-secondary: #764ba2;
        }
        .messages-page .perm-card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        .messages-page .perm-card-header {
            background: linear-gradient(135deg, var(--perm-primary) 0%, var(--perm-secondary) 100%);
            color: white;
            padding: 1.25rem 1.5rem;
            border-bottom: none;
        }
        .messages-page .perm-title {
            font-size: 1.35rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
            color: white;
        }
        .messages-page .perm-subtitle {
            font-size: 0.9rem;
            opacity: 0.95;
            color: white;
        }
    </style>
@endpush

@section('content')
<div class="messages-page">
    <div class="row g-3">
        <div class="col-12">
            <div class="card perm-card">
                <div class="perm-card-header">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div>
                            <div class="perm-title">
                                <i class="bi bi-envelope-open me-2"></i>
                                عرض الرسالة
                            </div>
                            <div class="perm-subtitle">
                                تفاصيل الرسالة المرسلة
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            @can('delete', $message)
                                <form action="{{ route('admin.messages.destroy', $message) }}" method="POST" class="d-inline" id="deleteForm">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-light btn-sm" onclick="if(confirm('هل أنت متأكد من حذف هذه الرسالة؟')) { document.getElementById('deleteForm').submit(); }">
                                        <i class="bi bi-trash me-1"></i>
                                        حذف
                                    </button>
                                </form>
                            @endcan
                            <a href="{{ route('admin.messages.index') }}" class="btn btn-light">
                                <i class="bi bi-arrow-right me-2"></i>
                                العودة
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">
                    <div class="message-details">
                        {{-- معلومات الرسالة --}}
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">المرسل:</label>
                                    <div>
                                        <span class="badge bg-primary">{{ $message->sender->name }}</span>
                                        <small class="text-muted ms-2">{{ $message->sender->role_name }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">المستقبل:</label>
                                    <div>
                                        @if($message->receiver)
                                            <span class="badge bg-info">{{ $message->receiver->name }}</span>
                                            <small class="text-muted ms-2">{{ $message->receiver->role_name }}</small>
                                        @elseif($message->operator)
                                            <span class="badge bg-info">{{ $message->operator->name }}</span>
                                            <small class="text-muted ms-2">مشغل</small>
                                        @else
                                            <span class="badge bg-secondary">
                                                @if($message->type === 'admin_to_all')
                                                    جميع المشغلين
                                                @elseif($message->type === 'operator_to_staff')
                                                    جميع موظفي المشغل
                                                @else
                                                    -
                                                @endif
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">النوع:</label>
                                    <div>
                                        @php
                                            $typeLabels = [
                                                'operator_to_operator' => ['label' => 'مشغل لمشغل', 'badge' => 'bg-primary'],
                                                'operator_to_staff' => ['label' => 'مشغل لموظفين', 'badge' => 'bg-success'],
                                                'admin_to_operator' => ['label' => 'أدمن لمشغل', 'badge' => 'bg-warning'],
                                                'admin_to_all' => ['label' => 'أدمن للجميع', 'badge' => 'bg-danger'],
                                            ];
                                            $typeInfo = $typeLabels[$message->type] ?? ['label' => $message->type, 'badge' => 'bg-secondary'];
                                        @endphp
                                        <span class="badge {{ $typeInfo['badge'] }}">
                                            {{ $typeInfo['label'] }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">التاريخ:</label>
                                    <div>
                                        <span>{{ $message->created_at->format('Y-m-d H:i:s') }}</span>
                                    </div>
                                </div>
                            </div>
                            @if($message->is_read && $message->read_at)
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">تاريخ القراءة:</label>
                                        <div>
                                            <span>{{ $message->read_at->format('Y-m-d H:i:s') }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- الموضوع --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">الموضوع:</label>
                            <div class="p-3 bg-light rounded">
                                <h5 class="mb-0">{{ $message->subject }}</h5>
                            </div>
                        </div>

                        {{-- المحتوى --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">محتوى الرسالة:</label>
                            <div class="p-4 bg-light rounded border" style="min-height: 200px; white-space: pre-wrap;">
                                {{ $message->body }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    'use strict';
    
    // تحديث عدد الرسائل غير المقروءة عند قراءة رسالة
    if (window.MessagesPanel) {
        window.MessagesPanel.refresh();
    }
})();
</script>
@endpush
