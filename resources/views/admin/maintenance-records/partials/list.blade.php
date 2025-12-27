@if($maintenanceRecords->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>رقم المولد</th>
                    <th>نوع الصيانة</th>
                    <th>تاريخ الصيانة</th>
                    <th>اسم الفني</th>
                    <th>زمن التوقف</th>
                    <th>تكلفة الصيانة</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($maintenanceRecords as $record)
                    <tr>
                        <td>{{ $record->id }}</td>
                        <td>
                            @if($record->generator)
                                <span class="badge bg-secondary">{{ $record->generator->generator_number }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $maintenanceTypes = [
                                    'periodic' => 'دورية',
                                    'preventive' => 'وقائية',
                                    'emergency' => 'طارئة',
                                    'major' => 'كبرى',
                                    'regular' => 'عادية',
                                    'صيانة دورية' => 'دورية',
                                    'صيانة وقائية' => 'وقائية',
                                    'صيانة طارئة' => 'طارئة',
                                    'صيانة كبرى' => 'كبرى',
                                    'صيانة عادية' => 'عادية',
                                ];
                                $maintenanceTypeAr = $maintenanceTypes[$record->maintenance_type] ?? $record->maintenance_type;
                                $badgeColor = 'info';
                                if (in_array($record->maintenance_type, ['emergency', 'طارئة', 'صيانة طارئة'])) {
                                    $badgeColor = 'danger';
                                } elseif (in_array($record->maintenance_type, ['periodic', 'دورية', 'صيانة دورية'])) {
                                    $badgeColor = 'info';
                                } else {
                                    $badgeColor = 'warning';
                                }
                            @endphp
                            <span class="badge bg-{{ $badgeColor }}">{{ $maintenanceTypeAr }}</span>
                        </td>
                        <td>{{ $record->maintenance_date->format('Y-m-d') }}</td>
                        <td>{{ $record->technician_name ?? '-' }}</td>
                        <td>
                            @if($record->downtime_hours)
                                {{ number_format($record->downtime_hours, 2) }} ساعة
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($record->maintenance_cost)
                                {{ number_format($record->maintenance_cost, 2) }} ₪
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <div class="log-row-actions">
                                @can('view', $record)
                                    <a href="{{ route('admin.maintenance-records.show', $record) }}" class="btn btn-xs btn-outline-info" title="عرض">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                @endcan
                                @can('update', $record)
                                    <a href="{{ route('admin.maintenance-records.edit', $record) }}" class="btn btn-xs btn-outline-primary" title="تعديل">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @endcan
                                @can('delete', $record)
                                    <button type="button" class="btn btn-xs btn-outline-danger maintenance-record-delete-btn" 
                                            data-maintenance-record-id="{{ $record->id }}"
                                            data-maintenance-record-name="سجل #{{ $record->id }}"
                                            title="حذف">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($maintenanceRecords->hasPages())
        <div class="log-pagination mt-4">
            {{ $maintenanceRecords->links() }}
        </div>
    @endif
@else
    <div class="log-empty-state text-center py-5">
        <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
        <h5 class="text-muted">لا توجد سجلات صيانة</h5>
        <p class="text-muted">لم يتم العثور على سجلات صيانة تطابق البحث</p>
    </div>
@endif
