@extends('layouts.admin')

@section('title', 'إضافة مستخدم')

@php
    $authUser = auth()->user();

    $breadcrumbTitle = $authUser->isCompanyOwner() ? 'إضافة موظف/فني' : 'إضافة مستخدم';
    $breadcrumbParent = 'إدارة المستخدمين';
    $breadcrumbParentUrl = route('admin.users.index');

    $mode = 'create';
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/users.css') }}">
@endpush

@section('content')
    <div class="users-page">

        <div class="users-page-header">
            <div>
                <h1 class="users-title">{{ $breadcrumbTitle }}</h1>
                <div class="users-subtitle">
                    @if($authUser->isCompanyOwner())
                        سيتم ربط المستخدم تلقائيًا بالمشغل الخاص بك.
                    @else
                        أنشئ مستخدمًا جديدًا وحدّد الدور وربطه بالمشغل عند الحاجة.
                    @endif
                </div>
            </div>
            <a href="{{ route('admin.users.index') }}" class="btn btn-light-subtle">
                <i class="bi bi-arrow-right me-1"></i> رجوع
            </a>
        </div>

        <div class="card ui-card">
            <div class="card-header">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-person-plus text-primary"></i>
                    <div class="fw-bold">{{ $breadcrumbTitle }}</div>
                </div>
            </div>

            <div class="card-body">
                <form action="{{ route('admin.users.store') }}" method="POST">
                    @csrf

                    @include('admin.users.partials.form', [
                        'mode' => $mode,
                        'user' => null,
                        'defaultRole' => $defaultRole ?? '',
                        'operatorFieldName' => 'operator_id',
                    ])

                    <div class="mt-4 d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-light-subtle">إلغاء</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> حفظ
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const roleSelect = document.getElementById('roleSelect');
            const operatorField = document.getElementById('operatorField');

            if (!roleSelect || !operatorField) return;

            function toggleOperator() {
                const val = roleSelect.value;
                const needOp = (val === '{{ \App\Role::Employee->value }}' || val === '{{ \App\Role::Technician->value }}');

                operatorField.style.display = needOp ? '' : 'none';
                const star = document.getElementById('opReqStar');
                if (star) star.style.display = needOp ? '' : 'none';

                if (!needOp) {
                    const opSelect = document.getElementById('operatorSelect');
                    if (opSelect) opSelect.value = '';
                }
            }

            roleSelect.addEventListener('change', toggleOperator);
            toggleOperator();
        });
    </script>
@endpush
