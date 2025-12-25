@extends('layouts.admin')

@section('title', 'إضافة مولد جديد')

@php
    $breadcrumbTitle = 'إضافة مولد جديد';
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/generators.css') }}">
@endpush

@section('content')
    <div class="generators-page">
        <div class="row g-3">
            <div class="col-12">
                <div class="card gen-card">
                    <div class="gen-card-header gen-toolbar-header">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <div>
                                <div class="gen-title">
                                    <i class="bi bi-lightning-charge me-2"></i>
                                    إضافة مولد جديد
                                </div>
                                <div class="gen-subtitle">
                                    قم بإدخال جميع بيانات المولد الكهربائي
                                </div>
                            </div>
                            <a href="{{ route('admin.generators.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-right me-2"></i>
                                العودة للقائمة
                            </a>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <form action="{{ route('admin.generators.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                    <!-- Navigation Tabs -->
                    <ul class="nav nav-pills mb-4 bg-light p-2 rounded-3" id="generatorTabs" role="tablist">
                        <li class="nav-item flex-fill" role="presentation">
                            <button class="nav-link active w-100 fw-semibold position-relative" id="basic-tab" data-bs-toggle="pill" 
                                    data-bs-target="#basic" type="button" role="tab">
                                <i class="bi bi-info-circle me-2"></i><span class="d-none d-md-inline">المعلومات الأساسية</span><span class="d-md-none">أساسي</span>
                                <span class="badge bg-white text-primary position-absolute top-0 start-0 m-1" style="font-size: 0.65rem;">1</span>
                            </button>
                        </li>
                        <li class="nav-item flex-fill" role="presentation">
                            <button class="nav-link w-100 fw-semibold position-relative" id="specs-tab" data-bs-toggle="pill" 
                                    data-bs-target="#specs" type="button" role="tab">
                                <i class="bi bi-gear me-2"></i><span class="d-none d-md-inline">المواصفات الفنية</span><span class="d-md-none">مواصفات</span>
                                <span class="badge bg-white text-muted position-absolute top-0 start-0 m-1" style="font-size: 0.65rem;">2</span>
                            </button>
                        </li>
                        <li class="nav-item flex-fill" role="presentation">
                            <button class="nav-link w-100 fw-semibold position-relative" id="fuel-tab" data-bs-toggle="pill" 
                                    data-bs-target="#fuel" type="button" role="tab">
                                <i class="bi bi-fuel-pump me-2"></i><span class="d-none d-md-inline">التشغيل والوقود</span><span class="d-md-none">تشغيل</span>
                                <span class="badge bg-white text-muted position-absolute top-0 start-0 m-1" style="font-size: 0.65rem;">3</span>
                            </button>
                        </li>
                        <li class="nav-item flex-fill" role="presentation">
                            <button class="nav-link w-100 fw-semibold position-relative" id="technical-tab" data-bs-toggle="pill" 
                                    data-bs-target="#technical" type="button" role="tab">
                                <i class="bi bi-clipboard-check me-2"></i><span class="d-none d-md-inline">الحالة الفنية</span><span class="d-md-none">حالة</span>
                                <span class="badge bg-white text-muted position-absolute top-0 start-0 m-1" style="font-size: 0.65rem;">4</span>
                            </button>
                        </li>
                        <li class="nav-item flex-fill" role="presentation">
                            <button class="nav-link w-100 fw-semibold position-relative" id="control-tab" data-bs-toggle="pill" 
                                    data-bs-target="#control" type="button" role="tab">
                                <i class="bi bi-cpu me-2"></i><span class="d-none d-md-inline">نظام التحكم</span><span class="d-md-none">تحكم</span>
                                <span class="badge bg-white text-muted position-absolute top-0 start-0 m-1" style="font-size: 0.65rem;">5</span>
                            </button>
                        </li>
                        <li class="nav-item flex-fill" role="presentation">
                            <button class="nav-link w-100 fw-semibold position-relative" id="tanks-tab" data-bs-toggle="pill" 
                                    data-bs-target="#tanks" type="button" role="tab">
                                <i class="bi bi-droplet me-2"></i><span class="d-none d-md-inline">خزانات الوقود</span><span class="d-md-none">خزانات</span>
                                <span class="badge bg-white text-muted position-absolute top-0 start-0 m-1" style="font-size: 0.65rem;">6</span>
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="generatorTabsContent">
                        <!-- المعلومات الأساسية -->
                        <div class="tab-pane fade show active" id="basic" role="tabpanel">
                            <div class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">اسم المولد <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                       value="{{ old('name') }}" required>
                                
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">رقم المولد (رقم فريد) <span class="text-danger">*</span></label>
                                <input type="text" name="generator_number" class="form-control @error('generator_number') is-invalid @enderror" 
                                       value="{{ old('generator_number') }}" required>
                                
                            </div>
                            @if(auth()->user()->isSuperAdmin())
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">المشغل <span class="text-danger">*</span></label>
                                    <select name="operator_id" class="form-select @error('operator_id') is-invalid @enderror" required>
                                        <option value="">اختر المشغل</option>
                                        @foreach($operators as $operator)
                                            <option value="{{ $operator->id }}" {{ old('operator_id') == $operator->id ? 'selected' : '' }}>
                                                {{ $operator->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    
                                </div>
                            @else
                                <input type="hidden" name="operator_id" value="{{ auth()->user()->ownedOperators()->first()->id }}">
                            @endif
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">حالة المولد <span class="text-danger">*</span></label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="">اختر الحالة</option>
                                    @foreach($constants['status'] ?? [] as $status)
                                        <option value="{{ $status->value }}" {{ old('status', 'active') == $status->value ? 'selected' : '' }}>
                                            {{ $status->label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">الوصف</label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description') }}</textarea>
                                
                            </div>
                        </div>
                    </div>
                        </div>

                        <!-- المواصفات الفنية -->
                        <div class="tab-pane fade" id="specs" role="tabpanel">
                            <div class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">قدرة المولد (KVA)</label>
                                <input type="number" step="0.01" name="capacity_kva" class="form-control @error('capacity_kva') is-invalid @enderror" 
                                       value="{{ old('capacity_kva') }}" min="0">
                                
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">معامل القدرة (P.F)</label>
                                <input type="number" step="0.01" name="power_factor" class="form-control @error('power_factor') is-invalid @enderror" 
                                       value="{{ old('power_factor') }}" min="0" max="1">
                                
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">الجهد الناتج (V)</label>
                                <input type="number" name="voltage" class="form-control @error('voltage') is-invalid @enderror" 
                                       value="{{ old('voltage') }}" min="0">
                                
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">التردد (Hz)</label>
                                <input type="number" name="frequency" class="form-control @error('frequency') is-invalid @enderror" 
                                       value="{{ old('frequency') }}" min="0">
                                
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">نوع المحرك</label>
                                <select name="engine_type" class="form-select @error('engine_type') is-invalid @enderror">
                                    <option value="">اختر نوع المحرك</option>
                                    @foreach($constants['engine_type'] ?? [] as $engineType)
                                        <option value="{{ $engineType->value }}" {{ old('engine_type') == $engineType->value ? 'selected' : '' }}>
                                            {{ $engineType->label }}
                                        </option>
                                    @endforeach
                                    {{-- Fallback للقيم القديمة --}}
                                    @if(($constants['engine_type'] ?? collect())->isEmpty())
                                        <option value="Perkins" {{ old('engine_type') === 'Perkins' ? 'selected' : '' }}>Perkins</option>
                                        <option value="Volvo" {{ old('engine_type') === 'Volvo' ? 'selected' : '' }}>Volvo</option>
                                        <option value="Caterpillar" {{ old('engine_type') === 'Caterpillar' ? 'selected' : '' }}>Caterpillar</option>
                                        <option value="DAF" {{ old('engine_type') === 'DAF' ? 'selected' : '' }}>DAF</option>
                                        <option value="MAN" {{ old('engine_type') === 'MAN' ? 'selected' : '' }}>MAN</option>
                                        <option value="SCAINA" {{ old('engine_type') === 'SCAINA' ? 'selected' : '' }}>SCAINA</option>
                                    @endif
                                </select>
                                @error('engine_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                        </div>

                        <!-- التشغيل والوقود -->
                        <div class="tab-pane fade" id="fuel" role="tabpanel">
                            <div class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">سنة التصنيع (YYYY)</label>
                                <input type="text" name="manufacturing_year" id="manufacturing_year" class="form-control @error('manufacturing_year') is-invalid @enderror" 
                                       value="{{ old('manufacturing_year') }}" placeholder="اختر السنة">
                                
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">نظام الحقن</label>
                                <select name="injection_system" class="form-select @error('injection_system') is-invalid @enderror">
                                    <option value="">اختر نظام الحقن</option>
                                    @foreach($constants['injection_system'] ?? [] as $injection)
                                        <option value="{{ $injection->value }}" {{ old('injection_system') == $injection->value ? 'selected' : '' }}>
                                            {{ $injection->label }}
                                        </option>
                                    @endforeach
                                    {{-- Fallback للقيم القديمة --}}
                                    @if(($constants['injection_system'] ?? collect())->isEmpty())
                                        <option value="عادي" {{ old('injection_system') === 'عادي' ? 'selected' : '' }}>عادي</option>
                                        <option value="كهربائي" {{ old('injection_system') === 'كهربائي' ? 'selected' : '' }}>كهربائي</option>
                                        <option value="هجين" {{ old('injection_system') === 'هجين' ? 'selected' : '' }}>هجين</option>
                                    @endif
                                </select>
                                @error('injection_system')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">معدل استهلاك الوقود (لتر/ساعة)</label>
                                <input type="number" step="0.01" name="fuel_consumption_rate" class="form-control @error('fuel_consumption_rate') is-invalid @enderror" 
                                       value="{{ old('fuel_consumption_rate') }}" min="0">
                                
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">سعة خزان الوقود الداخلي (لتر)</label>
                                <input type="number" name="internal_tank_capacity" class="form-control @error('internal_tank_capacity') is-invalid @enderror" 
                                       value="{{ old('internal_tank_capacity') }}" min="0">
                                
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">مؤشر القياس</label>
                                <select name="measurement_indicator" class="form-select @error('measurement_indicator') is-invalid @enderror">
                                    <option value="">اختر الحالة</option>
                                    @foreach($constants['measurement_indicator'] ?? [] as $indicator)
                                        <option value="{{ $indicator->value }}" {{ old('measurement_indicator') == $indicator->value ? 'selected' : '' }}>
                                            {{ $indicator->label }}
                                        </option>
                                    @endforeach
                                    {{-- Fallback للقيم القديمة --}}
                                    @if(($constants['measurement_indicator'] ?? collect())->isEmpty())
                                        <option value="غير متوفر" {{ old('measurement_indicator') === 'غير متوفر' ? 'selected' : '' }}>غير متوفر</option>
                                        <option value="متوفر ويعمل" {{ old('measurement_indicator') === 'متوفر ويعمل' ? 'selected' : '' }}>متوفر ويعمل</option>
                                        <option value="متوفر ولا يعمل" {{ old('measurement_indicator') === 'متوفر ولا يعمل' ? 'selected' : '' }}>متوفر ولا يعمل</option>
                                    @endif
                                </select>
                                @error('measurement_indicator')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                        </div>

                        <!-- الحالة الفنية والتوثيق -->
                        <div class="tab-pane fade" id="technical" role="tabpanel">
                            <div class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">الحالة الفنية</label>
                                <select name="technical_condition" class="form-select @error('technical_condition') is-invalid @enderror">
                                    <option value="">اختر الحالة</option>
                                    @foreach($constants['technical_condition'] ?? [] as $condition)
                                        <option value="{{ $condition->value }}" {{ old('technical_condition') == $condition->value ? 'selected' : '' }}>
                                            {{ $condition->label }}
                                        </option>
                                    @endforeach
                                    {{-- Fallback للقيم القديمة --}}
                                    @if(($constants['technical_condition'] ?? collect())->isEmpty())
                                        <option value="ممتازة" {{ old('technical_condition') === 'ممتازة' ? 'selected' : '' }}>ممتازة</option>
                                        <option value="جيدة جدا" {{ old('technical_condition') === 'جيدة جدا' ? 'selected' : '' }}>جيدة جدا</option>
                                        <option value="جيدة" {{ old('technical_condition') === 'جيدة' ? 'selected' : '' }}>جيدة</option>
                                        <option value="متوسطة" {{ old('technical_condition') === 'متوسطة' ? 'selected' : '' }}>متوسطة</option>
                                        <option value="سيئة" {{ old('technical_condition') === 'سيئة' ? 'selected' : '' }}>سيئة</option>
                                    @endif
                                </select>
                                @error('technical_condition')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">تاريخ آخر صيانة كبرى</label>
                                <input type="date" name="last_major_maintenance_date" class="form-control @error('last_major_maintenance_date') is-invalid @enderror" 
                                       value="{{ old('last_major_maintenance_date') }}">
                                
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">صورة لوحة البيانات للمحرك</label>
                                <input type="file" name="engine_data_plate_image" class="form-control image-input @error('engine_data_plate_image') is-invalid @enderror" 
                                       accept="image/*" data-preview="engine_data_plate_preview">
                                <div class="image-preview-container mt-2" id="engine_data_plate_preview" style="display: none;">
                                    <img src="" alt="معاينة" class="image-preview">
                                    <button type="button" class="btn btn-sm btn-danger remove-image" onclick="removeImagePreview('engine_data_plate_image', 'engine_data_plate_preview')">
                                        <i class="bi bi-x-circle"></i> إزالة
                                    </button>
                                </div>
                                
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">صورة لوحة البيانات للمولد</label>
                                <input type="file" name="generator_data_plate_image" class="form-control image-input @error('generator_data_plate_image') is-invalid @enderror" 
                                       accept="image/*" data-preview="generator_data_plate_preview">
                                <div class="image-preview-container mt-2" id="generator_data_plate_preview" style="display: none;">
                                    <img src="" alt="معاينة" class="image-preview">
                                    <button type="button" class="btn btn-sm btn-danger remove-image" onclick="removeImagePreview('generator_data_plate_image', 'generator_data_plate_preview')">
                                        <i class="bi bi-x-circle"></i> إزالة
                                    </button>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                        </div>

                        <!-- نظام التحكم -->
                        <div class="tab-pane fade" id="control" role="tabpanel">
                            <div class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">لوحة التحكم</label>
                                <select name="control_panel_available" class="form-select @error('control_panel_available') is-invalid @enderror">
                                    <option value="0" {{ old('control_panel_available', '0') == '0' ? 'selected' : '' }}>غير متوفرة</option>
                                    <option value="1" {{ old('control_panel_available') == '1' ? 'selected' : '' }}>متوفرة</option>
                                </select>
                                
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">نوع لوحة التحكم</label>
                                <select name="control_panel_type" class="form-select @error('control_panel_type') is-invalid @enderror">
                                    <option value="">اختر النوع</option>
                                    @foreach($constants['control_panel_type'] ?? [] as $panelType)
                                        <option value="{{ $panelType->value }}" {{ old('control_panel_type') == $panelType->value ? 'selected' : '' }}>
                                            {{ $panelType->label }}
                                        </option>
                                    @endforeach
                                    {{-- Fallback للقيم القديمة --}}
                                    @if(($constants['control_panel_type'] ?? collect())->isEmpty())
                                        <option value="Deep Sea" {{ old('control_panel_type') === 'Deep Sea' ? 'selected' : '' }}>Deep Sea</option>
                                        <option value="ComAp" {{ old('control_panel_type') === 'ComAp' ? 'selected' : '' }}>ComAp</option>
                                        <option value="Datakom" {{ old('control_panel_type') === 'Datakom' ? 'selected' : '' }}>Datakom</option>
                                        <option value="Analog" {{ old('control_panel_type') === 'Analog' ? 'selected' : '' }}>Analog</option>
                                    @endif
                                </select>
                                @error('control_panel_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">حالة لوحة التحكم</label>
                                <select name="control_panel_status" class="form-select @error('control_panel_status') is-invalid @enderror">
                                    <option value="">اختر الحالة</option>
                                    @foreach($constants['control_panel_status'] ?? [] as $panelStatus)
                                        <option value="{{ $panelStatus->value }}" {{ old('control_panel_status') == $panelStatus->value ? 'selected' : '' }}>
                                            {{ $panelStatus->label }}
                                        </option>
                                    @endforeach
                                    {{-- Fallback للقيم القديمة --}}
                                    @if(($constants['control_panel_status'] ?? collect())->isEmpty())
                                        <option value="تعمل" {{ old('control_panel_status') === 'تعمل' ? 'selected' : '' }}>تعمل</option>
                                        <option value="لا تعمل" {{ old('control_panel_status') === 'لا تعمل' ? 'selected' : '' }}>لا تعمل</option>
                                    @endif
                                </select>
                                @error('control_panel_status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">صورة لوحة التحكم</label>
                                <input type="file" name="control_panel_image" class="form-control image-input @error('control_panel_image') is-invalid @enderror" 
                                       accept="image/*" data-preview="control_panel_preview">
                                <div class="image-preview-container mt-2" id="control_panel_preview" style="display: none;">
                                    <img src="" alt="معاينة" class="image-preview">
                                    <button type="button" class="btn btn-sm btn-danger remove-image" onclick="removeImagePreview('control_panel_image', 'control_panel_preview')">
                                        <i class="bi bi-x-circle"></i> إزالة
                                    </button>
                                </div>
                                
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">قراءة ساعات التشغيل الحالية</label>
                                <input type="number" name="operating_hours" class="form-control @error('operating_hours') is-invalid @enderror" 
                                       value="{{ old('operating_hours') }}" min="0">
                                
                            </div>
                        </div>
                    </div>
                        </div>

                        <!-- خزانات الوقود -->
                        <div class="tab-pane fade" id="tanks" role="tabpanel">
                            <div class="mb-4">
                                <!-- خزان وقود خارجي -->
                                <div class="card mb-4 border-0 shadow-sm">
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">خزان وقود خارجي <span class="text-danger">*</span></label>
                                                <select name="external_fuel_tank" id="external_fuel_tank" class="form-select @error('external_fuel_tank') is-invalid @enderror" required>
                                                    <option value="0" {{ old('external_fuel_tank', '0') == '0' ? 'selected' : '' }}>لا</option>
                                                    <option value="1" {{ old('external_fuel_tank') == '1' ? 'selected' : '' }}>نعم</option>
                                                </select>
                                                
                                            </div>
                                            <div class="col-md-6" id="fuel_tanks_count_wrapper" style="display: none;">
                                                <label class="form-label fw-semibold">عدد خزانات الوقود (1-10) <span class="text-danger">*</span></label>
                                                <select name="fuel_tanks_count" id="fuel_tanks_count" class="form-select @error('fuel_tanks_count') is-invalid @enderror">
                                                    <option value="0">اختر العدد</option>
                                                    @for($i = 1; $i <= 10; $i++)
                                                        <option value="{{ $i }}" {{ old('fuel_tanks_count') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                                    @endfor
                                                </select>
                                                
                                            </div>
                                            <!-- حقل hidden لإرسال القيمة الافتراضية عندما يكون external_fuel_tank = 0 -->
                                            <input type="hidden" id="fuel_tanks_count_hidden" value="0">
                                        </div>
                                    </div>
                                </div>

                                <!-- خزانات الوقود الديناميكية -->
                                <div id="fuel_tanks_container"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="d-flex justify-content-between align-items-center mt-4 pt-4 border-top">
                        <button type="button" class="btn btn-outline-primary btn-lg px-4 rounded-pill shadow-sm" id="prevBtn" onclick="navigateTabs(-1)" style="display: none;">
                            <i class="bi bi-arrow-right me-2"></i>السابق
                        </button>
                        <a href="{{ route('admin.generators.index') }}" class="btn btn-outline-secondary btn-lg px-4 rounded-pill">
                            <i class="bi bi-x-circle me-2"></i>إلغاء
                        </a>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-primary btn-lg px-5 rounded-pill shadow" id="nextBtn" onclick="navigateTabs(1)">
                                التالي<i class="bi bi-arrow-left ms-2"></i>
                            </button>
                            <button type="submit" class="btn btn-success btn-lg px-5 rounded-pill shadow" id="submitBtn" style="display: none;">
                                <i class="bi bi-check-lg me-2"></i>حفظ البيانات
                            </button>
                        </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    /* استخدام نفس تصميم generators.css */
    /* تحسين التابات */
    .nav-pills .nav-link {
        color: #6c757d;
        border-radius: 0.5rem;
        transition: all 0.3s ease;
        padding: 0.75rem 1rem;
        font-size: 0.9rem;
        position: relative;
    }
    
    .nav-pills .nav-link .badge {
        transition: all 0.3s ease;
    }
    
    .nav-pills .nav-link:hover {
        background-color: rgba(37, 99, 235, 0.1);
        color: #2563eb;
        transform: translateY(-2px);
    }
    
    .nav-pills .nav-link.active {
        background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
        color: white !important;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    }
    
    .nav-pills .nav-link.active .badge {
        background-color: rgba(255,255,255,0.3) !important;
        color: white !important;
    }
    
    /* استجابة للشاشات الصغيرة */
    @media (max-width: 768px) {
        .nav-pills .nav-link {
            font-size: 0.75rem;
            padding: 0.5rem 0.5rem;
        }
        
        .nav-pills .nav-link i {
            font-size: 0.85rem;
        }
    }
    
    
    /* تحسين الأزرار */
    .btn {
        border-radius: 0.5rem;
        padding: 0.625rem 1.5rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-lg.rounded-pill {
        border-radius: 50rem !important;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
        border: none;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
        background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
    }
    
    .btn-success {
        background: linear-gradient(135deg, #15803d 0%, #22c55e 100%);
        border: none;
    }
    
    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(34, 197, 94, 0.4);
        background: linear-gradient(135deg, #166534 0%, #16a34a 100%);
    }
    
    .btn-outline-primary:hover {
        background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
        border-color: transparent;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    }
    
    .btn-outline-secondary:hover {
        transform: translateY(-2px);
    }
    
    /* تحسين الكاردات */
    .card {
        border-radius: 1rem;
        overflow: hidden;
    }
    
    .card-header {
        border: none;
    }
    
    /* تحسين التاب المحتوى */
    .tab-pane {
        animation: fadeIn 0.5s ease-in;
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* تحسين المساحات */
    .row.g-3 > * {
        margin-bottom: 0.5rem;
    }
    
    /* تأثيرات المؤشرات */
    #sessionTimer {
        animation: pulse 2s infinite;
        font-weight: 600;
    }
    
    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.8;
        }
    }
    
    /* تحسين التنبيهات */
    .alert {
        border-radius: 0.75rem;
    }
    
    .alert-info {
        border-left: 4px solid #2563eb;
    }
    
    /* معاينة الصور */
    .image-preview-container {
        position: relative;
        border-radius: 0.5rem;
        overflow: hidden;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 0.5rem;
        border: 2px dashed #dee2e6;
        transition: all 0.3s ease;
    }
    
    .image-preview-container:hover {
        border-color: #2563eb;
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    }
    
    .image-preview {
        width: 100%;
        max-height: 200px;
        object-fit: contain;
        border-radius: 0.375rem;
        display: block;
        transition: transform 0.3s ease;
    }
    
    .image-preview:hover {
        transform: scale(1.02);
    }
    
    .remove-image {
        position: absolute;
        top: 10px;
        right: 10px;
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        border-radius: 0.25rem;
        z-index: 10;
        transition: all 0.3s ease;
    }
    
    .remove-image:hover {
        transform: scale(1.1);
    }
    
    /* تحسين الفورم للشاشات الصغيرة */
    @media (max-width: 768px) {
        .card-body {
            padding: 1.5rem !important;
        }
        
        .image-preview {
            max-height: 150px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // تمرير الثوابت للـ JavaScript
    window.GENERATOR_CONSTANTS = {
        location: @json(($constants['location'] ?? collect())->map(fn($c) => ['value' => $c->value, 'label' => $c->label])->values()),
        material: @json(($constants['material'] ?? collect())->map(fn($c) => ['value' => $c->value, 'label' => $c->label])->values()),
        usage: @json(($constants['usage'] ?? collect())->map(fn($c) => ['value' => $c->value, 'label' => $c->label])->values()),
        measurement_method: @json(($constants['measurement_method'] ?? collect())->map(fn($c) => ['value' => $c->value, 'label' => $c->label])->values()),
    };

    document.addEventListener('DOMContentLoaded', function() {
        let currentTab = 0;
        const tabs = ['basic', 'specs', 'fuel', 'technical', 'control', 'tanks'];
        let lastRefreshTime = Date.now();
        const refreshInterval = 10 * 60 * 1000; // 10 دقائق
        
        // عرض وقت التحديث التالي
        function updateSessionTimer() {
            const timePassed = Date.now() - lastRefreshTime;
            const timeRemaining = Math.max(0, refreshInterval - timePassed);
            const minutesRemaining = Math.ceil(timeRemaining / 60000);
            
            const timerBadge = document.getElementById('sessionTimer');
            if (timerBadge) {
                if (minutesRemaining > 5) {
                    timerBadge.className = 'badge bg-success ms-2';
                    timerBadge.textContent = `نشط - التحديث بعد ${minutesRemaining} دقيقة`;
                } else if (minutesRemaining > 2) {
                    timerBadge.className = 'badge bg-warning ms-2';
                    timerBadge.textContent = `التحديث بعد ${minutesRemaining} دقائق`;
                } else if (minutesRemaining > 0) {
                    timerBadge.className = 'badge bg-danger ms-2';
                    timerBadge.textContent = `التحديث قريباً (${minutesRemaining} دقيقة)`;
                }
            }
        }
        
        // تحديث المؤقت كل 30 ثانية
        setInterval(updateSessionTimer, 30000);
        updateSessionTimer();
        
        // تحديث CSRF Token كل 10 دقائق لتجنب مشكلة Page Expired
        function refreshCSRFToken() {
            fetch('{{ route("admin.generators.create") }}', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newToken = doc.querySelector('input[name="_token"]');
                if (newToken) {
                    document.querySelector('input[name="_token"]').value = newToken.value;
                    lastRefreshTime = Date.now();
                    updateSessionTimer();
                    
                    // عرض إشعار نجاح
                    const timerBadge = document.getElementById('sessionTimer');
                    if (timerBadge) {
                        timerBadge.className = 'badge bg-success ms-2';
                        timerBadge.textContent = '✓ تم التحديث';
                        setTimeout(() => updateSessionTimer(), 2000);
                    }
                    
                    console.log('✓ تم تحديث CSRF token بنجاح');
                }
            })
            .catch(error => {
                console.error('خطأ في تحديث CSRF token:', error);
                const timerBadge = document.getElementById('sessionTimer');
                if (timerBadge) {
                    timerBadge.className = 'badge bg-danger ms-2';
                    timerBadge.textContent = '⚠ خطأ في التحديث';
                }
            });
        }
        
        // تحديث التوكن كل 10 دقائق
        setInterval(refreshCSRFToken, refreshInterval);
        
        // عرض تحذير قبل مغادرة الصفحة إذا تم تعديل الفورم
        let formModified = false;
        document.querySelectorAll('input, select, textarea').forEach(element => {
            element.addEventListener('change', () => formModified = true);
        });
        
        window.addEventListener('beforeunload', function(e) {
            if (formModified) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
        
        // إزالة التحذير عند إرسال الفورم
        document.querySelector('form').addEventListener('submit', function(e) {
            formModified = false;
            
            // إدارة حقول fuel_tanks_count قبل الإرسال
            const externalFuelTankValue = externalFuelTankSelect ? externalFuelTankSelect.value : '0';
            if (externalFuelTankValue === '0') {
                // إذا كان external_fuel_tank = 0، استخدم الحقل المخفي فقط
                if (fuelTanksCountSelect) {
                    fuelTanksCountSelect.removeAttribute('name');
                }
                if (fuelTanksCountHidden) {
                    fuelTanksCountHidden.setAttribute('name', 'fuel_tanks_count');
                }
            } else {
                // إذا كان external_fuel_tank = 1، استخدم الـ select فقط
                if (fuelTanksCountHidden) {
                    fuelTanksCountHidden.removeAttribute('name');
                }
                if (fuelTanksCountSelect) {
                    fuelTanksCountSelect.setAttribute('name', 'fuel_tanks_count');
                }
            }
            
            // التحقق من صلاحية الـ token قبل الإرسال
            const token = document.querySelector('input[name="_token"]').value;
            if (!token || token.length < 10) {
                e.preventDefault();
                
                // عرض رسالة تحذيرية
                if (confirm('انتهت صلاحية الجلسة. هل تريد تحديث الصفحة والمحاولة مرة أخرى؟\n\nملاحظة: سيتم حفظ البيانات المدخلة مؤقتاً.')) {
                    // حفظ البيانات في localStorage قبل إعادة التحميل
                    const formData = new FormData(this);
                    const savedData = {};
                    for (let [key, value] of formData.entries()) {
                        if (key !== '_token') {
                            savedData[key] = value;
                        }
                    }
                    localStorage.setItem('generator_form_backup', JSON.stringify(savedData));
                    
                    // إعادة تحميل الصفحة
                    window.location.reload();
                }
            }
        });
        
        // استعادة البيانات المحفوظة إن وُجدت
        const savedFormData = localStorage.getItem('generator_form_backup');
        if (savedFormData) {
            try {
                const data = JSON.parse(savedFormData);
                Object.keys(data).forEach(key => {
                    const field = document.querySelector(`[name="${key}"]`);
                    if (field && !field.value) {
                        field.value = data[key];
                    }
                });
                
                // عرض رسالة نجاح
                const alert = document.createElement('div');
                alert.className = 'alert alert-success alert-dismissible fade show';
                alert.innerHTML = `
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <strong>تم استعادة البيانات بنجاح!</strong> البيانات التي أدخلتها سابقاً تم استعادتها.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.querySelector('form').insertBefore(alert, document.querySelector('form').firstChild);
                
                // حذف البيانات المحفوظة
                localStorage.removeItem('generator_form_backup');
            } catch (e) {
                console.error('خطأ في استعادة البيانات:', e);
                localStorage.removeItem('generator_form_backup');
            }
        }
        
        // Tab Navigation Function
        window.navigateTabs = function(direction) {
            currentTab += direction;
            
            if (currentTab < 0) currentTab = 0;
            if (currentTab >= tabs.length) currentTab = tabs.length - 1;
            
            // Activate the tab
            const tabButton = document.getElementById(tabs[currentTab] + '-tab');
            if (tabButton) {
                const tab = new bootstrap.Tab(tabButton);
                tab.show();
            }
            
            updateNavigationButtons();
        };
        
        // Update navigation buttons visibility
        function updateNavigationButtons() {
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            const submitBtn = document.getElementById('submitBtn');
            
            prevBtn.style.display = currentTab === 0 ? 'none' : 'inline-block';
            nextBtn.style.display = currentTab === tabs.length - 1 ? 'none' : 'inline-block';
            submitBtn.style.display = currentTab === tabs.length - 1 ? 'inline-block' : 'none';
        }
        
        // Listen to tab changes
        document.querySelectorAll('#generatorTabs button[data-bs-toggle="pill"]').forEach((tabButton, index) => {
            tabButton.addEventListener('shown.bs.tab', function() {
                currentTab = index;
                updateNavigationButtons();
            });
        });
        
        updateNavigationButtons();
        
        // معالجة معاينة الصور
        document.querySelectorAll('.image-input').forEach(input => {
            input.addEventListener('change', function() {
                const previewId = this.getAttribute('data-preview');
                const previewContainer = document.getElementById(previewId);
                const file = this.files[0];
                
                if (file) {
                    // التحقق من نوع الملف
                    if (!file.type.startsWith('image/')) {
                        window.adminNotifications.error('يرجى اختيار ملف صورة صالح', 'خطأ');
                        this.value = '';
                        previewContainer.style.display = 'none';
                        return;
                    }
                    
                    // التحقق من حجم الملف (أقل من 5MB)
                    if (file.size > 5 * 1024 * 1024) {
                        window.adminNotifications.error('حجم الصورة كبير جداً. يرجى اختيار صورة أقل من 5 ميجابايت', 'خطأ');
                        this.value = '';
                        previewContainer.style.display = 'none';
                        return;
                    }
                    
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        const img = previewContainer.querySelector('.image-preview');
                        img.src = e.target.result;
                        previewContainer.style.display = 'block';
                        
                        // إضافة تأثير fade-in
                        previewContainer.style.opacity = '0';
                        setTimeout(() => {
                            previewContainer.style.transition = 'opacity 0.3s ease';
                            previewContainer.style.opacity = '1';
                        }, 10);
                    };
                    
                    reader.readAsDataURL(file);
                } else {
                    previewContainer.style.display = 'none';
                }
            });
        });
        
        // وظيفة إزالة الصورة
        window.removeImagePreview = function(inputName, previewId) {
            const input = document.querySelector(`input[name="${inputName}"]`);
            const previewContainer = document.getElementById(previewId);
            
            if (input) {
                input.value = '';
            }
            if (previewContainer) {
                // إضافة تأثير fade-out
                previewContainer.style.transition = 'opacity 0.3s ease';
                previewContainer.style.opacity = '0';
                
                setTimeout(() => {
                    previewContainer.style.display = 'none';
                    const img = previewContainer.querySelector('.image-preview');
                    if (img) {
                        img.src = '';
                    }
                    previewContainer.style.opacity = '1';
                }, 300);
            }
        };
        
        // تهيئة date picker لسنة التصنيع (سنوات فقط)
        const manufacturingYearInput = document.getElementById('manufacturing_year');
        if (manufacturingYearInput) {
            function initYearPicker() {
                if (typeof flatpickr === 'undefined') {
                    setTimeout(initYearPicker, 100);
                    return;
                }

                const currentYear = new Date().getFullYear();
                const defaultYear = manufacturingYearInput.value || null;

                flatpickr(manufacturingYearInput, {
                    dateFormat: 'Y',
                    mode: 'single',
                    minDate: '1900-01-01',
                    maxDate: new Date(),
                    locale: 'ar',
                    allowInput: true,
                    defaultDate: defaultYear ? defaultYear + '-01-01' : null,
                    onChange: function(selectedDates, dateStr, instance) {
                        // استخراج السنة فقط من التاريخ
                        if (dateStr) {
                            const year = parseInt(dateStr.split('-')[0]);
                            if (year >= 1900 && year <= currentYear) {
                                manufacturingYearInput.value = year;
                            } else {
                                manufacturingYearInput.value = '';
                            }
                        }
                    },
                    onReady: function(selectedDates, dateStr, instance) {
                        // جعل التقويم يظهر السنة فقط عند النقر
                        const monthNav = instance.calendarContainer.querySelector('.flatpickr-month');
                        if (monthNav) {
                            monthNav.style.cursor = 'pointer';
                        }
                    }
                });
            }
            initYearPicker();
        }

        // إدارة خزانات الوقود الديناميكية
        const externalFuelTankSelect = document.getElementById('external_fuel_tank');
        const fuelTanksCountWrapper = document.getElementById('fuel_tanks_count_wrapper');
        const fuelTanksCountSelect = document.getElementById('fuel_tanks_count');
        const fuelTanksCountHidden = document.getElementById('fuel_tanks_count_hidden');
        const fuelTanksContainer = document.getElementById('fuel_tanks_container');

        // عند تغيير "خزان وقود خارجي"
        if (externalFuelTankSelect) {
            externalFuelTankSelect.addEventListener('change', function() {
                if (this.value === '1') {
                    fuelTanksCountWrapper.style.display = 'block';
                    fuelTanksCountSelect.required = true;
                    // إخفاء الحقل المخفي وإظهار الـ select
                    if (fuelTanksCountHidden) {
                        fuelTanksCountHidden.disabled = true;
                    }
                    fuelTanksCountSelect.disabled = false;
                } else {
                    fuelTanksCountWrapper.style.display = 'none';
                    fuelTanksCountSelect.required = false;
                    fuelTanksCountSelect.value = '0';
                    fuelTanksContainer.innerHTML = '';
                    // إظهار الحقل المخفي وإخفاء الـ select
                    if (fuelTanksCountHidden) {
                        fuelTanksCountHidden.disabled = false;
                    }
                    fuelTanksCountSelect.disabled = true;
                }
            });

            // تهيئة أولية
            if (externalFuelTankSelect.value === '1') {
                fuelTanksCountWrapper.style.display = 'block';
                fuelTanksCountSelect.required = true;
                if (fuelTanksCountHidden) {
                    fuelTanksCountHidden.disabled = true;
                }
                fuelTanksCountSelect.disabled = false;
                if (fuelTanksCountSelect.value && fuelTanksCountSelect.value !== '0') {
                    renderFuelTanks(parseInt(fuelTanksCountSelect.value));
                }
            } else {
                if (fuelTanksCountHidden) {
                    fuelTanksCountHidden.disabled = false;
                }
                fuelTanksCountSelect.disabled = true;
            }
        }

        // عند تغيير عدد الخزانات
        if (fuelTanksCountSelect) {
            fuelTanksCountSelect.addEventListener('change', function() {
                const count = parseInt(this.value);
                if (count > 0 && count <= 10) {
                    renderFuelTanks(count);
                } else {
                    fuelTanksContainer.innerHTML = '';
                }
            });
        }

        // دالة لرسم خزانات الوقود
        function renderFuelTanks(count) {
            fuelTanksContainer.innerHTML = '';

            for (let i = 1; i <= count; i++) {
                const tankHtml = `
                    <div class="card mb-3 border-0 shadow-sm" id="tank_${i}">
                        <div class="card-header" style="background: linear-gradient(135deg, #2563eb 0%, #60a5fa 100%); padding: 1rem;">
                            <h6 class="mb-0 fw-bold text-white">
                                <i class="bi bi-droplet-fill me-2"></i>خزان الوقود ${i}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">سعة الخزان ${i} (لتر) <span class="text-danger">*</span></label>
                                    <select name="fuel_tanks[${i-1}][capacity]" class="form-select" required>
                                        <option value="">اختر السعة</option>
                                        ${Array.from({length: 10}, (_, j) => {
                                            const capacity = (j + 1) * 50;
                                            return `<option value="${capacity}">${capacity} لتر</option>`;
                                        }).join('')}
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">موقع الخزان ${i} <span class="text-danger">*</span></label>
                                    <select name="fuel_tanks[${i-1}][location]" class="form-select" required>
                                        <option value="">اختر الموقع</option>
                                        ${(window.GENERATOR_CONSTANTS.location && window.GENERATOR_CONSTANTS.location.length > 0) 
                                            ? window.GENERATOR_CONSTANTS.location.map(loc => `<option value="${loc.value}">${loc.label}</option>`).join('')
                                            : '<option value="ارضي">ارضي</option><option value="علوي">علوي</option><option value="تحت الارض">تحت الارض</option>'
                                        }
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">نظام الفلترة ${i}</label>
                                    <select name="fuel_tanks[${i-1}][filtration_system_available]" class="form-select">
                                        <option value="0">غير متوفر</option>
                                        <option value="1">متوفر</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">حالة الخزان ${i}</label>
                                    <input type="text" name="fuel_tanks[${i-1}][condition]" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">مادة التصنيع ${i}</label>
                                    <select name="fuel_tanks[${i-1}][material]" class="form-select">
                                        <option value="">اختر المادة</option>
                                        ${(window.GENERATOR_CONSTANTS.material && window.GENERATOR_CONSTANTS.material.length > 0) 
                                            ? window.GENERATOR_CONSTANTS.material.map(mat => `<option value="${mat.value}">${mat.label}</option>`).join('')
                                            : '<option value="حديد">حديد</option><option value="بلاستيك">بلاستيك</option><option value="مقوى">مقوى</option><option value="فايبر">فايبر</option>'
                                        }
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">استخدامه ${i}</label>
                                    <select name="fuel_tanks[${i-1}][usage]" class="form-select">
                                        <option value="">اختر الاستخدام</option>
                                        ${(window.GENERATOR_CONSTANTS.usage && window.GENERATOR_CONSTANTS.usage.length > 0) 
                                            ? window.GENERATOR_CONSTANTS.usage.map(use => `<option value="${use.value}">${use.label}</option>`).join('')
                                            : '<option value="مركزي">مركزي</option><option value="احتياطي">احتياطي</option>'
                                        }
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">طريقة القياس ${i}</label>
                                    <select name="fuel_tanks[${i-1}][measurement_method]" class="form-select">
                                        <option value="">اختر الطريقة</option>
                                        ${(window.GENERATOR_CONSTANTS.measurement_method && window.GENERATOR_CONSTANTS.measurement_method.length > 0) 
                                            ? window.GENERATOR_CONSTANTS.measurement_method.map(method => `<option value="${method.value}">${method.label}</option>`).join('')
                                            : '<option value="سيخ">سيخ</option><option value="مدرج">مدرج</option><option value="ساعه ميكانيكية">ساعه ميكانيكية</option><option value="حساس الكتروني">حساس الكتروني</option><option value="خرطوم شفاف">خرطوم شفاف</option>'
                                        }
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                fuelTanksContainer.insertAdjacentHTML('beforeend', tankHtml);
            }
        }

        // تهيئة أولية إذا كانت هناك قيم قديمة
        @if(old('external_fuel_tank') == '1' && old('fuel_tanks_count'))
            renderFuelTanks({{ old('fuel_tanks_count') }});
        @endif
    });
</script>
@endpush


