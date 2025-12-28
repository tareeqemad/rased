@extends('layouts.admin')

@section('title', 'تعديل سجل تشغيل')

@php
    $breadcrumbTitle = 'تعديل سجل تشغيل';
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/operation-logs.css') }}">
@endpush

@section('content')
    <div class="operation-logs-page">
        <div class="row g-3">
            <div class="col-12">
                <div class="log-card">
                    <div class="log-card-header">
                        <div>
                            <h5 class="log-title">
                                <i class="bi bi-pencil-square me-2"></i>
                                تعديل سجل التشغيل
                            </h5>
                            <div class="log-subtitle">
                                المولد: {{ $operationLog->generator->name }} | 
                                التاريخ: {{ $operationLog->operation_date->format('Y-m-d') }}
                            </div>
                        </div>
                        <a href="{{ route('admin.operation-logs.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-right me-2"></i>
                            العودة للقائمة
                        </a>
                    </div>

                    <form action="{{ route('admin.operation-logs.update', $operationLog) }}" method="POST" id="operationLogForm">
                        @csrf
                        @method('PUT')

                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <h6 class="fw-bold mb-3">
                                        <i class="bi bi-info-circle text-primary me-2"></i>
                                        المعلومات الأساسية
                                    </h6>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        المشغل <span class="text-danger">*</span>
                                    </label>
                                    <select name="operator_id" id="operator_id" 
                                            class="form-select @error('operator_id') is-invalid @enderror" 
                                            @if(auth()->user()->isCompanyOwner()) disabled @endif>
                                        <option value="">اختر المشغل</option>
                                        @foreach($operators as $operator)
                                            <option value="{{ $operator->id }}" 
                                                    {{ old('operator_id', $operationLog->operator_id) == $operator->id ? 'selected' : '' }}>
                                                {{ $operator->name }}
                                                @if($operator->unit_number)
                                                    - {{ $operator->unit_number }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @if(auth()->user()->isCompanyOwner())
                                        <input type="hidden" name="operator_id" value="{{ $operationLog->operator_id }}">
                                    @endif
                                    @error('operator_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        المولد <span class="text-danger">*</span>
                                    </label>
                                    <select name="generator_id" id="generator_id" 
                                            class="form-select @error('generator_id') is-invalid @enderror">
                                        <option value="">اختر المولد</option>
                                        @foreach($generators as $generator)
                                            <option value="{{ $generator->id }}" 
                                                    data-operator-id="{{ $generator->operator_id }}"
                                                    {{ old('generator_id', $operationLog->generator_id) == $generator->id ? 'selected' : '' }}>
                                                {{ $generator->generator_number }} - {{ $generator->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('generator_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="row g-3">
                                <div class="col-12">
                                    <h6 class="fw-bold mb-3">
                                        <i class="bi bi-calendar-event text-success me-2"></i>
                                        التاريخ والوقت
                                    </h6>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        تاريخ التشغيل <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" name="operation_date" 
                                           class="form-control @error('operation_date') is-invalid @enderror" 
                                           value="{{ old('operation_date', $operationLog->operation_date->format('Y-m-d')) }}">
                                    @error('operation_date')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        وقت البدء <span class="text-danger">*</span>
                                    </label>
                                    <input type="time" name="start_time" 
                                           class="form-control @error('start_time') is-invalid @enderror" 
                                           value="{{ old('start_time', $operationLog->start_time ? $operationLog->start_time->format('H:i') : '') }}">
                                    @error('start_time')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        وقت الإيقاف <span class="text-danger">*</span>
                                    </label>
                                    <input type="time" name="end_time" 
                                           class="form-control @error('end_time') is-invalid @enderror" 
                                           value="{{ old('end_time', $operationLog->end_time ? $operationLog->end_time->format('H:i') : '') }}">
                                    @error('end_time')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="row g-3">
                                <div class="col-12">
                                    <h6 class="fw-bold mb-3">
                                        <i class="bi bi-speedometer2 text-warning me-2"></i>
                                        الأداء ونسبة التحميل
                                    </h6>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        نسبة التحميل (%)
                                    </label>
                                    <input type="number" step="0.01" name="load_percentage" 
                                           class="form-control @error('load_percentage') is-invalid @enderror" 
                                           value="{{ old('load_percentage', $operationLog->load_percentage) }}" 
                                           min="0" max="100" 
                                           placeholder="0.00">
                                    <small class="text-muted">النسبة المئوية للتحميل أثناء التشغيل</small>
                                    @error('load_percentage')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="row g-3">
                                <div class="col-12">
                                    <h6 class="fw-bold mb-3">
                                        <i class="bi bi-fuel-pump text-danger me-2"></i>
                                        قراءات عداد الوقود
                                    </h6>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        قراءة عداد الوقود عند البدء
                                    </label>
                                    <input type="number" step="0.01" name="fuel_meter_start" 
                                           class="form-control @error('fuel_meter_start') is-invalid @enderror" 
                                           value="{{ old('fuel_meter_start', $operationLog->fuel_meter_start) }}" 
                                           min="0" 
                                           placeholder="0.00">
                                    @error('fuel_meter_start')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        قراءة عداد الوقود عند الانتهاء
                                    </label>
                                    <input type="number" step="0.01" name="fuel_meter_end" 
                                           class="form-control @error('fuel_meter_end') is-invalid @enderror" 
                                           value="{{ old('fuel_meter_end', $operationLog->fuel_meter_end) }}" 
                                           min="0" 
                                           placeholder="0.00">
                                    @error('fuel_meter_end')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        كمية الوقود المستهلك (لتر)
                                    </label>
                                    <input type="number" step="0.01" name="fuel_consumed" 
                                           class="form-control calculated-field @error('fuel_consumed') is-invalid @enderror" 
                                           value="{{ old('fuel_consumed', $operationLog->fuel_consumed) }}" 
                                           min="0" 
                                           placeholder="0.00"
                                           id="fuel_consumed"
                                           readonly
                                           tabindex="-1">
                                    <small class="text-muted">يتم الحساب تلقائياً من قراءات البداية والنهاية</small>
                                    @error('fuel_consumed')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="row g-3">
                                <div class="col-12">
                                    <h6 class="fw-bold mb-3">
                                        <i class="bi bi-lightning-charge text-warning me-2"></i>
                                        قراءات عداد الطاقة
                                    </h6>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        قراءة عداد الطاقة عند البدء
                                    </label>
                                    <input type="number" step="0.01" name="energy_meter_start" 
                                           class="form-control @error('energy_meter_start') is-invalid @enderror" 
                                           value="{{ old('energy_meter_start', $operationLog->energy_meter_start) }}" 
                                           min="0" 
                                           placeholder="0.00">
                                    @error('energy_meter_start')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        قراءة عداد الطاقة عند الإيقاف
                                    </label>
                                    <input type="number" step="0.01" name="energy_meter_end" 
                                           class="form-control @error('energy_meter_end') is-invalid @enderror" 
                                           value="{{ old('energy_meter_end', $operationLog->energy_meter_end) }}" 
                                           min="0" 
                                           placeholder="0.00">
                                    @error('energy_meter_end')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        كمية الطاقة المنتجة (kWh)
                                    </label>
                                    <input type="number" step="0.01" name="energy_produced" 
                                           class="form-control calculated-field @error('energy_produced') is-invalid @enderror" 
                                           value="{{ old('energy_produced', $operationLog->energy_produced) }}" 
                                           min="0" 
                                           placeholder="0.00"
                                           id="energy_produced"
                                           readonly
                                           tabindex="-1">
                                    <small class="text-muted">يتم الحساب تلقائياً من قراءات البداية والنهاية</small>
                                    @error('energy_produced')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="row g-3">
                                <div class="col-12">
                                    <h6 class="fw-bold mb-3">
                                        <i class="bi bi-journal-text text-primary me-2"></i>
                                        الملاحظات والأعطال
                                    </h6>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        ملاحظات تشغيلية
                                    </label>
                                    <textarea name="operational_notes" 
                                              class="form-control @error('operational_notes') is-invalid @enderror" 
                                              rows="4" 
                                              placeholder="أدخل أي ملاحظات متعلقة بتشغيل المولد...">{{ old('operational_notes', $operationLog->operational_notes) }}</textarea>
                                    @error('operational_notes')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        الأعطال المسجلة (إن وجدت)
                                    </label>
                                    <textarea name="malfunctions" 
                                              class="form-control @error('malfunctions') is-invalid @enderror" 
                                              rows="4" 
                                              placeholder="أدخل تفاصيل أي أعطال تمت ملاحظتها...">{{ old('malfunctions', $operationLog->malfunctions) }}</textarea>
                                    @error('malfunctions')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="d-flex justify-content-end align-items-center gap-2">
                                <a href="{{ route('admin.operation-logs.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-right me-2"></i>
                                    إلغاء
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-2"></i>
                                    حفظ التعديلات
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('assets/admin/libs/jquery/jquery.min.js') }}"></script>
<script>
    (function($) {
        $(document).ready(function() {
            const $form = $('#operationLogForm');
            const $submitBtn = $form.find('button[type="submit"]');

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
                
                operatorSelect.dispatchEvent(new Event('change'));
            }
            
            const fuelStart = document.querySelector('input[name="fuel_meter_start"]');
            const fuelEnd = document.querySelector('input[name="fuel_meter_end"]');
            const fuelConsumed = document.getElementById('fuel_consumed');
            
            function calculateFuel() {
                if (fuelStart && fuelEnd && fuelConsumed) {
                    const start = parseFloat(fuelStart.value) || 0;
                    const end = parseFloat(fuelEnd.value) || 0;
                    if (start > 0 && end > 0 && end >= start) {
                        fuelConsumed.value = (end - start).toFixed(2);
                    } else {
                        fuelConsumed.value = '';
                    }
                }
            }
            
            if (fuelStart) fuelStart.addEventListener('input', calculateFuel);
            if (fuelEnd) fuelEnd.addEventListener('input', calculateFuel);
            
            const energyStart = document.querySelector('input[name="energy_meter_start"]');
            const energyEnd = document.querySelector('input[name="energy_meter_end"]');
            const energyProduced = document.getElementById('energy_produced');
            
            function calculateEnergy() {
                if (energyStart && energyEnd && energyProduced) {
                    const start = parseFloat(energyStart.value) || 0;
                    const end = parseFloat(energyEnd.value) || 0;
                    if (start > 0 && end > 0 && end >= start) {
                        energyProduced.value = (end - start).toFixed(2);
                    } else {
                        energyProduced.value = '';
                    }
                }
            }
            
            if (energyStart) energyStart.addEventListener('input', calculateEnergy);
            if (energyEnd) energyEnd.addEventListener('input', calculateEnergy);

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
                
                // Remove calculated fields from form data (server will calculate them)
                formData.delete('fuel_consumed');
                formData.delete('energy_produced');

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
                                window.showToast(response.message || 'تم تحديث سجل التشغيل بنجاح', 'success');
                            } else {
                                alert(response.message || 'تم تحديث سجل التشغيل بنجاح');
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
