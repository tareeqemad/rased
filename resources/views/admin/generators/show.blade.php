@extends('layouts.admin')

@section('title', 'عرض المولد')

@php
    $breadcrumbTitle = 'عرض المولد';
    $breadcrumbParent = 'إدارة المولدات';
    $breadcrumbParentUrl = route('admin.generators.index');
    use Illuminate\Support\Facades\Storage;
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/generators.css') }}">
    <style>
        .generators-page .info-section,
        .general-page .info-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .generators-page .info-label,
        .general-page .info-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .generators-page .info-value,
        .general-page .info-value {
            color: #212529;
            font-size: 1rem;
        }
        .generators-page .image-preview,
        .general-page .image-preview {
            max-width: 300px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            padding: 0.5rem;
            background: white;
        }
    </style>
@endpush

@section('content')
<div class="general-page">
    <div class="row g-3">
        <div class="col-12">
            <div class="general-card">
                <div class="general-card-header">
                    <div>
                        <h5 class="general-title">
                            <i class="bi bi-lightning-charge me-2"></i>
                            عرض المولد: {{ $generator->name }}
                        </h5>
                        <div class="general-subtitle">
                            تفاصيل المولد الكهربائي
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.generators.qr-code', $generator) }}" target="_blank" class="btn btn-success">
                            <i class="bi bi-qr-code me-1"></i>
                            طباعة QR Code
                        </a>
                        @can('update', $generator)
                            <a href="{{ route('admin.generators.edit', $generator) }}" class="btn btn-primary">
                                <i class="bi bi-pencil me-1"></i>
                                تعديل
                            </a>
                        @endcan
                        <a href="{{ route('admin.generators.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-right me-2"></i>
                            العودة
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- المعلومات الأساسية -->
                    <div class="info-section">
                        <h5 class="mb-4 fw-bold">
                            <i class="bi bi-info-circle me-2 text-primary"></i>
                            المعلومات الأساسية
                        </h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="info-label">
                                    <i class="bi bi-lightning-charge text-warning"></i>
                                    اسم المولد
                                </div>
                                <div class="info-value">{{ $generator->name }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-label">
                                    <i class="bi bi-hash text-primary"></i>
                                    رقم المولد
                                </div>
                                <div class="info-value">
                                    <span class="badge bg-secondary">{{ $generator->generator_number }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-label">
                                    <i class="bi bi-building text-info"></i>
                                    المشغل
                                </div>
                                <div class="info-value">
                                    {{ $generator->operator ? $generator->operator->name : 'غير محدد' }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-label">
                                    <i class="bi bi-toggle-on text-{{ ($generator->statusDetail && $generator->statusDetail->code === 'ACTIVE') ? 'success' : 'danger' }}"></i>
                                    الحالة
                                </div>
                                <div class="info-value">
                                    @if($generator->statusDetail && $generator->statusDetail->code === 'ACTIVE')
                                        <span class="badge bg-success">فعال</span>
                                    @else
                                        <span class="badge bg-danger">غير فعال</span>
                                    @endif
                                </div>
                            </div>
                            @if($generator->description)
                                <div class="col-12">
                                    <div class="info-label">
                                        <i class="bi bi-file-text text-muted"></i>
                                        الوصف
                                    </div>
                                    <div class="info-value">{{ $generator->description }}</div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- المواصفات الفنية -->
                    <div class="info-section">
                        <h5 class="mb-4 fw-bold">
                            <i class="bi bi-gear me-2 text-primary"></i>
                            المواصفات الفنية
                        </h5>
                        <div class="row g-3">
                            @if($generator->capacity_kva)
                                <div class="col-md-4">
                                    <div class="info-label">
                                        <i class="bi bi-speedometer2 text-warning"></i>
                                        القدرة
                                    </div>
                                    <div class="info-value">{{ number_format($generator->capacity_kva, 2) }} KVA</div>
                                </div>
                            @endif
                            @if($generator->power_factor)
                                <div class="col-md-4">
                                    <div class="info-label">
                                        <i class="bi bi-percent text-info"></i>
                                        معامل القدرة
                                    </div>
                                    <div class="info-value">{{ number_format($generator->power_factor, 2) }}</div>
                                </div>
                            @endif
                            @if($generator->voltage)
                                <div class="col-md-4">
                                    <div class="info-label">
                                        <i class="bi bi-lightning text-danger"></i>
                                        الجهد
                                    </div>
                                    <div class="info-value">{{ $generator->voltage }}V</div>
                                </div>
                            @endif
                            @if($generator->frequency)
                                <div class="col-md-4">
                                    <div class="info-label">
                                        <i class="bi bi-arrow-repeat text-primary"></i>
                                        التردد
                                    </div>
                                    <div class="info-value">{{ $generator->frequency }} Hz</div>
                                </div>
                            @endif
                            @if($generator->engineTypeDetail)
                                <div class="col-md-4">
                                    <div class="info-label">
                                        <i class="bi bi-gear-wide text-secondary"></i>
                                        نوع المحرك
                                    </div>
                                    <div class="info-value">{{ $generator->engineTypeDetail->label }}</div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- التشغيل والوقود -->
                    <div class="info-section">
                        <h5 class="mb-4 fw-bold">
                            <i class="bi bi-fuel-pump me-2 text-primary"></i>
                            التشغيل والوقود
                        </h5>
                        <div class="row g-3">
                            @if($generator->manufacturing_year)
                                <div class="col-md-4">
                                    <div class="info-label">
                                        <i class="bi bi-calendar text-info"></i>
                                        سنة الصنع
                                    </div>
                                    <div class="info-value">{{ $generator->manufacturing_year }}</div>
                                </div>
                            @endif
                            @if($generator->injectionSystemDetail)
                                <div class="col-md-4">
                                    <div class="info-label">
                                        <i class="bi bi-droplet text-primary"></i>
                                        نظام الحقن
                                    </div>
                                    <div class="info-value">{{ $generator->injectionSystemDetail->label }}</div>
                                </div>
                            @endif
                            @if($generator->fuel_consumption_rate)
                                <div class="col-md-4">
                                    <div class="info-label">
                                        <i class="bi bi-speedometer text-warning"></i>
                                        معدل استهلاك الوقود
                                    </div>
                                    <div class="info-value">{{ number_format($generator->fuel_consumption_rate, 2) }} لتر/ساعة</div>
                                </div>
                            @endif
                            @if($generator->ideal_fuel_efficiency)
                                <div class="col-md-4">
                                    <div class="info-label">
                                        <i class="bi bi-graph-up text-success"></i>
                                        الكفاءة المثالية
                                    </div>
                                    <div class="info-value">{{ number_format($generator->ideal_fuel_efficiency, 3) }} kWh/لتر</div>
                                </div>
                            @endif
                            @if($generator->internal_tank_capacity)
                                <div class="col-md-4">
                                    <div class="info-label">
                                        <i class="bi bi-droplet-fill text-primary"></i>
                                        سعة الخزان الداخلي
                                    </div>
                                    <div class="info-value">{{ number_format($generator->internal_tank_capacity, 2) }} لتر</div>
                                </div>
                            @endif
                            @if($generator->measurementIndicatorDetail)
                                <div class="col-md-4">
                                    <div class="info-label">
                                        <i class="bi bi-rulers text-secondary"></i>
                                        مؤشر القياس
                                    </div>
                                    <div class="info-value">{{ $generator->measurementIndicatorDetail->label }}</div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- الحالة الفنية -->
                    <div class="info-section">
                        <h5 class="mb-4 fw-bold">
                            <i class="bi bi-clipboard-check me-2 text-primary"></i>
                            الحالة الفنية
                        </h5>
                        <div class="row g-3">
                            @if($generator->technicalConditionDetail)
                                <div class="col-md-6">
                                    <div class="info-label">
                                        <i class="bi bi-clipboard-check text-success"></i>
                                        الحالة الفنية
                                    </div>
                                    <div class="info-value">{{ $generator->technicalConditionDetail->label }}</div>
                                </div>
                            @endif
                            @if($generator->last_major_maintenance_date)
                                <div class="col-md-6">
                                    <div class="info-label">
                                        <i class="bi bi-calendar-event text-warning"></i>
                                        آخر صيانة كبرى
                                    </div>
                                    <div class="info-value">{{ $generator->last_major_maintenance_date->format('Y-m-d') }}</div>
                                </div>
                            @endif
                            @if($generator->engine_data_plate_image)
                                <div class="col-md-6">
                                    <div class="info-label">
                                        <i class="bi bi-image text-info"></i>
                                        صورة لوحة بيانات المحرك
                                    </div>
                                    <div class="info-value">
                                        <img src="{{ Storage::url($generator->engine_data_plate_image) }}" alt="لوحة بيانات المحرك" class="image-preview">
                                    </div>
                                </div>
                            @endif
                            @if($generator->generator_data_plate_image)
                                <div class="col-md-6">
                                    <div class="info-label">
                                        <i class="bi bi-image text-info"></i>
                                        صورة لوحة بيانات المولد
                                    </div>
                                    <div class="info-value">
                                        <img src="{{ Storage::url($generator->generator_data_plate_image) }}" alt="لوحة بيانات المولد" class="image-preview">
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- نظام التحكم -->
                    @if($generator->control_panel_available)
                        <div class="info-section">
                            <h5 class="mb-4 fw-bold">
                                <i class="bi bi-cpu me-2 text-primary"></i>
                                نظام التحكم
                            </h5>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="info-label">
                                        <i class="bi bi-check-circle text-success"></i>
                                        لوحة التحكم متوفرة
                                    </div>
                                    <div class="info-value">
                                        <span class="badge bg-success">نعم</span>
                                    </div>
                                </div>
                                @if($generator->controlPanelTypeDetail)
                                    <div class="col-md-4">
                                        <div class="info-label">
                                            <i class="bi bi-gear text-primary"></i>
                                            نوع لوحة التحكم
                                        </div>
                                        <div class="info-value">{{ $generator->controlPanelTypeDetail->label }}</div>
                                    </div>
                                @endif
                                @if($generator->controlPanelStatusDetail)
                                    <div class="col-md-4">
                                        <div class="info-label">
                                            <i class="bi bi-activity text-info"></i>
                                            حالة لوحة التحكم
                                        </div>
                                        <div class="info-value">{{ $generator->controlPanelStatusDetail->label }}</div>
                                    </div>
                                @endif
                                @if($generator->operating_hours)
                                    <div class="col-md-4">
                                        <div class="info-label">
                                            <i class="bi bi-clock text-warning"></i>
                                            ساعات التشغيل
                                        </div>
                                        <div class="info-value">{{ number_format($generator->operating_hours, 2) }} ساعة</div>
                                    </div>
                                @endif
                                @if($generator->control_panel_image)
                                    <div class="col-md-12">
                                        <div class="info-label">
                                            <i class="bi bi-image text-info"></i>
                                            صورة لوحة التحكم
                                        </div>
                                        <div class="info-value">
                                            <img src="{{ Storage::url($generator->control_panel_image) }}" alt="لوحة التحكم" class="image-preview">
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- خزانات الوقود -->
                    @if($generator->external_fuel_tank || ($generator->generationUnit && $generator->generationUnit->fuelTanks->count() > 0))
                        <div class="info-section">
                            <h5 class="mb-4 fw-bold">
                                <i class="bi bi-droplet me-2 text-primary"></i>
                                خزانات الوقود
                            </h5>
                            <div class="row g-3">
                                @if($generator->external_fuel_tank)
                                    <div class="col-md-6">
                                        <div class="info-label">
                                            <i class="bi bi-check-circle text-success"></i>
                                            خزان وقود خارجي
                                        </div>
                                        <div class="info-value">
                                            <span class="badge bg-success">نعم</span>
                                        </div>
                                    </div>
                                @endif
                                @if($generator->fuel_tanks_count)
                                    <div class="col-md-6">
                                        <div class="info-label">
                                            <i class="bi bi-hash text-primary"></i>
                                            عدد الخزانات
                                        </div>
                                        <div class="info-value">{{ $generator->fuel_tanks_count }}</div>
                                    </div>
                                @endif
                                @if($generator->generationUnit && $generator->generationUnit->fuelTanks->count() > 0)
                                    <div class="col-12">
                                        <div class="info-label mb-3">
                                            <i class="bi bi-list-ul text-info"></i>
                                            تفاصيل الخزانات
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>السعة (لتر)</th>
                                                        <th>الترتيب</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($generator->generationUnit->fuelTanks as $tank)
                                                        <tr>
                                                            <td>{{ $tank->id }}</td>
                                                            <td>{{ number_format($tank->capacity, 2) }}</td>
                                                            <td>{{ $tank->order }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- معلومات إضافية -->
                    <div class="info-section">
                        <h5 class="mb-4 fw-bold">
                            <i class="bi bi-info-circle me-2 text-primary"></i>
                            معلومات إضافية
                        </h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="info-label">
                                    <i class="bi bi-calendar-plus text-info"></i>
                                    تاريخ الإنشاء
                                </div>
                                <div class="info-value">{{ $generator->created_at->format('Y-m-d H:i:s') }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-label">
                                    <i class="bi bi-pencil text-primary"></i>
                                    آخر تحديث
                                </div>
                                <div class="info-value">{{ $generator->updated_at->format('Y-m-d H:i:s') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection



