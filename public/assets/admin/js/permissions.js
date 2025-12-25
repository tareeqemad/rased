(function ($) {
    const PERM = window.PERM || {};
    const ctx = PERM.ctx || {};
    const routes = PERM.routes || {};
    const rolesMeta = PERM.rolesMeta || {};

    const $overlay = $('#permissionsLoadingOverlay');
    const $alerts = $('#permAlerts');

    const $operatorSelect = $('#operatorSelect');
    const $userSelect = $('#userSelect');

    const $saveBtn = $('#savePermissionsBtn');
    const $resetBtn = $('#resetPermissionsBtn');

    const $selectedUserName = $('#selectedUserName');
    const $selectedUserRole = $('#selectedUserRole');

    const $statRole = $('#statRole');
    const $statDirect = $('#statDirect');
    const $statRevoked = $('#statRevoked');
    const $statEffective = $('#statEffective');
    const $statDirty = $('#statDirty');

    const $treeCount = $('#treeCount');

    let currentUserId = null;
    let currentUserRole = null;

    // Sets
    let roleSet = new Set();
    let directSet = new Set();
    let revokedSet = new Set();

    // baseline
    let baselineDirect = new Set();
    let baselineRevoked = new Set();

    function csrfToken() {
        return $('meta[name="csrf-token"]').attr('content') || $('#csrfToken').val();
    }

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': csrfToken() }
    });

    function setLoading(on) {
        $overlay.toggle(!!on);
    }

    function escapeHtml(str) {
        return String(str || '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function flash(type, msg) {
        // استخدام showToast من toast.blade.php
        if (typeof window.showToast === 'function') {
            const toastType = type === 'danger' ? 'danger' : type === 'warning' ? 'warning' : type === 'info' ? 'info' : 'success';
            window.showToast(msg, toastType);
        } else {
            // Fallback للـ alerts القديمة إذا لم يكن showToast متوفر
            const html = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${escapeHtml(msg)}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>`;
            $alerts.html(html);
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

    function userPermissionsUrl(userId) {
        return routes.userPermissions.replace('__USER__', userId);
    }

    function normalizeIds(arr) {
        return (arr || []).map(x => Number(x));
    }

    function cloneSet(s) {
        return new Set(Array.from(s));
    }

    function effectiveFor(id) {
        const hasBase = roleSet.has(id) || directSet.has(id);
        return hasBase && !revokedSet.has(id);
    }

    function baselineEffectiveFor(id) {
        const hasBase = roleSet.has(id) || baselineDirect.has(id);
        return hasBase && !baselineRevoked.has(id);
    }

    function computeEffectiveCount() {
        // (role ∪ direct) - revoked
        const tmp = new Set([...roleSet, ...directSet]);
        revokedSet.forEach(id => tmp.delete(id));
        return tmp.size;
    }

    function setRoleBadge(role) {
        const meta = rolesMeta[role] || null;
        if (!meta) {
            $selectedUserRole.attr('class', 'badge text-bg-secondary').text(role || '—');
            return;
        }
        $selectedUserRole
            .attr('class', `badge text-bg-${meta.color}`)
            .html(`<i class="bi ${meta.icon} me-1"></i>${meta.label}`);
    }

    function rowState(id) {
        if (revokedSet.has(id)) return 'revoked';
        if (directSet.has(id)) return 'direct';
        if (roleSet.has(id)) return 'role';
        return 'off';
    }

    function renderRow($row) {
        const id = Number($row.data('permission-id'));
        const state = rowState(id);
        const eff = effectiveFor(id);

        $row.removeClass('is-role is-direct is-revoked is-off is-dirty');
        $row.addClass(`is-${state}`);

        // dirty highlight
        if (currentUserId && eff !== baselineEffectiveFor(id)) {
            $row.addClass('is-dirty');
        }

        // badges
        $row.find('.perm-badge').addClass('d-none');
        if (state === 'revoked') $row.find('.perm-badge-revoked').removeClass('d-none');
        else if (state === 'direct') $row.find('.perm-badge-direct').removeClass('d-none');
        else if (state === 'role') $row.find('.perm-badge-role').removeClass('d-none');
        else $row.find('.perm-badge-off').removeClass('d-none');

        // toggle
        const $toggle = $row.find('.perm-toggle');
        $toggle.prop('disabled', !currentUserId);
        $toggle.prop('checked', eff);

        // dataset for filters
        $row.attr('data-state', state);
        $row.attr('data-effective', eff ? '1' : '0');
    }

    function renderAllRows() {
        $('.perm-row').each(function () {
            renderRow($(this));
        });
        updateStatsAndButtons();
    }

    function updateStatsAndButtons() {
        // حساب dirty count من جميع الصفوف (مرئية وغير مرئية)
        const dirtyCount = $('.perm-row.is-dirty').length;

        $statRole.text(roleSet.size);
        $statDirect.text(directSet.size);
        $statRevoked.text(revokedSet.size);
        $statEffective.text(computeEffectiveCount());
        $statDirty.text(dirtyCount);

        const hasChanges = dirtyCount > 0;
        $saveBtn.prop('disabled', !currentUserId || !hasChanges);
        $resetBtn.prop('disabled', !currentUserId || !hasChanges);
    }

    /**
     * Toggle واحد = تغيير النتيجة النهائية (Effective)
     * - إذا OFF وصلاحية من الدور => أضف revoked
     * - إذا OFF وصلاحية مش من الدور => احذف direct
     * - إذا ON => احذف revoked، وإذا مش من الدور => أضف direct
     */
    function applyEffective(id, on) {
        const isRole = roleSet.has(id);

        if (on) {
            revokedSet.delete(id);
            if (!isRole) directSet.add(id);
            else directSet.delete(id); // نظافة: لا داعي لDirect إذا الدور يكفي
        } else {
            directSet.delete(id);
            if (isRole) revokedSet.add(id);
            else revokedSet.delete(id);
        }
    }

    // ===== Select2 init =====

    function formatOperatorResult(item) {
        if (!item.id) return item.text;

        const owner = item.owner_name ? ` — <span class="text-muted">${escapeHtml(item.owner_name)}</span>` : '';
        const status = item.status ? `<span class="badge text-bg-light ms-2">${escapeHtml(item.status)}</span>` : '';
        const completed = item.profile_completed ? `<span class="badge text-bg-success ms-2">مكتمل</span>` : `<span class="badge text-bg-warning ms-2">غير مكتمل</span>`;

        return $(`
            <div>
                <div class="fw-bold">${escapeHtml(item.text)} ${status} ${completed}</div>
                <div class="small">${owner}</div>
            </div>
        `);
    }

    function formatUserResult(item) {
        if (!item.id) return item.text;

        let roleBadge = '';
        const meta = rolesMeta[item.role];
        if (meta) {
            roleBadge = `<span class="badge text-bg-${meta.color} ms-2"><i class="bi ${meta.icon} me-1"></i>${meta.label}</span>`;
        }

        return $(`
            <div>
                <div class="fw-bold">${escapeHtml(item.text)} ${roleBadge}</div>
                <div class="small text-muted">${escapeHtml(item.email || '')}</div>
            </div>
        `);
    }

    function initOperatorSelect() {
        if (!$operatorSelect.length) return;

        $operatorSelect.select2({
            width: '100%',
            placeholder: 'ابحث عن مشغل...',
            allowClear: true,
            dir: 'rtl',
            language: 'ar',
            ajax: {
                url: routes.selectOperators,
                dataType: 'json',
                delay: 250,
                data: params => ({
                    q: params.term || '',
                    page: params.page || 1
                }),
                processResults: data => ({
                    results: data.results || [],
                    pagination: data.pagination || { more: false }
                })
            },
            templateResult: formatOperatorResult,
            templateSelection: item => item.text || '—'
        });

        $operatorSelect.on('change', function () {
            const operatorId = $(this).val();
            currentUserId = null;
            resetUserContextUI();

            // reset sets
            roleSet = new Set();
            directSet = new Set();
            revokedSet = new Set();
            baselineDirect = new Set();
            baselineRevoked = new Set();

            // disable & clear user select
            if ($userSelect.length) {
                $userSelect.val(null).trigger('change');
                $userSelect.prop('disabled', !operatorId);
            }

            // re-init users with selected operator_id
            if (operatorId) {
                initUserSelect(operatorId);
            }
        });
    }

    function initUserSelect(operatorIdForSuperAdmin) {
        if (!$userSelect.length) return;

        // destroy old if exists
        if ($userSelect.hasClass('select2-hidden-accessible')) {
            $userSelect.select2('destroy');
        }

        $userSelect.select2({
            width: '100%',
            placeholder: ctx.isSuperAdmin ? 'اختر مستخدم...' : 'ابحث عن موظف/فني...',
            allowClear: true,
            dir: 'rtl',
            language: 'ar',
            ajax: {
                url: routes.selectUsers,
                dataType: 'json',
                delay: 250,
                data: params => {
                    const payload = {
                        q: params.term || '',
                        page: params.page || 1
                    };
                    if (ctx.isSuperAdmin) payload.operator_id = operatorIdForSuperAdmin;
                    return payload;
                },
                processResults: data => ({
                    results: data.results || [],
                    pagination: data.pagination || { more: false }
                })
            },
            templateResult: formatUserResult,
            templateSelection: item => item.text || '—'
        });

        $userSelect.prop('disabled', false);

        $userSelect.off('select2:select').on('select2:select', function (e) {
            const item = e.params.data;
            if (!item || !item.id) {
                flash('warning', 'يرجى اختيار مستخدم صحيح');
                return;
            }

            currentUserId = Number(item.id);
            currentUserRole = item.role || null;

            $selectedUserName.text(item.text || '—');
            setRoleBadge(currentUserRole);

            loadUserPermissions(currentUserId);
        });

        $userSelect.off('select2:clear').on('select2:clear', function () {
            currentUserId = null;
            resetUserContextUI();

            roleSet = new Set();
            directSet = new Set();
            revokedSet = new Set();
            baselineDirect = new Set();
            baselineRevoked = new Set();

            renderAllRows();
        });
    }

    function resetUserContextUI() {
        $selectedUserName.text('—');
        $selectedUserRole.attr('class', 'badge text-bg-secondary').text('—');

        $statRole.text('0');
        $statDirect.text('0');
        $statRevoked.text('0');
        $statEffective.text('0');
        $statDirty.text('0');

        $saveBtn.prop('disabled', true);
        $resetBtn.prop('disabled', true);

        // disable all toggles
        $('.perm-toggle').prop('disabled', true).prop('checked', false);
        $('.perm-row').removeClass('is-role is-direct is-revoked is-off is-dirty').addClass('is-off');
        $('.perm-badge').addClass('d-none');
        $('.perm-badge-off').removeClass('d-none');
    }

    // ===== Load user perms =====
    function loadUserPermissions(userId) {
        if (!userId) {
            flash('warning', 'يرجى اختيار مستخدم أولاً');
            return;
        }

        setLoading(true);

        $.ajax({
            url: userPermissionsUrl(userId),
            method: 'GET',
            dataType: 'json'
        })
            .done(res => {
                if (!res.success) {
                    flash('danger', res.message || 'فشل تحميل الصلاحيات');
                    resetUserContextUI();
                    return;
                }

                const u = res.user || {};
                // تحميل الصلاحيات الحالية من السيرفر
                directSet = new Set(normalizeIds(u.direct_permissions));
                roleSet = new Set(normalizeIds(u.role_permissions));
                revokedSet = new Set(normalizeIds(u.revoked_permissions));

                // حفظ نسخة احتياطية (baseline) للرجوع إليها عند Reset
                baselineDirect = cloneSet(directSet);
                baselineRevoked = cloneSet(revokedSet);

                renderAllRows();
                flash('success', 'تم تحميل الصلاحيات بنجاح. يمكنك الآن تعديلها.');
            })
            .fail(xhr => {
                let errorMsg = 'حدث خطأ أثناء تحميل الصلاحيات';
                
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.error) {
                        errorMsg = xhr.responseJSON.error;
                    } else if (xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        errorMsg = errors.join(' | ');
                    }
                } else if (xhr.status === 403) {
                    errorMsg = 'ليس لديك صلاحية لعرض صلاحيات هذا المستخدم';
                } else if (xhr.status === 404) {
                    errorMsg = 'المستخدم المحدد غير موجود';
                } else if (xhr.status === 500) {
                    errorMsg = 'حدث خطأ في الخادم، يرجى المحاولة لاحقاً';
                }
                
                flash('danger', errorMsg);
                resetUserContextUI();
            })
            .always(() => setLoading(false));
    }

    // ===== Toggle permission =====
    $(document).on('change', '.perm-toggle', function () {
        if (!currentUserId) {
            this.checked = false;
            flash('warning', 'يرجى اختيار مستخدم أولاً');
            return;
        }

        const id = Number($(this).data('permission-id'));
        const wasChecked = this.checked;
        applyEffective(id, wasChecked);

        const $row = $(this).closest('.perm-row');
        renderRow($row);
        updateStatsAndButtons();
    });

    // ===== Group enable/disable =====
    $(document).on('click', '.perm-group-enable', function (e) {
        e.preventDefault();
        e.stopPropagation();

        if (!currentUserId) {
            flash('warning', 'يرجى اختيار مستخدم أولاً');
            return;
        }

        const group = $(this).data('group');
        let count = 0;
        $(`.perm-row[data-group="${group}"]`).each(function () {
            const id = Number($(this).data('permission-id'));
            applyEffective(id, true);
            count++;
        });

        renderAllRows();
        updateStatsAndButtons();
        
        if (count > 0) {
            flash('success', `تم تفعيل ${count} صلاحية في هذه المجموعة`);
        }
    });

    $(document).on('click', '.perm-group-disable', function (e) {
        e.preventDefault();
        e.stopPropagation();

        if (!currentUserId) {
            flash('warning', 'يرجى اختيار مستخدم أولاً');
            return;
        }

        const group = $(this).data('group');
        let count = 0;
        $(`.perm-row[data-group="${group}"]`).each(function () {
            const id = Number($(this).data('permission-id'));
            applyEffective(id, false);
            count++;
        });

        renderAllRows();
        updateStatsAndButtons();
        
        if (count > 0) {
            flash('warning', `تم تعطيل ${count} صلاحية في هذه المجموعة`);
        }
    });

    // ===== Expand/Collapse All (Bootstrap 5 API) =====
    $('#expandAllBtn').on('click', function () {
        document.querySelectorAll('.perm-group-collapse').forEach(el => {
            bootstrap.Collapse.getOrCreateInstance(el, { toggle: false }).show();
        });
    });

    $('#collapseAllBtn').on('click', function () {
        document.querySelectorAll('.perm-group-collapse').forEach(el => {
            bootstrap.Collapse.getOrCreateInstance(el, { toggle: false }).hide();
        });
    });

    // ===== Search tree (server side) =====
    const runSearch = debounce(function () {
        const term = ($('#searchInput').val() || '').trim();
        
        // إذا كان البحث فارغاً، لا حاجة لـ loading overlay
        if (!term) {
            setLoading(true);
        } else {
            setLoading(true);
        }

        $.ajax({
            url: routes.searchTree,
            method: 'GET',
            data: { search: term },
            dataType: 'json'
        })
            .done(res => {
                if (!res.success) {
                    flash('warning', 'لم يتم العثور على نتائج');
                    return;
                }

                $('#permissionsTreeContainer').html(res.html);
                $treeCount.text(res.count ?? 0);

                // إعادة render جميع الصفوف حسب الحالة الحالية
                renderAllRows();

                // إعادة تطبيق الفلتر الحالي إذا كان مفعلاً
                const activeFilter = $('.perm-filter.active').data('filter');
                if (activeFilter && activeFilter !== 'all') {
                    applyFilter(activeFilter);
                }

                $('#clearSearchBtn').toggleClass('d-none', term === '');
            })
            .fail(xhr => {
                const errorMsg = xhr.responseJSON?.message || 'فشل تحميل نتائج البحث';
                flash('danger', errorMsg);
            })
            .always(() => setLoading(false));
    }, 350);

    $('#searchInput').on('input', runSearch);
    $('#searchBtn').on('click', runSearch);
    $('#clearSearchBtn').on('click', function () {
        $('#searchInput').val('');
        runSearch();
    });

    // ===== Filters (client side) =====
    function applyFilter(filter) {
        $('.perm-row').each(function () {
            const $row = $(this);
            const state = $row.attr('data-state');
            const effective = $row.attr('data-effective') === '1';

            let show = true;
            switch (filter) {
                case 'enabled': show = effective; break;
                case 'disabled': show = !effective; break;
                case 'revoked': show = (state === 'revoked'); break;
                case 'role': show = (state === 'role'); break;
                case 'direct': show = (state === 'direct'); break;
                case 'dirty': show = $row.hasClass('is-dirty'); break;
                default: show = true;
            }

            $row.toggle(show);
        });

        // hide group if no visible rows inside it (خصوصاً مع البحث/فلترة)
        $('.perm-group').each(function () {
            const $g = $(this);
            const visible = $g.find('.perm-row:visible').length;
            $g.toggle(visible > 0);
        });
    }

    $(document).on('click', '.perm-filter', function () {
        $('.perm-filter').removeClass('active');
        $(this).addClass('active');
        applyFilter($(this).data('filter'));
    });

    // ===== Reset =====
    $('#resetPermissionsBtn').on('click', function () {
        if (!currentUserId) {
            flash('warning', 'يرجى اختيار مستخدم أولاً');
            return;
        }

        // حساب dirty count من جميع الصفوف (مرئية وغير مرئية)
        let dirtyCount = 0;
        $('.perm-row').each(function() {
            if ($(this).hasClass('is-dirty')) {
                dirtyCount++;
            }
        });

        if (dirtyCount === 0) {
            flash('info', 'لا توجد تغييرات للتراجع عنها');
            return;
        }

        if (!confirm('هل أنت متأكد من التراجع عن جميع التغييرات؟')) {
            return;
        }

        // إظهار جميع الصفوف أولاً قبل reset (في حالة وجود فلتر نشط)
        $('.perm-row').show();
        $('.perm-group').show();

        // إعادة تعيين المجموعات إلى القيم الأساسية (التي تم حفظها عند التحميل)
        // هذا يلغي جميع التعديلات ويعيد الحالة الأصلية
        directSet = cloneSet(baselineDirect);
        revokedSet = cloneSet(baselineRevoked);

        // إعادة render جميع الصفوف - هذا سيحدّث حالة كل صف ويعيدها للحالة الأصلية
        renderAllRows();
        
        // إعادة تطبيق الفلتر الحالي إذا كان مفعلاً
        const activeFilter = $('.perm-filter.active').data('filter');
        if (activeFilter && activeFilter !== 'all') {
            applyFilter(activeFilter);
        }

        flash('success', 'تم التراجع عن جميع التغييرات بنجاح.');
    });

    // ===== Save =====
    $('#savePermissionsBtn').on('click', function () {
        if (!currentUserId) {
            flash('warning', 'يرجى اختيار مستخدم أولاً');
            return;
        }

        const dirtyCount = $('.perm-row.is-dirty').length;
        if (dirtyCount === 0) {
            flash('info', 'لا توجد تغييرات لحفظها');
            return;
        }

        // تأكيد قبل الحفظ
        if (!confirm(`هل أنت متأكد من حفظ ${dirtyCount} تغيير؟`)) {
            return;
        }

        const $btn = $(this);
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-2"></i>جاري الحفظ...');
        setLoading(true);

        const payload = {
            user_id: currentUserId,
            permissions: Array.from(directSet),
            revoked_permissions: Array.from(revokedSet),
        };

        $.ajax({
            url: routes.assign,
            method: 'POST',
            data: payload,
            dataType: 'json'
        })
            .done(res => {
                if (!res.success) {
                    flash('danger', res.message || 'فشل حفظ الصلاحيات');
                    $btn.prop('disabled', false).html(originalText);
                    return;
                }

                const u = res.user || {};
                directSet = new Set(normalizeIds(u.direct_permissions));
                roleSet = new Set(normalizeIds(u.role_permissions));
                revokedSet = new Set(normalizeIds(u.revoked_permissions));

                baselineDirect = cloneSet(directSet);
                baselineRevoked = cloneSet(revokedSet);

                renderAllRows();
                updateStatsAndButtons();

                flash('success', res.message || 'تم حفظ الصلاحيات بنجاح.');
                $btn.prop('disabled', false).html(originalText);
            })
            .fail(xhr => {
                let errorMsg = 'حدث خطأ أثناء حفظ الصلاحيات';
                
                if (xhr.responseJSON) {
                    // معالجة validation errors
                    if (xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        errorMsg = errors.join(' | ');
                    } else if (xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                } else if (xhr.status === 422) {
                    errorMsg = 'البيانات المرسلة غير صحيحة';
                } else if (xhr.status === 403) {
                    errorMsg = 'ليس لديك صلاحية لإجراء هذا التعديل';
                } else if (xhr.status === 404) {
                    errorMsg = 'المستخدم المحدد غير موجود';
                } else if (xhr.status === 500) {
                    errorMsg = 'حدث خطأ في الخادم، يرجى المحاولة لاحقاً';
                } else if (xhr.status === 0) {
                    errorMsg = 'فشل الاتصال بالخادم، تحقق من الاتصال بالإنترنت';
                }
                
                flash('danger', errorMsg);
                $btn.prop('disabled', false).html(originalText);
            })
            .always(() => setLoading(false));
    });

    // ===== Init =====
    $(document).ready(function () {
        // أول render (بدون مستخدم)
        resetUserContextUI();

        if (ctx.isSuperAdmin) {
            initOperatorSelect();
            // userSelect رح يتفعّل بعد اختيار مشغل
        } else if (ctx.isCompanyOwner) {
            // company owner: init directly
            initUserSelect(ctx.operatorId || null);
        }
    });

})(jQuery);
