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
            <span class="badge bg-{{ $record->maintenanceTypeDetail?->getBadgeColor() ?? 'secondary' }}">{{ $record->maintenanceTypeDetail?->label ?? '-' }}</span>
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



