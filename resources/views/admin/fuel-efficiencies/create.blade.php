@extends('layouts.admin')

@section('title', 'إضافة سجل كفاءة وقود')

@php
    $breadcrumbTitle = 'إضافة سجل كفاءة وقود';
@endphp

@section('content')
    <div class="container-fluid">
        <div class="card border-0 shadow-lg">
            <div class="card-header border-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 2rem 1.5rem;">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                         style="width: 50px; height: 50px; background: rgba(255,255,255,0.2);">
                        <i class="bi bi-speedometer2 text-white fs-4"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 fw-bold text-white">إضافة سجل كفاءة وقود جديد</h4>
                        <p class="mb-0 text-white-50 small">قم بإدخال بيانات كفاءة الوقود</p>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <form action="{{ route('admin.fuel-efficiencies.store') }}" method="POST">
                    @csrf

                    <div class="row g-3">
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

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">تاريخ الاستهلاك <span class="text-danger">*</span></label>
                            <input type="date" name="consumption_date" class="form-control @error('consumption_date') is-invalid @enderror" 
                                   value="{{ old('consumption_date', date('Y-m-d')) }}" required>
                            @error('consumption_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">ساعات التشغيل</label>
                            <input type="number" step="0.01" name="operating_hours" class="form-control @error('operating_hours') is-invalid @enderror" 
                                   value="{{ old('operating_hours') }}" min="0">
                            @error('operating_hours')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">سعر الوقود (للتر)</label>
                            <input type="number" step="0.01" name="fuel_price_per_liter" class="form-control @error('fuel_price_per_liter') is-invalid @enderror" 
                                   value="{{ old('fuel_price_per_liter') }}" min="0">
                            @error('fuel_price_per_liter')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">كفاءة استهلاك الوقود (%)</label>
                            <input type="number" step="0.01" name="fuel_efficiency_percentage" class="form-control @error('fuel_efficiency_percentage') is-invalid @enderror" 
                                   value="{{ old('fuel_efficiency_percentage') }}" min="0" max="100">
                            @error('fuel_efficiency_percentage')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">مقارنة كفاءة الوقود مع المعيار</label>
                            <select name="fuel_efficiency_comparison" class="form-select @error('fuel_efficiency_comparison') is-invalid @enderror">
                                <option value="">اختر المقارنة</option>
                                <option value="within_standard" {{ old('fuel_efficiency_comparison') === 'within_standard' ? 'selected' : '' }}>ضمن المعدل</option>
                                <option value="above" {{ old('fuel_efficiency_comparison') === 'above' ? 'selected' : '' }}>أعلى</option>
                                <option value="below" {{ old('fuel_efficiency_comparison') === 'below' ? 'selected' : '' }}>أقل</option>
                            </select>
                            @error('fuel_efficiency_comparison')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">كفاءة توزيع الطاقة (%)</label>
                            <input type="number" step="0.01" name="energy_distribution_efficiency" class="form-control @error('energy_distribution_efficiency') is-invalid @enderror" 
                                   value="{{ old('energy_distribution_efficiency') }}" min="0" max="100">
                            @error('energy_distribution_efficiency')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">مقارنة كفاءة الطاقة مع المعيار</label>
                            <select name="energy_efficiency_comparison" class="form-select @error('energy_efficiency_comparison') is-invalid @enderror">
                                <option value="">اختر المقارنة</option>
                                <option value="within_standard" {{ old('energy_efficiency_comparison') === 'within_standard' ? 'selected' : '' }}>ضمن المعدل</option>
                                <option value="above" {{ old('energy_efficiency_comparison') === 'above' ? 'selected' : '' }}>أعلى</option>
                                <option value="below" {{ old('energy_efficiency_comparison') === 'below' ? 'selected' : '' }}>أقل</option>
                            </select>
                            @error('energy_efficiency_comparison')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">تكلفة التشغيل الإجمالية</label>
                            <input type="number" step="0.01" name="total_operating_cost" class="form-control @error('total_operating_cost') is-invalid @enderror" 
                                   value="{{ old('total_operating_cost') }}" min="0">
                            @error('total_operating_cost')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('admin.fuel-efficiencies.index') }}" class="btn btn-secondary">إلغاء</a>
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

