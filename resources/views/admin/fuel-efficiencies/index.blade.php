@extends('layouts.admin')

@section('title', 'كفاءة الوقود')

@php
    $breadcrumbTitle = 'كفاءة الوقود';
@endphp

@section('content')
    <div class="container-fluid">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">
                    <i class="bi bi-speedometer2 me-2"></i>
                    كفاءة الوقود
                </h5>
                @can('create', App\Models\FuelEfficiency::class)
                    <a href="{{ route('admin.fuel-efficiencies.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>
                        إضافة سجل جديد
                    </a>
                @endcan
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>رقم المولد</th>
                                <th>تاريخ الاستهلاك</th>
                                <th>ساعات التشغيل</th>
                                <th>كفاءة الوقود</th>
                                <th>كفاءة الطاقة</th>
                                <th>التكلفة الإجمالية</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($fuelEfficiencies as $efficiency)
                                <tr>
                                    <td>{{ $efficiency->id }}</td>
                                    <td>
                                        @if($efficiency->generator)
                                            <span class="badge bg-secondary">{{ $efficiency->generator->generator_number }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $efficiency->consumption_date->format('Y-m-d') }}</td>
                                    <td>
                                        @if($efficiency->operating_hours)
                                            {{ number_format($efficiency->operating_hours, 2) }} ساعة
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($efficiency->fuel_efficiency_percentage)
                                            <span class="badge bg-{{ $efficiency->fuel_efficiency_comparison === 'within_standard' ? 'success' : ($efficiency->fuel_efficiency_comparison === 'above' ? 'warning' : 'danger') }}">
                                                {{ $efficiency->fuel_efficiency_percentage }}%
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($efficiency->energy_distribution_efficiency)
                                            <span class="badge bg-{{ $efficiency->energy_efficiency_comparison === 'within_standard' ? 'success' : ($efficiency->energy_efficiency_comparison === 'above' ? 'warning' : 'danger') }}">
                                                {{ $efficiency->energy_distribution_efficiency }}%
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($efficiency->total_operating_cost)
                                            {{ number_format($efficiency->total_operating_cost, 2) }} ₪
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            @can('view', $efficiency)
                                                <a href="{{ route('admin.fuel-efficiencies.show', $efficiency) }}" class="btn btn-sm btn-outline-info">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            @endcan
                                            @can('update', $efficiency)
                                                <a href="{{ route('admin.fuel-efficiencies.edit', $efficiency) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            @endcan
                                            @can('delete', $efficiency)
                                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $efficiency->id }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>

                                @can('delete', $efficiency)
                                    <div class="modal fade" id="deleteModal{{ $efficiency->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">تأكيد الحذف</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>هل أنت متأكد من حذف سجل كفاءة الوقود #{{ $efficiency->id }}؟</p>
                                                    <p class="text-danger"><small>هذا الإجراء لا يمكن التراجع عنه</small></p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                    <form action="{{ route('admin.fuel-efficiencies.destroy', $efficiency) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">حذف</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endcan
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        لا توجد سجلات كفاءة وقود
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($fuelEfficiencies->hasPages())
                <div class="card-footer bg-white border-top">
                    {{ $fuelEfficiencies->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

