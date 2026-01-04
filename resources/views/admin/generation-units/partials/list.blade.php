@if($generationUnits->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>كود الوحدة</th>
                    <th>اسم الوحدة</th>
                    <th>المشغل</th>
                    <th class="text-center">عدد المولدات</th>
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
                        <td>{{ $unit->operator->name }}</td>
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
                            @if($unit->status === 'active')
                                <span class="badge bg-success">نشط</span>
                            @else
                                <span class="badge bg-secondary">غير نشط</span>
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

    @if($generationUnits->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $generationUnits->links() }}
        </div>
    @endif
@else
    <div class="text-center py-5">
        <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
        <p class="text-muted mt-3">لا توجد وحدات توليد.</p>
    </div>
@endif

