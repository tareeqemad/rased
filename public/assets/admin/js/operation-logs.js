(function ($) {
    const OPLOG = window.OPLOG || {};
    const routes = OPLOG.routes || {};

    const $listContainer = $('#operationLogsListContainer');
    const $searchInput = $('#searchInput');
    const $searchBtn = $('#searchBtn');
    const $clearSearchBtn = $('#clearSearchBtn');
    const $countSpan = $('#operationLogsCount');
    const $operatorFilter = $('#operatorFilter');
    const $generatorFilter = $('#generatorFilter');
    const $dateFromFilter = $('#dateFromFilter');
    const $dateToFilter = $('#dateToFilter');
    const $groupByGeneratorToggle = $('#groupByGeneratorToggle');
    
    // Get the container with loading overlay (the card-body)
    const $loadingContainer = $listContainer.closest('.data-table-container');

    function csrfToken() {
        return $('meta[name="csrf-token"]').attr('content') || $('#csrfToken').val();
    }

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': csrfToken() }
    });

    function setLoading(on) {
        if (typeof window.DataTableLoading !== 'undefined') {
            if (on) {
                window.DataTableLoading.show($loadingContainer);
            } else {
                window.DataTableLoading.hide($loadingContainer);
            }
        }
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

    function deleteOperationLogUrl(operationLogId) {
        return routes.delete.replace('__ID__', operationLogId);
    }

    function loadOperationLogs(params = {}) {
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
                let errorMsg = 'حدث خطأ أثناء تحميل سجلات التشغيل';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        errorMsg = errors.join(' | ');
                    }
                } else if (xhr.status === 403) {
                    errorMsg = 'ليس لديك صلاحية لعرض سجلات التشغيل';
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
        const operatorId = $operatorFilter.length ? $operatorFilter.val() : '';
        const generatorId = $generatorFilter.length ? $generatorFilter.val() : '';
        const dateFrom = $dateFromFilter.length ? $dateFromFilter.val() : '';
        const dateTo = $dateToFilter.length ? $dateToFilter.val() : '';
        const groupByGenerator = $groupByGeneratorToggle.length && $groupByGeneratorToggle.is(':checked');

        const params = {};
        if (term) params.q = term;
        if (operatorId) params.operator_id = operatorId;
        if (generatorId) params.generator_id = generatorId;
        if (dateFrom) params.date_from = dateFrom;
        if (dateTo) params.date_to = dateTo;
        if (groupByGenerator) params.group_by_generator = 1;

        loadOperationLogs(params);
    }, 300);

    $searchBtn.on('click', runSearch);
    
    // Search on Enter key
    $searchInput.on('keypress', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            runSearch();
        }
    });

    $clearSearchBtn.on('click', function () {
        $searchInput.val('');
        if ($operatorFilter.length) $operatorFilter.val('');
        if ($generatorFilter.length) $generatorFilter.val('');
        if ($dateFromFilter.length) $dateFromFilter.val('');
        if ($dateToFilter.length) $dateToFilter.val('');
        $clearSearchBtn.addClass('d-none');
        runSearch();
    });

    // Show/hide clear button when typing (without auto search)
    $searchInput.on('input', function () {
        if ($(this).val().trim()) {
            $clearSearchBtn.removeClass('d-none');
        } else {
            $clearSearchBtn.addClass('d-none');
        }
    });

    // Operator filter change
    if ($operatorFilter.length) {
        $operatorFilter.on('change', function() {
            // If operator changed, reload generators list
            // For now, just run search
            runSearch();
        });
    }

    // Generator filter change
    if ($generatorFilter.length) {
        $generatorFilter.on('change', runSearch);
    }

    // Group by generator toggle
    if ($groupByGeneratorToggle.length) {
        $groupByGeneratorToggle.on('change', runSearch);
    }

    // Date filters change
    if ($dateFromFilter.length) {
        $dateFromFilter.on('change', runSearch);
    }
    if ($dateToFilter.length) {
        $dateToFilter.on('change', runSearch);
    }

    // Delete functionality
    $(document).on('click', '.log-delete-btn', function () {
        const $btn = $(this);
        const operationLogId = $btn.data('operation-log-id');
        const operationLogName = $btn.data('operation-log-name') || 'هذا السجل';

        if (!confirm(`هل أنت متأكد من حذف سجل التشغيل "${operationLogName}"؟\n\nهذا الإجراء لا يمكن التراجع عنه.`)) {
            return;
        }

        const deleteUrl = deleteOperationLogUrl(operationLogId);

        $.ajax({
            url: deleteUrl,
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .done(res => {
                if (res.success) {
                    flash('success', res.message || 'تم حذف سجل التشغيل بنجاح');
                    runSearch(); // Reload list
                } else {
                    flash('danger', res.message || 'حدث خطأ أثناء حذف سجل التشغيل');
                }
            })
            .fail(xhr => {
                let errorMsg = 'حدث خطأ أثناء حذف سجل التشغيل';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.status === 403) {
                    errorMsg = 'ليس لديك صلاحية لحذف سجل التشغيل';
                } else if (xhr.status === 404) {
                    errorMsg = 'سجل التشغيل غير موجود';
                }
                flash('danger', errorMsg);
            });
    });

    // Pagination links
    $(document).on('click', '.log-pagination a', function (e) {
        e.preventDefault();
        const url = $(this).attr('href');
        if (!url) return;

        const urlObj = new URL(url, window.location.origin);
        const params = {};
        urlObj.searchParams.forEach((value, key) => {
            params[key] = value;
        });

        // Preserve group_by_generator toggle state
        if ($groupByGeneratorToggle.length && $groupByGeneratorToggle.is(':checked')) {
            params.group_by_generator = 1;
        }

        loadOperationLogs(params);

        // Scroll to top
        $('html, body').animate({ scrollTop: $listContainer.offset().top - 100 }, 300);
    });

    // Export to window for global access
    window.OPLOG = OPLOG;
    window.OPLOG.loadOperationLogs = loadOperationLogs;

})(jQuery);




