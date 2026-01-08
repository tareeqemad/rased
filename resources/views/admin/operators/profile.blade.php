@extends('layouts.admin')

@section('title', 'ملف المشغل')
@php
    $breadcrumbTitle = 'ملف المشغل';
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/admin/css/operators.css') }}">
@endpush

@section('content')
<div class="operators-page operator-profile-page">
    <div class="row g-3">
        <div class="col-12">
            <div class="card op-card position-relative" id="profileCard">
                <div class="op-card-header d-flex align-items-center justify-content-between gap-2 flex-wrap">
                    <div>
                        <div class="op-title">
                            <i class="bi bi-ui-checks-grid me-2"></i>
                            بيانات المشغل
                        </div>
                        <div class="op-subtitle">ملف المشغل</div>
                    </div>

                    <button class="btn btn-primary" id="saveProfileBtn" type="button">
                        <i class="bi bi-save me-1"></i>
                        حفظ
                    </button>
                </div>

                <div class="card-body">
                    <form id="operatorProfileForm" action="{{ route('admin.operators.profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">اسم المشغل <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control"
                                       value="{{ old('name', $operator->name) }}"
                                       placeholder="مثال: مشغل الطاقة النظيفة">
                                <div class="form-text">الاسم الرسمي للمشغل</div>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">اسم المالك</label>
                                <input type="text" name="owner_name" id="owner_name" class="form-control"
                                       value="{{ old('owner_name', $operator->owner_name) }}"
                                       placeholder="اسم المالك">
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">رقم هوية المالك</label>
                                <input type="text" name="owner_id_number" id="owner_id_number" class="form-control"
                                       value="{{ old('owner_id_number', $operator->owner_id_number) }}"
                                       placeholder="رقم هوية المالك">
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">رقم هوية المشغل</label>
                                <input type="text" name="operator_id_number" id="operator_id_number" class="form-control"
                                       value="{{ old('operator_id_number', $operator->operator_id_number) }}"
                                       placeholder="رقم هوية المشغل">
                            </div>
                            
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <strong>ملاحظة:</strong> جميع البيانات الأخرى (الموقع، القدرات، المستفيدون، الحالة) يتم إدخالها عند إضافة وحدات التوليد في القسم أدناه.
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="d-none" id="hiddenSubmitBtn"></button>
                    </form>
                </div>
            </div>

                <div class="op-loading d-none" id="profileLoading">
                    <div class="text-center">
                        <div class="spinner-border" role="status"></div>
                        <div class="mt-2 text-muted fw-semibold">جاري الحفظ...</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- قسم وحدات التوليد --}}
        <div class="col-12">
            <div class="card op-card">
                <div class="op-card-header d-flex align-items-center justify-content-between gap-2 flex-wrap">
                    <div>
                        <div class="op-title">
                            <i class="bi bi-lightning-charge me-2"></i>
                            وحدات التوليد
                        </div>
                        <div class="op-subtitle">إدارة وحدات التوليد والمولدات التابعة لها</div>
                    </div>
                    @can('create', App\Models\GenerationUnit::class)
                        <a href="{{ route('admin.generation-units.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-1"></i>
                            إضافة وحدة توليد
                        </a>
                    @endcan
                </div>
                <div class="card-body">
                    @if($generationUnits->isEmpty())
                        <div class="text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                            <p class="text-muted mt-3">لا توجد وحدات توليد. ابدأ بإضافة وحدة توليد جديدة.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>كود الوحدة</th>
                                        <th>اسم الوحدة</th>
                                        <th class="text-center">عدد المولدات المطلوبة</th>
                                        <th class="text-center">عدد المولدات الفعلي</th>
                                        <th class="text-center">الحالة</th>
                                        <th class="text-end">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($generationUnits as $unit)
                                        <tr>
                                            <td>
                                                <code class="text-primary">{{ $unit->unit_code }}</code>
                                            </td>
                                            <td>{{ $unit->name }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-info">{{ $unit->generators_count }}</span>
                                            </td>
                                            <td class="text-center">
                                                @php
                                                    $actualCount = $unit->generators()->count();
                                                    $requiredCount = $unit->generators_count;
                                                @endphp
                                                <span class="badge {{ $actualCount >= $requiredCount ? 'bg-success' : 'bg-warning' }}">
                                                    {{ $actualCount }} / {{ $requiredCount }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                @if($unit->statusDetail)
                                                    <span class="badge {{ $unit->statusDetail->code === 'ACTIVE' ? 'bg-success' : 'bg-secondary' }}">
                                                        {{ $unit->statusDetail->label }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">غير محدد</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <div class="d-flex gap-2 justify-content-end">
                                                    @can('view', $unit)
                                                        <a href="{{ route('admin.generation-units.show', $unit) }}" class="btn btn-sm btn-outline-info" title="عرض التفاصيل">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                    @endcan
                                                    @can('update', $unit)
                                                        <a href="{{ route('admin.generation-units.edit', $unit) }}" class="btn btn-sm btn-outline-primary" title="تعديل">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                    @endcan
                                                    @can('create', App\Models\Generator::class)
                                                        <a href="{{ route('admin.generators.create', ['generation_unit_id' => $unit->id]) }}" class="btn btn-sm btn-success" title="إضافة مولد">
                                                            <i class="bi bi-plus-circle"></i> مولد
                                                        </a>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    function notify(type, msg, title) {
        if (window.adminNotifications && typeof window.adminNotifications[type] === 'function') {
            window.adminNotifications[type](msg, title);
            return;
        }
        alert(msg);
    }

    const form = document.getElementById('operatorProfileForm');
    const saveBtn = document.getElementById('saveProfileBtn');
    const loading = document.getElementById('profileLoading');

    function setLoading(on) {
        loading.classList.toggle('d-none', !on);
        saveBtn.disabled = on;
    }

    function clearErrors() {
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
    }

    function showErrors(errors) {
        const firstField = Object.keys(errors || {})[0];
        if (firstField) {
            const input = form.querySelector(`[name="${CSS.escape(firstField)}"]`);
            if (input) {
                input.classList.add('is-invalid');
                const div = document.createElement('div');
                div.className = 'invalid-feedback';
                div.textContent = errors[firstField][0];
                input.insertAdjacentElement('afterend', div);
                input.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        Object.keys(errors || {}).forEach(field => {
            const input = form.querySelector(`[name="${CSS.escape(field)}"]`);
            if (!input) return;
            input.classList.add('is-invalid');
            if (input.nextElementSibling && input.nextElementSibling.classList.contains('invalid-feedback')) return;
            const div = document.createElement('div');
            div.className = 'invalid-feedback';
            div.textContent = errors[field][0];
            input.insertAdjacentElement('afterend', div);
        });
    }

    // ====== AJAX submit ======
    async function submitProfile() {
        clearErrors();
        setLoading(true);

        try {
            const fd = new FormData(form);

            const res = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: fd
            });

            const data = await res.json();

            if (res.status === 422) {
                showErrors(data.errors || {});
                notify('error', 'تحقق من الحقول المطلوبة');
                return;
            }

            if (data && data.success) {
                notify('success', data.message || 'تم الحفظ');
                
                // تحديث بيانات المشغل
                if (data.operator) {
                    if (data.operator.name) {
                        const nameInput = document.getElementById('name');
                        if (nameInput) nameInput.value = data.operator.name;
                    }
                    if (data.operator.owner_name !== undefined) {
                        const ownerNameInput = document.getElementById('owner_name');
                        if (ownerNameInput) ownerNameInput.value = data.operator.owner_name || '';
                    }
                    if (data.operator.owner_id_number !== undefined) {
                        const ownerIdNumberInput = document.getElementById('owner_id_number');
                        if (ownerIdNumberInput) ownerIdNumberInput.value = data.operator.owner_id_number || '';
                    }
                    if (data.operator.operator_id_number !== undefined) {
                        const operatorIdNumberInput = document.getElementById('operator_id_number');
                        if (operatorIdNumberInput) operatorIdNumberInput.value = data.operator.operator_id_number || '';
                    }
                }
            } else {
                notify('error', (data && data.message) ? data.message : 'فشل الحفظ');
            }

        } catch (e) {
            notify('error', 'حدث خطأ أثناء الحفظ');
        } finally {
            setLoading(false);
        }
    }

    saveBtn.addEventListener('click', submitProfile);

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        submitProfile();
    });

})();

// إدارة وحدات التوليد
(function() {
    const btnAddUnit = document.getElementById('btnAddGenerationUnit');
    if (btnAddUnit) {
        btnAddUnit.addEventListener('click', function() {
            // TODO: فتح modal لإضافة وحدة توليد جديدة
            // سيتم إضافتها لاحقاً عند إنشاء Controller وViews لوحدات التوليد
            alert('سيتم إضافة وظيفة إضافة وحدة توليد قريباً');
        });
    }
})();
</script>
@endpush

