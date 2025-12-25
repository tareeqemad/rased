(function ($) {
    const GEN = window.GEN || {};
    const routes = GEN.routes || {};

    const $loadingOverlay = $('#generatorsLoadingOverlay');
    const $listContainer = $('#generatorsListContainer');
    const $searchInput = $('#searchInput');
    const $searchBtn = $('#searchBtn');
    const $clearSearchBtn = $('#clearSearchBtn');
    const $countSpan = $('#generatorsCount');
    const $operatorFilter = $('#operatorFilter');

    function csrfToken() {
        return $('meta[name="csrf-token"]').attr('content') || $('#csrfToken').val();
    }

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': csrfToken() }
    });

    function setLoading(on) {
        $loadingOverlay.toggle(!!on);
    }

    function flash(type, msg) {
        if (typeof window.showToast === 'function') {
            const toastType = type === 'danger' ? 'danger' : type === 'warning' ? 'warning' : type === 'info' ? 'info' : 'success';
            window.showToast(msg, toastType);
        }
    }

    function debounce(fn, delay) {
        let t = null;
        return function () {
            const args = arguments;
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, args), delay);
        };
    }

    function deleteGeneratorUrl(generatorId) {
        return routes.delete.replace('__ID__', generatorId);
    }

    function loadGenerators(params = {}) {
        setLoading(true);

        // Add AJAX header to request
        $.ajax({
            url: routes.search,
            method: 'GET',
            data: Object.assign({}, params, { ajax: 1 }),
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .done(res => {
                if (res.success && res.html) {
                    $listContainer.html(res.html);
                    if (res.count !== undefined) {
                        $countSpan.text(res.count);
                    }
                } else {
                    flash('warning', 'لم يتم العثور على نتائج');
                }
            })
            .fail(xhr => {
                let errorMsg = 'حدث خطأ أثناء تحميل المولدات';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        errorMsg = errors.join(' | ');
                    }
                } else if (xhr.status === 403) {
                    errorMsg = 'ليس لديك صلاحية لعرض المولدات';
                } else if (xhr.status === 500) {
                    errorMsg = 'حدث خطأ في الخادم، يرجى المحاولة لاحقاً';
                }
                flash('danger', errorMsg);
            })
            .always(() => setLoading(false));
    }

    // Search functionality
    const runSearch = debounce(function () {
        const term = ($searchInput.val() || '').trim();
        const status = $('.gen-filter.active').data('filter');
        const operatorId = $operatorFilter.length ? $operatorFilter.val() : '';

        const params = {};
        if (term) params.q = term;
        if (status && status !== 'all') params.status = status;
        if (operatorId) params.operator_id = operatorId;

        // Update URL without reload
        const url = new URL(routes.search);
        Object.keys(params).forEach(key => {
            if (params[key]) {
                url.searchParams.set(key, params[key]);
            } else {
                url.searchParams.delete(key);
            }
        });
        window.history.pushState({}, '', url);

        loadGenerators(params);
    }, 350);

    $searchInput.on('input', runSearch);
    $searchBtn.on('click', runSearch);

    $clearSearchBtn.on('click', function () {
        $searchInput.val('');
        runSearch();
    });

    // Filters
    $(document).on('click', '.gen-filter', function () {
        $('.gen-filter').removeClass('active');
        $(this).addClass('active');
        runSearch();
    });

    // Operator filter change
    if ($operatorFilter.length) {
        $operatorFilter.on('change', runSearch);
    }

    // Delete generator
    $(document).on('click', '.gen-delete-btn', function (e) {
        e.preventDefault();
        const $btn = $(this);
        const generatorId = $btn.data('generator-id');
        const generatorName = $btn.data('generator-name');

        if (!confirm(`هل أنت متأكد من حذف المولد "${generatorName}"؟\nهذا الإجراء لا يمكن التراجع عنه.`)) {
            return;
        }

        const $row = $btn.closest('.gen-row');
        $row.fadeOut(200);

        $.ajax({
            url: deleteGeneratorUrl(generatorId),
            method: 'DELETE',
            dataType: 'json'
        })
            .done(res => {
                flash('success', res.message || 'تم حذف المولد بنجاح');
                
                // Reload the list
                const term = ($searchInput.val() || '').trim();
                const status = $('.gen-filter.active').data('filter');
                const operatorId = $operatorFilter.length ? $operatorFilter.val() : '';

                const params = {};
                if (term) params.q = term;
                if (status && status !== 'all') params.status = status;
                if (operatorId) params.operator_id = operatorId;

                loadGenerators(params);
            })
            .fail(xhr => {
                $row.fadeIn(200);
                let errorMsg = 'حدث خطأ أثناء حذف المولد';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        errorMsg = errors.join(' | ');
                    }
                } else if (xhr.status === 403) {
                    errorMsg = 'ليس لديك صلاحية لحذف هذا المولد';
                } else if (xhr.status === 404) {
                    errorMsg = 'المولد غير موجود';
                } else if (xhr.status === 500) {
                    errorMsg = 'حدث خطأ في الخادم، يرجى المحاولة لاحقاً';
                }
                flash('danger', errorMsg);
            });
    });

    // Pagination links
    $(document).on('click', '.pagination a', function (e) {
        e.preventDefault();
        const url = $(this).attr('href');
        if (url) {
            setLoading(true);
            $.get(url)
                .done(html => {
                    $listContainer.html(html);
                    $('html, body').animate({ scrollTop: 0 }, 300);
                })
                .fail(() => flash('danger', 'حدث خطأ أثناء التحميل'))
                .always(() => setLoading(false));
        }
    });

})(jQuery);

