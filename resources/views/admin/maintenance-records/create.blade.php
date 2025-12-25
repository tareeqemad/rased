@extends('layouts.admin')

@section('title', 'إضافة سجل صيانة')

@php
    $breadcrumbTitle = 'إضافة سجل صيانة';
@endphp

@section('content')
    <div class="container-fluid">
        <div class="card border-0 shadow-lg">
            <div class="card-header border-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 2rem 1.5rem;">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                         style="width: 50px; height: 50px; background: rgba(255,255,255,0.2);">
                        <i class="bi bi-tools text-white fs-4"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 fw-bold text-white">إضافة سجل صيانة جديد</h4>
                        <p class="mb-0 text-white-50 small">قم بإدخال بيانات سجل الصيانة</p>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <form action="{{ route('admin.maintenance-records.store') }}" method="POST">
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
                            
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">نوع الصيانة <span class="text-danger">*</span></label>
                            <select name="maintenance_type" class="form-select @error('maintenance_type') is-invalid @enderror" required>
                                <option value="">اختر النوع</option>
                                <option value="periodic" {{ old('maintenance_type') === 'periodic' ? 'selected' : '' }}>دورية</option>
                                <option value="emergency" {{ old('maintenance_type') === 'emergency' ? 'selected' : '' }}>طارئة</option>
                            </select>
                            
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">تاريخ الصيانة <span class="text-danger">*</span></label>
                            <input type="date" name="maintenance_date" class="form-control @error('maintenance_date') is-invalid @enderror" 
                                   value="{{ old('maintenance_date', date('Y-m-d')) }}" required>
                            
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">اسم الفني المسؤول</label>
                            <input type="text" name="technician_name" class="form-control @error('technician_name') is-invalid @enderror" 
                                   value="{{ old('technician_name') }}">
                            
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold">الأعمال المنفذة</label>
                            <textarea name="work_performed" class="form-control @error('work_performed') is-invalid @enderror" rows="4">{{ old('work_performed') }}</textarea>
                            
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">زمن التوقف (ساعات)</label>
                            <input type="number" step="0.01" name="downtime_hours" class="form-control @error('downtime_hours') is-invalid @enderror" 
                                   value="{{ old('downtime_hours') }}" min="0">
                            
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">تكلفة الصيانة</label>
                            <input type="number" step="0.01" name="maintenance_cost" class="form-control @error('maintenance_cost') is-invalid @enderror" 
                                   value="{{ old('maintenance_cost') }}" min="0">
                            
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('admin.maintenance-records.index') }}" class="btn btn-secondary">إلغاء</a>
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

