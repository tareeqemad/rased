@foreach($complianceSafeties as $compliance)
    <tr>
        <td>{{ $compliance->id }}</td>
        <td>
            @if($compliance->operator)
                <span class="badge bg-info">{{ $compliance->operator->name }}</span>
            @else
                <span class="text-muted">-</span>
            @endif
        </td>
        <td>
            @php
                $statusColors = [
                    'compliant' => 'success',
                    'non_compliant' => 'danger',
                    'pending' => 'warning',
                    'available' => 'success',
                    'expired' => 'warning',
                    'not_available' => 'danger'
                ];
                $statusLabels = [
                    'compliant' => 'متوافق',
                    'non_compliant' => 'غير متوافق',
                    'pending' => 'قيد الانتظار',
                    'available' => 'متوفرة',
                    'expired' => 'منتهية',
                    'not_available' => 'غير متوفرة'
                ];
            @endphp
            <span class="badge bg-{{ $statusColors[$compliance->safety_certificate_status] ?? 'secondary' }}">
                {{ $statusLabels[$compliance->safety_certificate_status] ?? $compliance->safety_certificate_status }}
            </span>
        </td>
        <td>
            @if($compliance->last_inspection_date)
                {{ $compliance->last_inspection_date->format('Y-m-d') }}
            @else
                <span class="text-muted">-</span>
            @endif
        </td>
        <td>{{ $compliance->inspection_authority ?? '-' }}</td>
        <td>
            <div class="log-row-actions">
                @can('view', $compliance)
                    <a href="{{ route('admin.compliance-safeties.show', $compliance) }}" class="btn btn-xs btn-outline-info" title="عرض">
                        <i class="bi bi-eye"></i>
                    </a>
                @endcan
                @can('update', $compliance)
                    <a href="{{ route('admin.compliance-safeties.edit', $compliance) }}" class="btn btn-xs btn-outline-primary" title="تعديل">
                        <i class="bi bi-pencil"></i>
                    </a>
                @endcan
                @can('delete', $compliance)
                    <button type="button" class="btn btn-xs btn-outline-danger compliance-safety-delete-btn" 
                            data-compliance-safety-id="{{ $compliance->id }}"
                            data-compliance-safety-name="سجل #{{ $compliance->id }}"
                            title="حذف">
                        <i class="bi bi-trash"></i>
                    </button>
                @endcan
            </div>
        </td>
    </tr>
@endforeach



