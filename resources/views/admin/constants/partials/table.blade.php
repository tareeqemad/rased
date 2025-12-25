<div class="table-responsive">
    <table class="table table-hover mb-0">
        <thead class="table-light">
            <tr>
                <th class="text-nowrap">رقم الثابت</th>
                <th class="text-nowrap">اسم الثابت</th>
                <th class="text-nowrap d-none d-md-table-cell">الوصف</th>
                <th class="text-nowrap">عدد التفاصيل</th>
                <th class="text-nowrap">الحالة</th>
                <th class="text-nowrap d-none d-lg-table-cell">ترتيب العرض</th>
                <th class="text-nowrap">الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @forelse($constants as $constant)
                <tr>
                    <td class="text-nowrap">
                        <code class="text-primary">{{ $constant->constant_number }}</code>
                    </td>
                    <td class="text-nowrap">
                        <span class="fw-semibold">{{ $constant->constant_name }}</span>
                    </td>
                    <td class="d-none d-md-table-cell">
                        <small class="text-muted">{{ $constant->description ?? '-' }}</small>
                    </td>
                    <td class="text-nowrap">
                        <span class="badge bg-info">{{ $constant->all_details_count ?? 0 }}</span>
                    </td>
                    <td class="text-nowrap">
                        @if($constant->is_active)
                            <span class="badge bg-success">نشط</span>
                        @else
                            <span class="badge bg-danger">غير نشط</span>
                        @endif
                    </td>
                    <td class="d-none d-lg-table-cell">
                        <small class="text-muted">{{ $constant->order }}</small>
                    </td>
                    <td class="text-nowrap">
                        <div class="d-flex gap-2">
                            @can('view', $constant)
                                <a href="{{ route('admin.constants.show', $constant) }}" class="btn btn-sm btn-outline-info" title="عرض">
                                    <i class="bi bi-eye"></i>
                                </a>
                            @endcan
                            @can('update', $constant)
                                <a href="{{ route('admin.constants.edit', $constant) }}" class="btn btn-sm btn-outline-primary" title="تعديل">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            @endcan
                            @can('delete', $constant)
                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $constant->id }}" title="حذف">
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
                        <span class="empty-message">
                            @if(request('search'))
                                لا توجد نتائج للبحث "{{ request('search') }}"
                            @else
                                لا توجد ثوابت
                            @endif
                        </span>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

