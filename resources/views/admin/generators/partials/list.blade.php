@if($generators->count() > 0)
    <div class="gen-list">
        @foreach($generators as $generator)
            <div class="gen-row" data-generator-id="{{ $generator->id }}" data-status="{{ $generator->status_id }}">
                <div class="gen-row-main">
                    <div class="gen-row-content">
                        <div class="gen-row-header">
                            <div class="gen-row-title">
                                <i class="bi bi-lightning-charge me-2 text-warning"></i>
                                <span class="fw-bold">{{ $generator->name }}</span>
                                <span class="badge bg-secondary ms-2">{{ $generator->generator_number }}</span>
                            </div>
                            <div class="gen-row-meta">
                                @if($generator->statusDetail && $generator->statusDetail->code === 'ACTIVE')
                                    <span class="badge bg-success">فعال</span>
                                @else
                                    <span class="badge bg-danger">غير فعال</span>
                                @endif
                            </div>
                        </div>

                        <div class="gen-row-details">
                            <div class="row g-2">
                                @if($generator->operator)
                                    <div class="col-md-3 col-sm-6">
                                        <div class="gen-detail-item">
                                            <i class="bi bi-building me-2 text-muted"></i>
                                            <span class="text-muted">المشغل:</span>
                                            <strong>{{ $generator->operator->name }}</strong>
                                        </div>
                                    </div>
                                @endif
                                
                                @if($generator->capacity_kva)
                                    <div class="col-md-3 col-sm-6">
                                        <div class="gen-detail-item">
                                            <i class="bi bi-speedometer2 me-2 text-muted"></i>
                                            <span class="text-muted">القدرة:</span>
                                            <strong>{{ number_format($generator->capacity_kva, 2) }} KVA</strong>
                                        </div>
                                    </div>
                                @endif

                                @if($generator->voltage)
                                    <div class="col-md-3 col-sm-6">
                                        <div class="gen-detail-item">
                                            <i class="bi bi-lightning me-2 text-muted"></i>
                                            <span class="text-muted">الجهد:</span>
                                            <strong>{{ $generator->voltage }}V</strong>
                                        </div>
                                    </div>
                                @endif

                                <div class="col-md-3 col-sm-6">
                                    <div class="gen-detail-item">
                                        <i class="bi bi-calendar3 me-2 text-muted"></i>
                                        <span class="text-muted">تاريخ الإنشاء:</span>
                                        <strong>{{ $generator->created_at->format('Y-m-d') }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="gen-row-actions">
                        @can('view', $generator)
                            <a href="{{ route('admin.generators.show', $generator) }}" class="btn btn-sm btn-outline-info" title="عرض">
                                <i class="bi bi-eye"></i>
                            </a>
                        @endcan
                        @can('update', $generator)
                            <a href="{{ route('admin.generators.edit', $generator) }}" class="btn btn-sm btn-outline-primary" title="تعديل">
                                <i class="bi bi-pencil"></i>
                            </a>
                        @endcan
                        @can('delete', $generator)
                            <button type="button" class="btn btn-sm btn-outline-danger gen-delete-btn" 
                                    data-generator-id="{{ $generator->id }}"
                                    data-generator-name="{{ $generator->name }}"
                                    title="حذف">
                                <i class="bi bi-trash"></i>
                            </button>
                        @endcan
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if($generators->hasPages())
        <div class="gen-pagination mt-4">
            {{ $generators->links() }}
        </div>
    @endif
@else
    <div class="gen-empty-state text-center py-5">
        <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
        <h5 class="text-muted">لا توجد مولدات</h5>
        <p class="text-muted">لم يتم العثور على مولدات تطابق البحث</p>
        @can('create', App\Models\Generator::class)
            <a href="{{ route('admin.generators.create') }}" class="btn btn-primary mt-3">
                <i class="bi bi-plus-circle me-2"></i>
                إضافة مولد جديد
            </a>
        @endcan
    </div>
@endif






