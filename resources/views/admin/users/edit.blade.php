@extends('layouts.admin')

@section('title', 'تعديل المستخدم')

@php
    $breadcrumbTitle = 'تعديل المستخدم';
    $breadcrumbParent = 'إدارة المستخدمين';
    $breadcrumbParentUrl = route('admin.users.index');

    $mode = 'edit';
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/users.css') }}">
@endpush

@section('content')
    <div class="users-page">

        <div class="users-page-header">
            <div>
                <h1 class="users-title">تعديل المستخدم</h1>
                <div class="users-subtitle">تعديل بيانات: <span class="fw-bold">{{ $user->name }}</span></div>
            </div>
            <a href="{{ route('admin.users.index') }}" class="btn btn-light-subtle">
                <i class="bi bi-arrow-right me-1"></i> رجوع
            </a>
        </div>

        <div class="card ui-card">
            <div class="card-header">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-person-gear text-primary"></i>
                    <div class="fw-bold">تعديل المستخدم</div>
                </div>
            </div>

            <div class="card-body">
                <form action="{{ route('admin.users.update', $user) }}" method="POST">
                    @csrf
                    @method('PUT')

                    @include('admin.users.partials.form', [
                        'mode' => $mode,
                        'user' => $user,
                        'defaultRole' => '',
                        'operatorFieldName' => 'operator_id[]',  {{-- نخليها Array للـ UpdateRequest إذا كان بتحقق على array --}}
                    ])

                    <div class="mt-4 d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-light-subtle">إلغاء</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> حفظ التغييرات
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
            }

            roleSelect.addEventListener('change', toggleOperator);
            toggleOperator();
        });
    </script>
@endpush
