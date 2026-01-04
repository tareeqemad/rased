@extends('layouts.admin')

@section('title', 'سجل تغييرات الصلاحيات')

@php
    $breadcrumbTitle = 'سجل تغييرات الصلاحيات';
    $isSuperAdmin = auth()->user()->isSuperAdmin();
    $isCompanyOwner = auth()->user()->isCompanyOwner();
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/data-table-loading.css') }}">
    <style>
        .perm-audit-page {
            --r-border: #e5e7eb;
            --r-border2: #eef2f7;
            --r-surface: #ffffff;
            --r-subtle: #f8fafc;
            --r-text: #0f172a;
            --r-muted: #64748b;
            margin-bottom: 2rem;
        }

        .perm-audit-page .perm-audit-card {
            position: relative;
            border: 1px solid var(--r-border);
            border-radius: 14px;
            background: var(--r-surface);
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(15, 23, 42, .04);
        }

        .perm-audit-page .perm-audit-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: rgba(var(--primary-rgb), .85);
        }

        .perm-audit-page .perm-audit-card-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--r-border2);
            background: var(--r-surface);
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
        }

        .perm-audit-page .perm-audit-title {
            font-weight: 900;
            font-size: 1.05rem;
            margin: 0;
            color: var(--r-text);
        }

        .perm-audit-page .perm-audit-subtitle {
            margin-top: .25rem;
            color: var(--r-muted);
            font-size: .85rem;
        }

        .perm-audit-page .perm-audit-table {
            margin-bottom: 0;
        }

        .perm-audit-page .perm-audit-table thead th {
            background: var(--r-subtle);
            border-bottom: 2px solid var(--r-border);
            font-weight: 700;
            font-size: .85rem;
            color: var(--r-text);
            padding: .75rem 1rem;
        }

        .perm-audit-page .perm-audit-table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid var(--r-border2);
        }

        .perm-audit-page .perm-audit-table tbody tr:hover {
            background: var(--r-subtle);
        }

        .perm-audit-badge {
            font-weight: 600;
            font-size: .75rem;
            padding: .35rem .65rem;
            border-radius: 8px;
        }

        .perm-audit-badge.granted {
            background: #d1fae5;
            color: #065f46;
        }

        .perm-audit-badge.revoked {
            background: #fee2e2;
            color: #991b1b;
        }

        .perm-audit-user {
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .perm-audit-user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(var(--primary-rgb), .1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: rgba(var(--primary-rgb), 1);
            font-size: .85rem;
        }

        .perm-audit-empty {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--r-muted);
        }

        .perm-audit-empty i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: .5;
        }
    </style>
@endpush

@section('content')
<div class="perm-audit-page">
    <div class="row g-3">
        <div class="col-12">
            <div class="perm-audit-card">
                <div class="perm-audit-card-header">
                    <div>
                        <h5 class="perm-audit-title">
                            <i class="bi bi-shield-check me-2"></i>
                            سجل تغييرات الصلاحيات
                        </h5>
                        <div class="perm-audit-subtitle">
                            {{ $isSuperAdmin ? 'متابعة جميع التغييرات على الصلاحيات في النظام' : 'متابعة تغييرات الصلاحيات للموظفين والفنيين التابعين لمشغلك' }}
                        </div>
                    </div>
                </div>

                <div class="card-body pb-4">
                    <div class="table-responsive data-table-container" id="auditLogsTableContainer">
                        @if($auditLogs->count() > 0)
                            <table class="table table-hover align-middle mb-0 perm-audit-table">
                                <thead>
                                    <tr>
                                        <th style="min-width: 200px;">المستخدم</th>
                                        <th style="min-width: 200px;">الصلاحية</th>
                                        <th style="min-width: 120px;">الإجراء</th>
                                        <th style="min-width: 180px;">نفذ بواسطة</th>
                                        <th style="min-width: 150px;">التاريخ والوقت</th>
                                        <th style="min-width: 100px;">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($auditLogs as $log)
                                        <tr>
                                            <td>
                                                <div class="perm-audit-user">
                                                    <div class="perm-audit-user-avatar">
                                                        {{ mb_substr($log->user->name ?? '?', 0, 1) }}
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold">{{ $log->user->name ?? '—' }}</div>
                                                        <div class="small text-muted">{{ $log->user->username ?? '—' }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <div class="fw-semibold">{{ $log->permission->label ?? '—' }}</div>
                                                    <code class="small text-muted">{{ $log->permission->name ?? '—' }}</code>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="perm-audit-badge {{ $log->action === 'granted' ? 'granted' : 'revoked' }}">
                                                    @if($log->action === 'granted')
                                                        <i class="bi bi-check-circle me-1"></i>
                                                        منح
                                                    @else
                                                        <i class="bi bi-x-circle me-1"></i>
                                                        إلغاء/منع
                                                    @endif
                                                </span>
                                            </td>
                                            <td>
                                                <div>
                                                    <div class="fw-semibold">{{ $log->performedBy->name ?? '—' }}</div>
                                                    <div class="small text-muted">{{ $log->performedBy->username ?? '—' }}</div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <div class="fw-semibold">{{ $log->created_at->format('Y-m-d') }}</div>
                                                    <div class="small text-muted">{{ $log->created_at->format('H:i:s') }}</div>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.permission-audit-logs.show', $log) }}" 
                                                   class="btn btn-sm btn-outline-primary" 
                                                   title="عرض التفاصيل">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                <div class="fw-bold">لا توجد سجلات</div>
                                <div>لم يتم تسجيل أي تغييرات على الصلاحيات بعد</div>
                            </div>
                        @endif
                    </div>

                    @if($auditLogs->hasPages())
                        <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 gap-2">
                            <div class="small text-muted">
                                عرض {{ $auditLogs->firstItem() ?? 0 }} - {{ $auditLogs->lastItem() ?? 0 }} من {{ $auditLogs->total() }}
                            </div>
                            <nav>
                                {{ $auditLogs->links() }}
                            </nav>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
