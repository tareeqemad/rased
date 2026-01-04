{{-- resources/views/admin/complaints-suggestions/show.blade.php --}}
@extends('layouts.admin')

@section('title', 'تفاصيل الطلب')

@php
    $breadcrumbTitle = 'تفاصيل الطلب';
    $isSuperAdmin = auth()->user()->isSuperAdmin();
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/complaints-suggestions-detail.css') }}">
@endpush

@section('content')
<div class="complaint-detail-page">
    <div class="row g-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h4 class="mb-0">
                        <i class="bi bi-chat-left-text me-2"></i>
                        تفاصيل الطلب
                    </h4>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.complaints-suggestions.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-right me-1"></i>
                        العودة
                    </a>
                    @if($isSuperAdmin)
                        <a href="{{ route('admin.complaints-suggestions.edit', $complaintSuggestion) }}" class="btn btn-warning">
                            <i class="bi bi-pencil me-1"></i>
                            تعديل
                        </a>
                    @endif
                </div>
            </div>

            <div class="detail-card">
                <div class="detail-card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="detail-card-title mb-0">
                            <span class="badge-status badge-status-{{ $complaintSuggestion->status }}">
                                {{ $complaintSuggestion->status_label }}
                            </span>
                        </h5>
                        <span class="badge bg-{{ $complaintSuggestion->type === 'complaint' ? 'danger' : 'primary' }}">
                            {{ $complaintSuggestion->type_label }}
                        </span>
                    </div>
                </div>
                <div class="detail-card-body">
                    <div class="tracking-code-box">
                        <div class="tracking-code-label">رمز التتبع</div>
                        <div class="tracking-code-value">{{ $complaintSuggestion->tracking_code }}</div>
                    </div>

                    <div class="info-row">
                        <div class="info-item">
                            <div class="info-label">الاسم</div>
                            <div class="info-value">{{ $complaintSuggestion->name }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">رقم الهاتف</div>
                            <div class="info-value">{{ $complaintSuggestion->phone }}</div>
                        </div>
                    </div>

                    @if($complaintSuggestion->email)
                    <div class="info-row">
                        <div class="info-item">
                            <div class="info-label">البريد الإلكتروني</div>
                            <div class="info-value">{{ $complaintSuggestion->email }}</div>
                        </div>
                    </div>
                    @endif

                    <div class="info-row">
                        <div class="info-item">
                            <div class="info-label">المحافظة</div>
                            <div class="info-value">{{ $complaintSuggestion->getGovernorateLabel() ?? 'غير محدد' }}</div>
                        </div>
                        @if($complaintSuggestion->generator)
                        <div class="info-item">
                            <div class="info-label">المولد</div>
                            <div class="info-value">{{ $complaintSuggestion->generator->name }}</div>
                        </div>
                        @endif
                        @if($complaintSuggestion->generator && $complaintSuggestion->generator->operator)
                        <div class="info-item">
                            <div class="info-label">المشغل</div>
                            <div class="info-value">{{ $complaintSuggestion->generator->operator->name }}</div>
                        </div>
                        @endif
                    </div>

                    <div class="info-row">
                        <div class="info-item">
                            <div class="info-label">تاريخ الإرسال</div>
                            <div class="info-value">{{ $complaintSuggestion->created_at->format('Y-m-d H:i') }}</div>
                        </div>
                        @if($complaintSuggestion->responded_at)
                        <div class="info-item">
                            <div class="info-label">تاريخ الرد</div>
                            <div class="info-value">{{ $complaintSuggestion->responded_at->format('Y-m-d H:i') }}</div>
                        </div>
                        @endif
                        @if($complaintSuggestion->responder)
                        <div class="info-item">
                            <div class="info-label">الرد من</div>
                            <div class="info-value">{{ $complaintSuggestion->responder->name }}</div>
                        </div>
                        @endif
                    </div>

                    <div class="message-box">
                        <div class="message-label">الرسالة</div>
                        <div class="message-content">{{ $complaintSuggestion->message }}</div>
                    </div>

                    @if($complaintSuggestion->image)
                    <div class="message-box">
                        <div class="message-label">الصورة المرفقة</div>
                        <div style="margin-top: 1rem;">
                            <img src="{{ asset('storage/' . $complaintSuggestion->image) }}" 
                                 alt="صورة مرفقة" 
                                 style="max-width: 100%; border-radius: 12px; border: 2px solid var(--c-border);">
                        </div>
                    </div>
                    @endif

                    @if($complaintSuggestion->response)
                        <div class="response-box">
                            <div class="response-label">رد الإدارة</div>
                            <div class="response-content">{{ $complaintSuggestion->response }}</div>
                        </div>
                    @else
                        <div class="message-box" style="background: #fef3c7; border-right-color: #f59e0b;">
                            <div style="font-size: 1rem; color: #92400e;">
                                ⏳ الطلب قيد المراجعة. لم يتم الرد عليه بعد.
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            @php
                $canRespond = !$complaintSuggestion->response || 
                              $isSuperAdmin || 
                              ($complaintSuggestion->responded_by && $complaintSuggestion->responded_by == auth()->id());
            @endphp

            @if($canRespond)
            <div class="detail-card">
                <div class="detail-card-header">
                    <h5 class="detail-card-title mb-0">
                        <i class="bi bi-reply me-2"></i>
                        {{ $complaintSuggestion->response ? 'تعديل الرد' : 'الرد على الطلب' }}
                    </h5>
                </div>
                <div class="detail-card-body">
                    <form action="{{ route('admin.complaints-suggestions.respond', $complaintSuggestion) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">الرد <span class="text-danger">*</span></label>
                            <textarea name="response" class="form-control" rows="6" 
                                      placeholder="اكتب ردك هنا...">{{ old('response', $complaintSuggestion->response) }}</textarea>
                            @error('response')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">تحديث الحالة <span class="text-danger">*</span></label>
                            <select name="status" class="form-select">
                                <option value="pending" {{ old('status', $complaintSuggestion->status) == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                                <option value="in_progress" {{ old('status', $complaintSuggestion->status) == 'in_progress' ? 'selected' : '' }}>قيد المعالجة</option>
                                <option value="resolved" {{ old('status', $complaintSuggestion->status) == 'resolved' ? 'selected' : '' }}>تم الحل</option>
                                <option value="rejected" {{ old('status', $complaintSuggestion->status) == 'rejected' ? 'selected' : '' }}>مرفوض</option>
                            </select>
                            @error('status')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send me-1"></i>
                                {{ $complaintSuggestion->response ? 'تحديث الرد' : 'إرسال الرد' }}
                            </button>
                            <a href="{{ route('admin.complaints-suggestions.index') }}" class="btn btn-outline-secondary">
                                إلغاء
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection




