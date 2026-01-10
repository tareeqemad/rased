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
                            @if($unit->qr_code_generated_at)
                                <span class="badge bg-success ms-2" title="تم توليد QR Code في {{ $unit->qr_code_generated_at->format('Y-m-d H:i') }}">
                                    <i class="bi bi-check-circle"></i> QR
                                </span>
                            @else
                                <span class="badge bg-secondary ms-2" title="لم يتم توليد QR Code بعد">
                                    <i class="bi bi-x-circle"></i> بدون QR
                                </span>
                            @endif
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
                                    <a href="{{ route('admin.generation-units.qr-code', $unit) }}" target="_blank" class="btn btn-sm btn-success" title="طباعة QR Code">
                                        <i class="bi bi-qr-code"></i>
                                    </a>
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

