@extends('layouts.admin')

@section('title', 'إدارة الثوابت')

@php
    $breadcrumbTitle = 'إدارة الثوابت';
@endphp

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-database me-2"></i>
                        إدارة الثوابت
                    </h5>
                    @can('create', App\Models\ConstantMaster::class)
                        <a href="{{ route('admin.constants.create') }}" class="btn btn-sm">
                            <i class="bi bi-plus-circle me-1"></i>
                            إضافة ثابت جديد
                        </a>
                    @endcan
                </div>
                <div class="card-body position-relative">
                    <!-- Search Form -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <form id="searchForm" class="d-flex gap-2">
                                <input type="text" name="search" id="searchInput" class="form-control" placeholder="بحث في الثوابت..." value="{{ request('search') }}">
                        <button type="submit" class="btn btn-primary" id="searchBtn">
                            <i class="bi bi-search me-1"></i>
                            بحث
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="clearSearchBtn" style="{{ request('search') ? '' : 'display: none;' }}">
                            <i class="bi bi-x"></i>
                        </button>
                            </form>
                        </div>
                    </div>

                    <!-- Loading Overlay -->
                    <div id="loadingOverlay" class="loading-overlay-constants" style="display: none;">
                        <div class="loading-content">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">جاري التحميل...</span>
                            </div>
                            <p class="mt-3 mb-0 text-muted fw-semibold">جاري البحث...</p>
                        </div>
                    </div>

                    <!-- Table Container -->
                    <div id="tableContainer">
                        @include('admin.constants.partials.table', ['constants' => $constants])
                    </div>
                </div>
                <!-- Pagination Container -->
                <div id="paginationContainer" class="card-footer">
                    @include('admin.constants.partials.pagination', ['constants' => $constants])
                </div>
            </div>
        </div>
    </div>

    <!-- Modals Container -->
    <div id="modalsContainer">
        @include('admin.constants.partials.modals', ['constants' => $constants])
    </div>
@endsection

@push('scripts')
<script src="{{ asset('assets/admin/libs/jquery/jquery.min.js') }}"></script>
<script>
    $(document).ready(function() {
        const $searchForm = $('#searchForm');
        const $searchInput = $('#searchInput');
        const $searchBtn = $('#searchBtn');
        const $clearSearchBtn = $('#clearSearchBtn');
        const $tableContainer = $('#tableContainer');
        const $paginationContainer = $('#paginationContainer');
        const $loadingOverlay = $('#loadingOverlay');
        const $cardBody = $('.card-body');

        // دالة تحميل البيانات
        function loadData(url) {
            $loadingOverlay.fadeIn(200);
            $cardBody.css('position', 'relative');

            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success) {
                        $tableContainer.html(response.html);
                        $paginationContainer.html(response.pagination);
                        
                        // تحديث Modals
                        if (response.modals) {
                            $('#modalsContainer').html(response.modals);
                        }
                        
                        // تحديث URL بدون reload
                        if (window.history && window.history.pushState) {
                            window.history.pushState({path: url}, '', url);
                        }

                        // إعادة تهيئة Modals بعد تحديث المحتوى
                        $('[data-bs-toggle="modal"]').off('click').on('click', function() {
                            const target = $(this).data('bs-target');
                            $(target).modal('show');
                        });

                        // إظهار/إخفاء زر مسح البحث
                        if ($searchInput.val().trim()) {
                            $('#clearSearchBtn').show();
                        } else {
                            $('#clearSearchBtn').hide();
                        }
                    } else {
                        window.adminNotifications.error('حدث خطأ أثناء تحميل البيانات', 'خطأ');
                    }
                },
                error: function(xhr) {
                    window.adminNotifications.error('حدث خطأ أثناء تحميل البيانات', 'خطأ');
                    console.error('Error:', xhr);
                },
                complete: function() {
                    $loadingOverlay.fadeOut(200);
                }
            });
        }

        // البحث
        $searchForm.on('submit', function(e) {
            e.preventDefault();
            const searchValue = $searchInput.val().trim();
            const url = new URL('{{ route("admin.constants.index") }}', window.location.origin);
            if (searchValue) {
                url.searchParams.set('search', searchValue);
            }
            loadData(url.toString());
        });

        // مسح البحث
        $(document).on('click', '#clearSearchBtn', function() {
            $searchInput.val('');
            const url = '{{ route("admin.constants.index") }}';
            loadData(url);
        });

        // Pagination - النقر على روابط pagination
        $(document).on('click', '.pagination a', function(e) {
            e.preventDefault();
            const url = $(this).attr('href');
            if (url) {
                loadData(url);
                // Scroll to top
                $('html, body').animate({
                    scrollTop: $('.card').offset().top - 100
                }, 300);
            }
        });

        // دعم زر Back/Forward في المتصفح
        window.addEventListener('popstate', function(event) {
            if (event.state && event.state.path) {
                loadData(event.state.path);
            } else {
                location.reload();
            }
        });
    });
</script>
@endpush

