@extends('layouts.admin')

@section('title', 'شجرة الصلاحيات')

@php
    $breadcrumbTitle = 'شجرة الصلاحيات';
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/admin/libs/select2/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/admin/css/tree.css') }}">
<style>
    .user-select-section {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1.5rem;
        border: 1px solid #e5e7eb;
    }
    
    .select2-container {
        width: 100% !important;
    }
    
    .select2-container--default .select2-selection--single {
        height: 48px !important;
        border: 2px solid #dee2e6 !important;
        border-radius: 8px !important;
        padding: 0 !important;
        background: #ffffff !important;
        transition: all 0.3s ease !important;
        cursor: pointer !important;
        position: relative !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
    }
    
    .select2-container--default .select2-selection--single:hover {
        border-color: #3b82f6 !important;
        background-color: #f8f9fa !important;
        box-shadow: 0 2px 4px rgba(59, 130, 246, 0.1) !important;
    }
    
    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15) !important;
        background-color: white !important;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 44px !important;
        padding-right: 45px !important;
        padding-left: 12px !important;
        font-size: 15px !important;
        color: #495057 !important;
        font-weight: 500 !important;
    }
    
    /* جعل السهم واضحاً وكبيراً */
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 46px !important;
        right: 8px !important;
        left: auto !important;
        width: 35px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        background: #f8f9fa !important;
        border-left: 1px solid #e9ecef !important;
        border-radius: 0 6px 6px 0 !important;
    }
    
    .select2-container--default .select2-selection--single:hover .select2-selection__arrow {
        background: #e9ecef !important;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__arrow b {
        border-color: #495057 transparent transparent transparent !important;
        border-width: 10px 8px 0 8px !important;
        margin-top: 0 !important;
        margin-left: -8px !important;
        border-style: solid !important;
        position: relative !important;
    }
    
    .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow {
        background: #e9ecef !important;
    }
    
    .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
        border-color: transparent transparent #495057 transparent !important;
        border-width: 0 8px 10px 8px !important;
        margin-top: -2px !important;
    }
    
    /* إضافة placeholder style */
    .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #6c757d !important;
        font-weight: 400 !important;
    }
    
    .select2-dropdown {
        border: 2px solid #e0e0e0 !important;
        border-radius: 8px !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
        margin-top: 4px !important;
    }
    
    .select2-results__option {
        padding: 12px 16px !important;
        font-size: 14px !important;
    }
    
    .select2-results__option--highlighted {
        background-color: #3b82f6 !important;
        color: white !important;
    }
    
    .select2-results__group {
        padding: 10px 16px !important;
        font-weight: 600 !important;
        font-size: 13px !important;
        color: #6b7280 !important;
        background-color: #f9fafb !important;
    }
    
    .permissions-count-badge {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 14px;
    }
    
    .loading-spinner {
        display: none;
    }
    
    .loading-spinner.active {
        display: inline-block;
    }
