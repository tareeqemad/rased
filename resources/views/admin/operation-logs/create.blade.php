@extends('layouts.admin')

@section('title', 'إضافة سجل تشغيل')

@php
    $breadcrumbTitle = 'إضافة سجل تشغيل';
@endphp

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <!-- Header Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body bg-gradient-primary text-white p-4">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                                 style="width: 60px; height: 60px; background: rgba(255,255,255,0.25);">
                                <i class="bi bi-journal-plus text-white fs-3"></i>
                            </div>
                            <div>
                                <h3 class="mb-1 fw-bold text-white">إضافة سجل تشغيل جديد</h3>
                                <p class="mb-0 text-white-50">قم بإدخال بيانات سجل التشغيل بشكل كامل</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Form Card -->
                <form action="{{ route('admin.operation-logs.store') }}" method="POST" id="operationLogForm">
                    @csrf

                    <!-- Basic Information Section -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0 fw-bold">
                                <i class="bi bi-info-circle-fill text-primary me-2"></i>
                                المعلومات الأساسية
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-building text-info me-1"></i>
                                        المشغل <span class="text-danger">*</span>
                                    </label>
                                    <select name="operator_id" id="operator_id" 
                                            class="form-select form-select-lg @error('operator_id') is-invalid @enderror" 
                                            @if(auth()->user()->isCompanyOwner()) disabled @endif>
                                        <option value="">اختر المشغل</option>
                                        @foreach($operators as $operator)
                                            <option value="{{ $operator->id }}" 
                                                    {{ old('operator_id', auth()->user()->isCompanyOwner() ? auth()->user()->ownedOperators()->first()->id : '') == $operator->id ? 'selected' : '' }}>
                                                {{ $operator->name }}
                                                @if($operator->unit_number)
                                                    - {{ $operator->unit_number }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @if(auth()->user()->isCompanyOwner())
                                        <input type="hidden" name="operator_id" value="{{ auth()->user()->ownedOperators()->first()->id }}">
                                    @endif
                                    @error('operator_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-lightning-charge-fill text-warning me-1"></i>
                                        المولد <span class="text-danger">*</span>
                                    </label>
                                    <select name="generator_id" id="generator_id" 
                                            class="form-select form-select-lg @error('generator_id') is-invalid @enderror">
                                        <option value="">اختر المولد</option>
                                        @foreach($generators as $generator)
                                            <option value="{{ $generator->id }}" 
                                                    data-operator-id="{{ $generator->operator_id }}"
                                                    {{ old('generator_id') == $generator->id ? 'selected' : '' }}>
                                                {{ $generator->generator_number }} - {{ $generator->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('generator_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Date & Time Section -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0 fw-bold">
                                <i class="bi bi-calendar-event text-success me-2"></i>
                                التاريخ والوقت
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-calendar3 text-primary me-1"></i>
                                        تاريخ التشغيل <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" name="operation_date" 
                                           class="form-control form-control-lg @error('operation_date') is-invalid @enderror" 
                                           value="{{ old('operation_date', date('Y-m-d')) }}">
                                    @error('operation_date')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-clock text-info me-1"></i>
                                        وقت البدء <span class="text-danger">*</span>
                                    </label>
                                    <input type="time" name="start_time" 
                                           class="form-control form-control-lg @error('start_time') is-invalid @enderror" 
                                           value="{{ old('start_time') }}">
                                    @error('start_time')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-clock-history text-danger me-1"></i>
                                        وقت الإيقاف <span class="text-danger">*</span>
                                    </label>
                                    <input type="time" name="end_time" 
                                           class="form-control form-control-lg @error('end_time') is-invalid @enderror" 
                                           value="{{ old('end_time') }}">
                                    @error('end_time')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-12">
                                    <div class="alert alert-info mb-0">
                                        <i class="bi bi-info-circle me-2"></i>
                                        <strong>ملاحظة:</strong> سيتم حساب مدة التشغيل تلقائياً بناءً على وقت البدء والإيقاف
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Load & Performance Section -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0 fw-bold">
                                <i class="bi bi-speedometer2 text-warning me-2"></i>
                                الأداء ونسبة التحميل
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-percent text-primary me-1"></i>
                                        نسبة التحميل (%)
                                    </label>
                                    <input type="number" step="0.01" name="load_percentage" 
                                           class="form-control form-control-lg @error('load_percentage') is-invalid @enderror" 
                                           value="{{ old('load_percentage') }}" 
                                           min="0" max="100" 
                                           placeholder="0.00">
                                    <small class="text-muted">النسبة المئوية للتحميل أثناء التشغيل</small>
                                    @error('load_percentage')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Fuel Meter Section -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0 fw-bold">
                                <i class="bi bi-fuel-pump text-danger me-2"></i>
                                قراءات عداد الوقود
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-arrow-right-circle text-success me-1"></i>
                                        قراءة عداد الوقود عند البدء
                                    </label>
                                    <input type="number" step="0.01" name="fuel_meter_start" 
                                           class="form-control form-control-lg @error('fuel_meter_start') is-invalid @enderror" 
                                           value="{{ old('fuel_meter_start') }}" 
                                           min="0" 
                                           placeholder="0.00">
                                    @error('fuel_meter_start')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-arrow-left-circle text-danger me-1"></i>
                                        قراءة عداد الوقود عند الانتهاء
                                    </label>
                                    <input type="number" step="0.01" name="fuel_meter_end" 
                                           class="form-control form-control-lg @error('fuel_meter_end') is-invalid @enderror" 
                                           value="{{ old('fuel_meter_end') }}" 
                                           min="0" 
                                           placeholder="0.00">
                                    @error('fuel_meter_end')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-calculator text-info me-1"></i>
                                        كمية الوقود المستهلك (لتر)
                                    </label>
                                    <input type="number" step="0.01" name="fuel_consumed" 
                                           class="form-control form-control-lg @error('fuel_consumed') is-invalid @enderror" 
                                           value="{{ old('fuel_consumed') }}" 
                                           min="0" 
                                           placeholder="0.00"
                                           id="fuel_consumed">
                                    <small class="text-muted">سيتم الحساب تلقائياً إذا تم إدخال قراءات البداية والنهاية</small>
                                    @error('fuel_consumed')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Energy Meter Section -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0 fw-bold">
                                <i class="bi bi-lightning-charge text-warning me-2"></i>
                                قراءات عداد الطاقة
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-arrow-right-circle text-success me-1"></i>
                                        قراءة عداد الطاقة عند البدء
                                    </label>
                                    <input type="number" step="0.01" name="energy_meter_start" 
                                           class="form-control form-control-lg @error('energy_meter_start') is-invalid @enderror" 
                                           value="{{ old('energy_meter_start') }}" 
                                           min="0" 
                                           placeholder="0.00">
                                    @error('energy_meter_start')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-arrow-left-circle text-danger me-1"></i>
                                        قراءة عداد الطاقة عند الإيقاف
                                    </label>
                                    <input type="number" step="0.01" name="energy_meter_end" 
                                           class="form-control form-control-lg @error('energy_meter_end') is-invalid @enderror" 
                                           value="{{ old('energy_meter_end') }}" 
                                           min="0" 
                                           placeholder="0.00">
                                    @error('energy_meter_end')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-calculator text-info me-1"></i>
                                        كمية الطاقة المنتجة (kWh)
                                    </label>
                                    <input type="number" step="0.01" name="energy_produced" 
                                           class="form-control form-control-lg @error('energy_produced') is-invalid @enderror" 
                                           value="{{ old('energy_produced') }}" 
                                           min="0" 
                                           placeholder="0.00"
                                           id="energy_produced">
                                    <small class="text-muted">سيتم الحساب تلقائياً إذا تم إدخال قراءات البداية والنهاية</small>
                                    @error('energy_produced')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes & Malfunctions Section -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0 fw-bold">
                                <i class="bi bi-journal-text text-primary me-2"></i>
                                الملاحظات والأعطال
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-sticky text-info me-1"></i>
                                        ملاحظات تشغيلية
                                    </label>
                                    <textarea name="operational_notes" 
                                              class="form-control @error('operational_notes') is-invalid @enderror" 
                                              rows="4" 
                                              placeholder="أدخل أي ملاحظات متعلقة بتشغيل المولد...">{{ old('operational_notes') }}</textarea>
                                    @error('operational_notes')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-exclamation-triangle text-danger me-1"></i>
                                        الأعطال المسجلة (إن وجدت)
                                    </label>
                                    <textarea name="malfunctions" 
                                              class="form-control @error('malfunctions') is-invalid @enderror" 
                                              rows="4" 
                                              placeholder="أدخل تفاصيل أي أعطال تمت ملاحظتها...">{{ old('malfunctions') }}</textarea>
                                    @error('malfunctions')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('admin.operation-logs.index') }}" class="btn btn-outline-secondary btn-lg">
                                    <i class="bi bi-arrow-right me-2"></i>
                                    إلغاء
                                </a>
                                <button type="submit" class="btn btn-primary btn-lg px-5">
                                    <i class="bi bi-check-lg me-2"></i>
                                    حفظ البيانات
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #0056b3 0%, #003d82 100%) !important;
    }
    .form-control-lg, .form-select-lg {
        font-size: 1rem;
        padding: 0.75rem 1rem;
    }
    .card {
        transition: box-shadow 0.2s ease;
    }
    .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1) !important;
    }
    .form-label {
        color: #495057;
        margin-bottom: 0.5rem;
    }
    .form-label i {
        font-size: 1.1rem;
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/admin/libs/jquery/jquery.min.js') }}"></script>
<script>
    (function($) {
        $(document).ready(function() {
            const $form = $('#operationLogForm');
            const $submitBtn = $form.find('button[type="submit"]');

            // Filter generators based on operator
            const operatorSelect = document.getElementById('operator_id');
            const generatorSelect = document.getElementById('generator_id');
            
            if (operatorSelect && generatorSelect && !operatorSelect.disabled) {
                operatorSelect.addEventListener('change', function() {
                    const operatorId = parseInt(this.value);
                    const currentValue = generatorSelect.value;
                    
                    Array.from(generatorSelect.options).forEach(option => {
                        if (option.value) {
                            const optionOperatorId = parseInt(option.dataset.operatorId);
                            if (operatorId && optionOperatorId === operatorId) {
                                option.style.display = '';
                            } else if (!operatorId) {
                                option.style.display = '';
                            } else {
                                option.style.display = 'none';
                                if (option.value === currentValue) {
                                    generatorSelect.value = '';
                                }
                            }
                        }
                    });
                });
                
                @if(old('operator_id') || auth()->user()->isCompanyOwner())
                    operatorSelect.dispatchEvent(new Event('change'));
                @endif
            }
            
            // Auto-calculate fuel consumed
            const fuelStart = document.querySelector('input[name="fuel_meter_start"]');
            const fuelEnd = document.querySelector('input[name="fuel_meter_end"]');
            const fuelConsumed = document.getElementById('fuel_consumed');
            
            function calculateFuel() {
                if (fuelStart && fuelEnd && fuelConsumed) {
                    const start = parseFloat(fuelStart.value) || 0;
                    const end = parseFloat(fuelEnd.value) || 0;
                    if (start > 0 && end > 0 && end >= start) {
                        fuelConsumed.value = (end - start).toFixed(2);
                    }
                }
            }
            
            if (fuelStart) fuelStart.addEventListener('input', calculateFuel);
            if (fuelEnd) fuelEnd.addEventListener('input', calculateFuel);
            
            // Auto-calculate energy produced
            const energyStart = document.querySelector('input[name="energy_meter_start"]');
            const energyEnd = document.querySelector('input[name="energy_meter_end"]');
            const energyProduced = document.getElementById('energy_produced');
            
            function calculateEnergy() {
                if (energyStart && energyEnd && energyProduced) {
                    const start = parseFloat(energyStart.value) || 0;
                    const end = parseFloat(energyEnd.value) || 0;
                    if (start > 0 && end > 0 && end >= start) {
                        energyProduced.value = (end - start).toFixed(2);
                    }
                }
            }
            
            if (energyStart) energyStart.addEventListener('input', calculateEnergy);
            if (energyEnd) energyEnd.addEventListener('input', calculateEnergy);

            // AJAX form submission
            $form.on('submit', function(e) {
                e.preventDefault();
                
                if (!$form[0].checkValidity()) {
                    $form[0].reportValidity();
                    return false;
                }

                $submitBtn.prop('disabled', true);
                const originalText = $submitBtn.html();
                $submitBtn.html('<span class="spinner-border spinner-border-sm me-2"></span>جاري الحفظ...');

                const formData = new FormData(this);

                $.ajax({
                    url: $form.attr('action'),
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    success: function(response) {
                        if (response.success) {
                            if (typeof window.showToast === 'function') {
                                window.showToast(response.message || 'تم إنشاء سجل التشغيل بنجاح', 'success');
                            } else {
                                alert(response.message || 'تم إنشاء سجل التشغيل بنجاح');
                            }
                            setTimeout(function() {
                                window.location.href = '{{ route('admin.operation-logs.index') }}';
                            }, 500);
                        }
                    },
                    error: function(xhr) {
                        $submitBtn.prop('disabled', false);
                        $submitBtn.html(originalText);

                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON?.errors || {};
                            let firstError = '';
                            
                            // Clear previous errors
                            $form.find('.is-invalid').removeClass('is-invalid');
                            $form.find('.invalid-feedback').remove();

                            $.each(errors, function(field, messages) {
                                const $field = $form.find('[name="' + field + '"]');
                                if ($field.length) {
                                    $field.addClass('is-invalid');
                                    const errorMsg = Array.isArray(messages) ? messages[0] : messages;
                                    if (!firstError) firstError = errorMsg;
                                    $field.after('<div class="invalid-feedback d-block">' + errorMsg + '</div>');
                                }
                            });

                            if (typeof window.showToast === 'function') {
                                window.showToast(firstError || 'يرجى التحقق من الحقول المطلوبة', 'error');
                            } else {
                                alert(firstError || 'يرجى التحقق من الحقول المطلوبة');
                            }
                        } else {
                            const errorMsg = xhr.responseJSON?.message || 'حدث خطأ أثناء حفظ البيانات';
                            if (typeof window.showToast === 'function') {
                                window.showToast(errorMsg, 'error');
                            } else {
                                alert(errorMsg);
                            }
                        }
                    }
                });
            });
        });
    })(jQuery);
</script>
@endpush
