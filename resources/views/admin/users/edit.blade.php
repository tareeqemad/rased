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
        <div class="row g-3">
            <div class="col-12">
                <div class="users-card">
                    <div class="users-card-header">
                        <div>
                            <h5 class="users-title">
                                <i class="bi bi-person-gear me-2"></i>
                                تعديل المستخدم
                            </h5>
                            <div class="users-subtitle">تعديل بيانات: <span class="fw-bold">{{ $user->name }}</span></div>
                        </div>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-right me-2"></i>
                            رجوع
                        </a>
                    </div>

                    <form action="{{ route('admin.users.update', $user) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="card-body">
                            @include('admin.users.partials.form', [
                                'mode' => $mode,
                                'user' => $user,
                                'defaultRole' => '',
                                'operatorFieldName' => 'operator_id[]',
                            ])

                            <hr class="my-4">

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">إلغاء</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-1"></i> حفظ التغييرات
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
