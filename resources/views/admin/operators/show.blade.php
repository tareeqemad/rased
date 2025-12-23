@extends('layouts.admin')

@section('title', 'تفاصيل المشغل')

@php
    $breadcrumbTitle = 'تفاصيل المشغل';
    $breadcrumbParent = 'إدارة المشغلين';
    $breadcrumbParentUrl = route('admin.operators.index');
@endphp

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="avatar-circle-lg mx-auto mb-3">{{ substr($operator->name, 0, 1) }}</div>
                        <h4 class="fw-bold">{{ $operator->name }}</h4>
                        @if($operator->email)
                            <p class="text-muted mb-3">{{ $operator->email }}</p>
                        @endif
                        <div class="d-flex gap-2 justify-content-center">
                            @can('update', $operator)
                                <a href="{{ route('admin.operators.edit', $operator) }}" class="btn btn-primary">
                                    <i class="bi bi-pencil me-2"></i>
                                    تعديل
                                </a>
                            @endcan
                            <a href="{{ route('admin.operators.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-2"></i>
                                رجوع
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-bold">معلومات المشغل</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <th width="200">اسم المشغل:</th>
                                <td>{{ $operator->name }}</td>
                            </tr>
                            @if($operator->email)
                                <tr>
                                    <th>البريد الإلكتروني:</th>
                                    <td>{{ $operator->email }}</td>
                                </tr>
                            @endif
                            @if($operator->phone)
                                <tr>
                                    <th>الهاتف:</th>
                                    <td>{{ $operator->phone }}</td>
                                </tr>
                            @endif
                            @if($operator->address)
                                <tr>
                                    <th>العنوان:</th>
                                    <td>{{ $operator->address }}</td>
                                </tr>
                            @endif
                            <tr>
                                <th>صاحب المشغل:</th>
                                <td>
                                    @if($operator->owner)
                                        <span class="badge bg-primary">{{ $operator->owner->name }}</span>
                                        <small class="text-muted ms-2">({{ $operator->owner->username }})</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>عدد المولدات:</th>
                                <td>
                                    <span class="badge bg-info">{{ $operator->generators->count() }} مولد</span>
                                </td>
                            </tr>
                            <tr>
                                <th>عدد الموظفين:</th>
                                <td>
                                    <span class="badge bg-success">{{ $operator->users->count() }} موظف</span>
                                </td>
                            </tr>
                            <tr>
                                <th>تاريخ الإنشاء:</th>
                                <td>{{ $operator->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                            <tr>
                                <th>آخر تحديث:</th>
                                <td>{{ $operator->updated_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