</style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="card border-0 shadow-lg">
            <!-- Card Header -->
            <div class="card-header bg-white border-bottom" style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); padding: 1rem 1.5rem;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="mb-0 fw-bold text-white">
                            <i class="bi bi-diagram-3 me-2"></i>
                            شجرة الصلاحيات
                        </h5>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-light" id="expandAllBtn">
                            <i class="bi bi-arrows-angle-expand me-1"></i>
                            توسيع الكل
                        </button>
                        <button type="button" class="btn btn-sm btn-light d-none" id="collapseAllBtn">
                            <i class="bi bi-arrows-angle-contract me-1"></i>
                            طي الكل
                        </button>
                    </div>
                </div>
            </div>

            <!-- Card Body -->
            <div class="card-body p-4">
                <!-- User Selection Section -->
                @if((auth()->user()->isSuperAdmin() || auth()->user()->isCompanyOwner()) && $users->count() > 0)
                    <div class="user-select-section mb-4">
                        <form action="{{ route('admin.permissions.assign') }}" method="POST" id="assignPermissionsForm">
                            @csrf
                            <div class="row g-3 align-items-end">
                                <div class="col-md-5">
                                    <label class="form-label fw-bold mb-2 d-block">
                                        <i class="bi bi-person-circle me-2 text-primary"></i>
                                        اختر المستخدم
                                    </label>
                                    <select name="user_id" id="selectedUser" class="form-select" required style="height: 48px; font-size: 15px;">
                                        <option value="">-- اختر المستخدم --</option>
                                        
                                        @if($groupedUsers['company_owners']->count() > 0)
                                            <optgroup label="صاحبو المشغلين">
                                                @foreach($groupedUsers['company_owners'] as $userOption)
                                                    <option value="{{ $userOption->id }}" 
                                                            data-permissions="{{ $userOption->permissions->pluck('id')->toJson() }}">
                                                        {{ $userOption->name }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endif

                                        @if($groupedUsers['employees']->count() > 0)
                                            <optgroup label="الموظفون">
                                                @foreach($groupedUsers['employees'] as $userOption)
                                                    <option value="{{ $userOption->id }}" 
                                                            data-permissions="{{ $userOption->permissions->pluck('id')->toJson() }}">
                                                        {{ $userOption->name }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endif

                                        @if($groupedUsers['technicians']->count() > 0)
                                            <optgroup label="الفنيون">
                                                @foreach($groupedUsers['technicians'] as $userOption)
                                                    <option value="{{ $userOption->id }}" 
                                                            data-permissions="{{ $userOption->permissions->pluck('id')->toJson() }}">
                                                        {{ $userOption->name }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <div id="selectedPermissionsCount" class="d-flex align-items-center h-100">
                                        <div class="text-muted">
                                            <i class="bi bi-info-circle me-2"></i>
                                            <span>اختر مستخدم أولاً</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 text-end">
                                    <button type="submit" class="btn btn-primary w-100" id="submitBtn" disabled style="height: 56px; font-size: 16px; font-weight: 600;">
                                        <i class="bi bi-save me-2 fs-5"></i>
                                        حفظ الصلاحيات
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                @endif

                <!-- Search Section -->
                <div class="mb-4">
                    <div class="d-flex gap-2">
                        <div class="flex-grow-1">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input type="text" 
                                       id="searchInput" 
                                       class="form-control border-start-0" 
                                       placeholder="ابحث عن صلاحية..." 
                                       value="{{ $search }}"
                                       style="height: 42px;">
                            </div>
                        </div>
                        <button type="button" id="searchBtn" class="btn btn-primary" style="height: 42px;">
                            <span class="loading-spinner spinner-border spinner-border-sm me-2"></span>
                            <i class="bi bi-search me-1"></i>
                            بحث
                        </button>
                        <button type="button" id="clearSearchBtn" class="btn btn-outline-secondary {{ $search ? '' : 'd-none' }}" style="height: 42px;">
                            <i class="bi bi-x me-1"></i>
                            إلغاء
                        </button>
                    </div>
                </div>

                <!-- Permissions Tree -->
                <div class="permissions-tree" id="permissionsTree" style="max-height: 500px; overflow-y: auto;">
                    @include('admin.permissions.partials.permissions-tree', ['permissions' => $permissions, 'search' => $search])
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<!-- jQuery (مطلوب لـ Select2) -->
<script src="{{ asset('assets/admin/libs/jquery/jquery.min.js') }}"></script>
<!-- Select2 -->
<script src="{{ asset('assets/admin/libs/select2/select2.min.js') }}"></script>
@if(file_exists(public_path('assets/admin/libs/select2/i18n/ar.js')))
<script src="{{ asset('assets/admin/libs/select2/i18n/ar.js') }}"></script>
@endif
<script>
    $(document).ready(function() {
        const $expandAllBtn = $('#expandAllBtn');
        const $collapseAllBtn = $('#collapseAllBtn');
        const $selectedUser = $('#selectedUser');
        const $submitBtn = $('#submitBtn');
        const $selectedPermissionsCount = $('#selectedPermissionsCount');
        const $searchInput = $('#searchInput');
        const $searchBtn = $('#searchBtn');
        const $clearSearchBtn = $('#clearSearchBtn');
        const $permissionsTree = $('#permissionsTree');
        const $loadingSpinner = $('.loading-spinner');
        let searchTimeout;

        // تهيئة Select2
        if ($selectedUser.length) {
            $selectedUser.select2({
                theme: 'bootstrap-5',
                dir: 'rtl',
                placeholder: '-- اختر المستخدم --',
                allowClear: true,
                width: '100%',
                language: 'ar',
                dropdownParent: $selectedUser.closest('.user-select-section')
            });

            // عند تغيير الاختيار
            $selectedUser.on('select2:select select2:clear', function() {
                const userId = $(this).val();
                const $selectedOption = $(this).find('option:selected');
                
                if (userId) {
                    const currentPermissions = JSON.parse($selectedOption.data('permissions') || '[]');
                    
                    // تحديث حالة checkboxes
                    $('.permission-checkbox').each(function() {
                        $(this).prop('checked', currentPermissions.includes(parseInt($(this).val())));
                    });

                    // تحديث عداد الصلاحيات
                    const count = currentPermissions.length;
                    $selectedPermissionsCount.html(`
                        <div class="permissions-count-badge">
                            <i class="bi bi-check-circle me-2"></i>
                            <strong>${count}</strong> صلاحية محددة
                        </div>
                    `);

                    $submitBtn.prop('disabled', false);
                } else {
                    $('.permission-checkbox').prop('checked', false);
                    $selectedPermissionsCount.html(`
                        <div class="text-muted">
                            <i class="bi bi-info-circle me-2"></i>
                            <span>اختر مستخدم أولاً</span>
                        </div>
                    `);
                    $submitBtn.prop('disabled', true);
                }
            });
        }

        // البحث AJAX
        function performSearch() {
            const searchTerm = $searchInput.val().trim();
            
            $loadingSpinner.addClass('active');
            $searchBtn.prop('disabled', true);
            
            $.ajax({
                url: '{{ route("admin.permissions.search") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    search: searchTerm
                },
                success: function(response) {
                    if (response.success) {
                        $permissionsTree.html(response.html);
                        
                        // إعادة تهيئة collapse
                        $('.tree-group-header').off('click').on('click', function() {
                            const target = $(this).data('bs-target');
                            $(target).collapse('toggle');
                        });
                        
                        // إعادة ربط checkboxes
                        bindCheckboxes();
                        
                        // إظهار/إخفاء زر الإلغاء
                        if (searchTerm) {
                            $clearSearchBtn.removeClass('d-none');
                        } else {
                            $clearSearchBtn.addClass('d-none');
                        }
                    }
                },
                error: function() {
                    alert('حدث خطأ أثناء البحث. يرجى المحاولة مرة أخرى.');
                },
                complete: function() {
                    $loadingSpinner.removeClass('active');
                    $searchBtn.prop('disabled', false);
                }
            });
        }

        // البحث عند الضغط على Enter
        $searchInput.on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                performSearch();
            }
        });

        // البحث عند الضغط على الزر
        $searchBtn.on('click', function() {
            performSearch();
        });

        // إلغاء البحث
        $clearSearchBtn.on('click', function() {
            $searchInput.val('');
            performSearch();
        });

        // ربط checkboxes
        function bindCheckboxes() {
            $('.permission-checkbox').off('change').on('change', function() {
                if ($selectedUser.val()) {
                    const checkedCount = $('.permission-checkbox:checked').length;
                    $selectedPermissionsCount.html(`
                        <div class="permissions-count-badge">
                            <i class="bi bi-check-circle me-2"></i>
                            <strong>${checkedCount}</strong> صلاحية محددة
                        </div>
                    `);
                }
            });
        }

        // ربط checkboxes الأولي
        bindCheckboxes();

        // توسيع/طي الكل
        $expandAllBtn.on('click', function() {
            $('.tree-node .collapse').collapse('show');
            $expandAllBtn.addClass('d-none');
            $collapseAllBtn.removeClass('d-none');
        });

        $collapseAllBtn.on('click', function() {
            $('.tree-node .collapse').collapse('hide');
            $expandAllBtn.removeClass('d-none');
            $collapseAllBtn.addClass('d-none');
        });

        // تحديث حالة الأزرار عند فتح/إغلاق مجموعة
        $(document).on('show.bs.collapse hide.bs.collapse', '.tree-node .collapse', function() {
            const $groups = $('.tree-node');
            const allExpanded = $groups.find('.collapse.show').length === $groups.length;
            
            if (allExpanded) {
                $expandAllBtn.addClass('d-none');
                $collapseAllBtn.removeClass('d-none');
            } else {
                $expandAllBtn.removeClass('d-none');
                $collapseAllBtn.addClass('d-none');
            }
        });
    });
</script>
@endpush
