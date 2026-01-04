@extends('layouts.admin')

@section('title', 'تفاصيل المشغل')

@php
    $breadcrumbTitle = 'تفاصيل المشغل';
    $breadcrumbParent = 'إدارة المشغلين';
    $breadcrumbParentUrl = route('admin.operators.index');
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/operators.css') }}">
@endpush

@section('content')
    <div class="operators-page operator-show-page">
        <div class="row g-3">
            {{-- Header Card with Summary --}}
            <div class="col-12">
                <div class="card op-card">
                    <div class="op-card-header">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <div>
                                <div class="op-title">
                                    <i class="bi bi-person-badge me-2"></i>
                                    {{ $operator->name }}
                                </div>
                                <div class="op-subtitle">
                                    {{ $operator->unit_number ? $operator->unit_number . ' - ' : '' }}{{ $operator->unit_name ?? '—' }}
                                    @if($operator->getGovernorateLabel())
                                        | {{ $operator->getGovernorateLabel() }}
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                @can('viewAny', [\App\Models\ElectricityTariffPrice::class, $operator])
                                    <a href="{{ route('admin.operators.tariff-prices.index', $operator) }}" class="btn btn-info">
                                        <i class="bi bi-currency-exchange me-2"></i>
                                        أسعار التعرفة
                                    </a>
                                @endcan
                                @can('update', $operator)
                                    <a href="{{ route('admin.operators.edit', $operator) }}" class="btn btn-primary">
                                        <i class="bi bi-pencil me-2"></i>
                                        تعديل
                                    </a>
                                @endcan
                                <a href="{{ route('admin.operators.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>
                                    رجوع
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Statistics Cards --}}
                    <div class="card-body">
                        <div class="row g-3 mb-4">
                            <div class="col-md-3 col-sm-6">
                                <div class="stat-card">
                                    <div class="stat-icon bg-primary">
                                        <i class="bi bi-lightning-charge"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-label">عدد المولدات</div>
                                        <div class="stat-value">{{ $operator->generators_count ?? 0 }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="stat-card">
                                    <div class="stat-icon bg-success">
                                        <i class="bi bi-people"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-label">عدد الموظفين</div>
                                        <div class="stat-value">{{ $operator->users_count ?? 0 }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="stat-card">
                                    <div class="stat-icon bg-info">
                                        <i class="bi bi-journal-text"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-label">سجلات التشغيل</div>
                                        <div class="stat-value">{{ $operator->operation_logs_count ?? 0 }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="stat-card">
                                    <div class="stat-icon {{ $operator->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                        <i class="bi bi-check-circle"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-label">الحالة</div>
                                        <div class="stat-value">
                                            {{ $operator->status === 'active' ? 'فعال' : 'غير فعال' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Main Information --}}
            <div class="col-12 col-lg-8">
                <div class="card op-card">
                    <div class="op-card-header">
                        <div class="op-title">
                            <i class="bi bi-info-circle me-2"></i>
                            معلومات المشغل
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            {{-- Basic Information --}}
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="info-label">اسم المشغل</label>
                                    <div class="info-value">{{ $operator->name ?? '—' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="info-label">البريد الإلكتروني</label>
                                    <div class="info-value">{{ $operator->email ?? '—' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="info-label">رقم الهاتف</label>
                                    <div class="info-value">{{ $operator->phone ?? '—' }}</div>
                                </div>
                            </div>
                            @if($operator->phone_alt)
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label class="info-label">رقم الهاتف البديل</label>
                                        <div class="info-value">{{ $operator->phone_alt }}</div>
                                    </div>
                                </div>
                            @endif
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="info-label">اسم الوحدة</label>
                                    <div class="info-value">{{ $operator->unit_name ?? '—' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="info-label">رقم الوحدة</label>
                                    <div class="info-value">{{ $operator->unit_number ?? '—' }}</div>
                                </div>
                            </div>
                            @if($operator->unit_code)
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label class="info-label">رمز الوحدة</label>
                                        <div class="info-value">{{ $operator->unit_code }}</div>
                                    </div>
                                </div>
                            @endif
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="info-label">المحافظة</label>
                                    <div class="info-value">{{ $operator->getGovernorateLabel() ?? '—' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="info-label">المدينة</label>
                                    <div class="info-value">{{ $operator->getCityName() ?? '—' }}</div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="info-item">
                                    <label class="info-label">العنوان التفصيلي</label>
                                    <div class="info-value">{{ $operator->detailed_address ?? ($operator->address ?? '—') }}</div>
                                </div>
                            </div>
                            @if($operator->latitude && $operator->longitude)
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label class="info-label">خط العرض</label>
                                        <div class="info-value">{{ $operator->latitude }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label class="info-label">خط الطول</label>
                                        <div class="info-value">{{ $operator->longitude }}</div>
                                    </div>
                                </div>
                            @endif

                            {{-- Owner Information --}}
                            <div class="col-12 mt-3">
                                <h6 class="fw-bold text-muted mb-3">معلومات المالك</h6>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="info-label">صاحب المشغل</label>
                                    <div class="info-value">
                                        @if($operator->owner)
                                            {{ $operator->owner->name }}
                                            <small class="text-muted ms-2">({{ $operator->owner->username }})</small>
                                        @else
                                            —
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @if($operator->owner_name)
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label class="info-label">اسم المالك</label>
                                        <div class="info-value">{{ $operator->owner_name }}</div>
                                    </div>
                                </div>
                            @endif
                            @if($operator->owner_id_number)
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label class="info-label">رقم هوية المالك</label>
                                        <div class="info-value">{{ $operator->owner_id_number }}</div>
                                    </div>
                                </div>
                            @endif
                            @if($operator->operator_id_number)
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label class="info-label">رقم هوية المشغل</label>
                                        <div class="info-value">{{ $operator->operator_id_number }}</div>
                                    </div>
                                </div>
                            @endif
                            @if($operator->operation_entity)
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label class="info-label">جهة التشغيل</label>
                                        <div class="info-value">
                                            {{ $operator->operation_entity === 'same_owner' ? 'نفس المالك' : 'طرف آخر' }}
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Technical Information --}}
                            @if($operator->total_capacity || $operator->generators_count || $operator->synchronization_available !== null)
                                <div class="col-12 mt-3">
                                    <h6 class="fw-bold text-muted mb-3">المعلومات الفنية</h6>
                                </div>
                                @if($operator->total_capacity)
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <label class="info-label">القدرة الإجمالية (KVA)</label>
                                            <div class="info-value">{{ number_format($operator->total_capacity, 2) }}</div>
                                        </div>
                                    </div>
                                @endif
                                @if($operator->generators_count !== null)
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <label class="info-label">عدد المولدات المسموح</label>
                                            <div class="info-value">{{ $operator->generators_count }}</div>
                                        </div>
                                    </div>
                                @endif
                                @if($operator->synchronization_available !== null)
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <label class="info-label">مزامنة المولدات</label>
                                            <div class="info-value">
                                                {{ $operator->synchronization_available ? 'متوفرة' : 'غير متوفرة' }}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                @if($operator->max_synchronization_capacity)
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <label class="info-label">قدرة المزامنة القصوى (KVA)</label>
                                            <div class="info-value">{{ number_format($operator->max_synchronization_capacity, 2) }}</div>
                                        </div>
                                    </div>
                                @endif
                            @endif

                            {{-- Beneficiaries Information --}}
                            @if($operator->beneficiaries_count || $operator->beneficiaries_description)
                                <div class="col-12 mt-3">
                                    <h6 class="fw-bold text-muted mb-3">معلومات المستفيدين</h6>
                                </div>
                                @if($operator->beneficiaries_count)
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <label class="info-label">عدد المستفيدين</label>
                                            <div class="info-value">{{ $operator->beneficiaries_count }}</div>
                                        </div>
                                    </div>
                                @endif
                                @if($operator->beneficiaries_description)
                                    <div class="col-md-12">
                                        <div class="info-item">
                                            <label class="info-label">وصف المستفيدين</label>
                                            <div class="info-value">{{ $operator->beneficiaries_description }}</div>
                                        </div>
                                    </div>
                                @endif
                            @endif

                            @if($operator->environmental_compliance_status)
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label class="info-label">حالة الامتثال البيئي</label>
                                        <div class="info-value">{{ $operator->environmental_compliance_status }}</div>
                                    </div>
                                </div>
                            @endif

                            {{-- Dates --}}
                            <div class="col-12 mt-3">
                                <h6 class="fw-bold text-muted mb-3">التواريخ</h6>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="info-label">تاريخ الإنشاء</label>
                                    <div class="info-value">{{ $operator->created_at->format('Y-m-d H:i') }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="info-label">آخر تحديث</label>
                                    <div class="info-value">{{ $operator->updated_at->format('Y-m-d H:i') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar: Generators List --}}
            <div class="col-12 col-lg-4">
                <div class="card op-card">
                    <div class="op-card-header">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="op-title">
                                <i class="bi bi-lightning-charge me-2"></i>
                                المولدات
                            </div>
                            @if($operator->generators_count > 0)
                                <a href="{{ route('admin.generators.index', ['operator_id' => $operator->id]) }}" class="btn btn-sm btn-outline-primary">
                                    عرض الكل
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        @if($operator->generators->count() > 0)
                            <div class="generators-list">
                                @foreach($operator->generators as $generator)
                                    <div class="generator-item">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <div class="fw-semibold">{{ $generator->name }}</div>
                                                @if($generator->generator_number)
                                                    <small class="text-muted">{{ $generator->generator_number }}</small>
                                                @endif
                                                @if($generator->capacity_kva)
                                                    <div class="mt-1">
                                                        <span class="badge bg-info">{{ number_format($generator->capacity_kva, 2) }} KVA</span>
                                                    </div>
                                                @endif
                                            </div>
                                            <a href="{{ route('admin.generators.index', ['q' => $generator->name]) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-arrow-left"></i>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-lightning-charge fs-1 d-block mb-2"></i>
                                <p>لا توجد مولدات</p>
                                    @can('create', App\Models\Generator::class)
                                    <a href="{{ route('admin.generators.create') }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-plus-circle me-1"></i>
                                        إضافة مولد
                                    </a>
                                @endcan
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Profile Completion Status --}}
                <div class="card op-card mt-3">
                    <div class="op-card-header">
                        <div class="op-title">
                            <i class="bi bi-check-circle me-2"></i>
                            حالة الملف
                        </div>
                    </div>
                    <div class="card-body">
                        @if($operator->isProfileComplete())
                            <div class="alert alert-success mb-0">
                                <i class="bi bi-check-circle me-2"></i>
                                الملف مكتمل
                            </div>
                        @else
                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                الملف غير مكتمل
                            </div>
                            @php
                                $missing = $operator->getMissingFields();
                            @endphp
                            @if(count($missing) > 0)
                                <div class="mt-2">
                                    <small class="text-muted d-block mb-1">الحقول الناقصة:</small>
                                    <ul class="mb-0 ps-3 small">
                                        @foreach($missing as $field)
                                            <li>{{ $field }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
