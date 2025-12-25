<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 op-table">
            <thead class="table-light">
            <tr>
                <th>المشغل</th>
                <th class="d-none d-lg-table-cell">المالك</th>
                <th class="text-center">الموظفون</th>
                <th class="text-center">المولدات</th>
                <th class="text-center d-none d-md-table-cell">الحالة</th>
                <th class="d-none d-xl-table-cell">تاريخ الإنشاء</th>
                <th class="text-nowrap">إجراءات</th>
            </tr>
            </thead>
            <tbody>
            @forelse($operators as $operator)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar-circle">{{ mb_substr($operator->name, 0, 1) }}</div>
                            <div>
                                <div class="fw-bold">{{ $operator->unit_name ?? $operator->name }}</div>
                                <div class="text-muted small">
                                    {{ $operator->unit_number ?? '—' }}
                                    @if($operator->getGovernorateLabel())
                                        • {{ $operator->getGovernorateLabel() }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>

                    <td class="d-none d-lg-table-cell">
                        @if($operator->owner)
                            <span class="badge bg-primary">{{ $operator->owner->name }}</span>
                            <span class="text-muted small ms-1">({{ $operator->owner->username }})</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>

                    <td class="text-center">
                        <span class="badge bg-success">
                            {{ $operator->employees_count ?? 0 }}
                        </span>
                    </td>

                    <td class="text-center">
                        <span class="badge bg-info">
                            {{ $operator->generators_count ?? 0 }}
                        </span>
                    </td>

                    <td class="text-center d-none d-md-table-cell">
                        @if($operator->status === 'active')
                            <span class="badge bg-success">فعّال</span>
                        @elseif($operator->status === 'inactive')
                            <span class="badge bg-secondary">غير فعّال</span>
                        @else
                            <span class="badge bg-light text-dark">غير محدد</span>
                        @endif
                    </td>

                    <td class="d-none d-xl-table-cell">
                        <span class="text-muted small">{{ optional($operator->created_at)->format('Y-m-d') }}</span>
                    </td>

                    <td class="text-nowrap">
                        <div class="d-flex gap-2">
                            @can('view', $operator)
                                <a href="{{ route('admin.operators.show', $operator) }}" class="btn btn-sm btn-outline-info" title="عرض">
                                    <i class="bi bi-eye"></i>
                                </a>
                            @endcan

                            @can('update', $operator)
                                <a href="{{ route('admin.operators.edit', $operator) }}"
                                   class="btn btn-sm btn-outline-primary"
                                   title="تعديل"
                                   data-action="edit-operator"
                                   data-url="{{ route('admin.operators.edit', $operator) }}">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            @endcan

                            @can('delete', $operator)
                                <button type="button"
                                        class="btn btn-sm btn-outline-danger"
                                        title="حذف"
                                        data-action="delete-operator"
                                        data-name="{{ $operator->name }}"
                                        data-url="{{ route('admin.operators.destroy', $operator) }}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        لا توجد نتائج
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($operators->hasPages())
    <div class="card-footer py-3">
        {{ $operators->links() }}
    </div>
@endif
