@extends('layouts.admin')

@section('title', 'إضافة سجل تشغيل')

@php
    $breadcrumbTitle = 'إضافة سجل تشغيل';
@endphp

@section('content')
    <div class="container-fluid">
        <div class="card border-0 shadow-lg">
            <div class="card-header border-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 2rem 1.5rem;">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                         style="width: 50px; height: 50px; background: rgba(255,255,255,0.2);">
                        <i class="bi bi-journal-text text-white fs-4"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 fw-bold text-white">إضافة سجل تشغيل جديد</h4>
                        <p class="mb-0 text-white-50 small">قم بإدخال بيانات سجل التشغيل</p>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <form action="{{ route('admin.operation-logs.store') }}" method="POST">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">المشغل <span class="text-danger">*</span></label>
                            <select name="operator_id" class="form-select @error('operator_id') is-invalid @enderror" required
                                @if(auth()->user()->isCompanyOwner()) disabled @endif>
                                <option value="">اختر المشغل</option>
                                @foreach($operators as $operator)
                                    <option value="{{ $operator->id }}" 
                                            {{ old('operator_id', auth()->user()->isCompanyOwner() ? auth()->user()->ownedOperators()->first()->id : '') == $operator->id ? 'selected' : '' }}>
                                        {{ $operator->name }}
                                    </option>
                                @endforeach
                            </select>
                            @if(auth()->user()->isCompanyOwner())
                                <input type="hidden" name="operator_id" value="{{ auth()->user()->ownedOperators()->first()->id }}">
                            @endif
                            @error('operator_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">المولد <span class="text-danger">*</span></label>
                            <select name="generator_id" class="form-select @error('generator_id') is-invalid @enderror" required>
                                <option value="">اختر المولد</option>
                                @foreach($generators as $generator)
                                    <option value="{{ $generator->id }}" {{ old('generator_id') == $generator->id ? 'selected' : '' }}>
                                        {{ $generator->generator_number }} - {{ $generator->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('generator_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">تاريخ التشغيل <span class="text-danger">*</span></label>
                            <input type="date" name="operation_date" class="form-control @error('operation_date') is-invalid @enderror" 
                                   value="{{ old('operation_date', date('Y-m-d')) }}" required>
                            @error('operation_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">وقت البدء <span class="text-danger">*</span></label>
                            <input type="time" name="start_time" class="form-control @error('start_time') is-invalid @enderror" 
                                   value="{{ old('start_time') }}" required>
                            @error('start_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">وقت الإيقاف <span class="text-danger">*</span></label>
                            <input type="time" name="end_time" class="form-control @error('end_time') is-invalid @enderror" 
                                   value="{{ old('end_time') }}" required>
                            @error('end_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">نسبة التحميل (%)</label>
                            <input type="number" step="0.01" name="load_percentage" class="form-control @error('load_percentage') is-invalid @enderror" 
                                   value="{{ old('load_percentage') }}" min="0" max="100">
                            @error('load_percentage')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">قراءة عداد الوقود عند البدء</label>
                            <input type="number" step="0.01" name="fuel_meter_start" class="form-control @error('fuel_meter_start') is-invalid @enderror" 
                                   value="{{ old('fuel_meter_start') }}" min="0">
                            @error('fuel_meter_start')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">قراءة عداد الوقود عند الانتهاء</label>
                            <input type="number" step="0.01" name="fuel_meter_end" class="form-control @error('fuel_meter_end') is-invalid @enderror" 
                                   value="{{ old('fuel_meter_end') }}" min="0">
                            @error('fuel_meter_end')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">كمية الوقود المستهلك (لتر)</label>
                            <input type="number" step="0.01" name="fuel_consumed" class="form-control @error('fuel_consumed') is-invalid @enderror" 
                                   value="{{ old('fuel_consumed') }}" min="0">
                            @error('fuel_consumed')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">قراءة عداد الطاقة عند البدء</label>
                            <input type="number" step="0.01" name="energy_meter_start" class="form-control @error('energy_meter_start') is-invalid @enderror" 
                                   value="{{ old('energy_meter_start') }}" min="0">
                            @error('energy_meter_start')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">قراءة عداد الطاقة عند الإيقاف</label>
                            <input type="number" step="0.01" name="energy_meter_end" class="form-control @error('energy_meter_end') is-invalid @enderror" 
                                   value="{{ old('energy_meter_end') }}" min="0">
                            @error('energy_meter_end')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">كمية الطاقة المنتجة (kWh)</label>
                            <input type="number" step="0.01" name="energy_produced" class="form-control @error('energy_produced') is-invalid @enderror" 
                                   value="{{ old('energy_produced') }}" min="0">
                            @error('energy_produced')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold">ملاحظات تشغيلية</label>
                            <textarea name="operational_notes" class="form-control @error('operational_notes') is-invalid @enderror" rows="3">{{ old('operational_notes') }}</textarea>
                            @error('operational_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold">الأعطال المسجلة (إن وجدت)</label>
                            <textarea name="malfunctions" class="form-control @error('malfunctions') is-invalid @enderror" rows="3">{{ old('malfunctions') }}</textarea>
                            @error('malfunctions')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('admin.operation-logs.index') }}" class="btn btn-secondary">إلغاء</a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-lg me-2"></i>
                            حفظ البيانات
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

