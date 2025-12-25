@extends('layouts.admin')

@section('title', 'ملف المشغل')
@php
    $breadcrumbTitle = 'ملف المشغل';
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/admin/css/operators.css') }}">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@section('content')
<div class="operators-page operator-profile-page">
    <div class="row g-3">
        <div class="col-12 col-lg-4">
            <div class="card op-card">
                <div class="op-card-header">
                    <div class="op-title">
                        <i class="bi bi-person-badge me-2"></i>
                        ملخص المشغل
                    </div>
                    <div class="op-subtitle">الهدف: ملف مكتمل + بيانات دقيقة.</div>
                </div>

                <div class="card-body">
                    <div class="op-kv">
                        <div class="k">اسم الوحدة</div>
                        <div class="v">{{ $operator->unit_name ?? '—' }}</div>
                    </div>
                    <div class="op-kv">
                        <div class="k">رقم الوحدة</div>
                        <div class="v">{{ $operator->unit_number ?? '—' }}</div>
                    </div>
                    <div class="op-kv">
                        <div class="k">المحافظة</div>
                        <div class="v">{{ $operator->getGovernorateLabel() ?? '—' }}</div>
                    </div>

                    <div class="mt-3">
                        @if(empty($missing))
                            <div class="alert alert-success mb-0">
                                <i class="bi bi-check-circle me-1"></i>
                                الملف مكتمل ✅
                            </div>
                        @else
                            <div class="alert alert-warning mb-0">
                                <div class="fw-bold mb-1">حقول ناقصة:</div>
                                <ul class="mb-0 ps-3">
                                    @foreach($missing as $m)
                                        <li>{{ $m }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    <div class="d-grid gap-2 mt-3">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-people me-1"></i>
                            إدارة الموظفين
                        </a>
                        <a href="{{ route('admin.permissions.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-diagram-3 me-1"></i>
                            شجرة الصلاحيات
                        </a>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8">
            <div class="card op-card position-relative" id="profileCard">
                <div class="op-card-header d-flex align-items-center justify-content-between gap-2 flex-wrap">
                    <div>
                        <div class="op-title">
                            <i class="bi bi-ui-checks-grid me-2"></i>
                            بيانات المشغل
                        </div>
                        <div class="op-subtitle">قسّمناها Tabs عشان ما تحس إنك داخل حرب 😄</div>
                    </div>

                    <button class="btn btn-primary" id="saveProfileBtn" type="button">
                        <i class="bi bi-save me-1"></i>
                        حفظ
                    </button>
                </div>

                <div class="card-body">
                    <form id="operatorProfileForm" action="{{ route('admin.operators.profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <ul class="nav nav-pills op-tabs" id="profileTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-unit" type="button" role="tab">
                                    <i class="bi bi-info-circle me-1"></i> الوحدة
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-owner" type="button" role="tab">
                                    <i class="bi bi-person-badge me-1"></i> الملكية
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-location" type="button" role="tab">
                                    <i class="bi bi-geo-alt me-1"></i> الموقع
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-tech" type="button" role="tab">
                                    <i class="bi bi-lightning-charge me-1"></i> القدرات
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-benef" type="button" role="tab">
                                    <i class="bi bi-people me-1"></i> المستفيدون
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-status" type="button" role="tab">
                                    <i class="bi bi-activity me-1"></i> الحالة
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content pt-3">
                            {{-- TAB: UNIT --}}
                            <div class="tab-pane fade show active" id="tab-unit" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">رقم الوحدة <span class="text-danger">*</span></label>
                                        <input type="text" name="unit_number" id="unit_number" class="form-control"
                                               value="{{ old('unit_number', $operator->unit_number) }}" readonly required>
                                        <div class="form-text">يتولد تلقائيًا حسب المحافظة.</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">كود الوحدة</label>
                                        <input type="text" name="unit_code" class="form-control"
                                               value="{{ old('unit_code', $operator->unit_code) }}" readonly>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-semibold">اسم الوحدة <span class="text-danger">*</span></label>
                                        <input type="text" name="unit_name" class="form-control"
                                               value="{{ old('unit_name', $operator->unit_name) }}"
                                               placeholder="مثال: مولدات البابا" required>
                                    </div>
                                </div>
                            </div>

                            {{-- TAB: OWNER --}}
                            <div class="tab-pane fade" id="tab-owner" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">اسم المالك <span class="text-danger">*</span></label>
                                        <input type="text" name="owner_name" class="form-control"
                                               value="{{ old('owner_name', $operator->owner_name) }}" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">رقم هوية المالك</label>
                                        <input type="text" name="owner_id_number" class="form-control"
                                               value="{{ old('owner_id_number', $operator->owner_id_number) }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">جهة التشغيل <span class="text-danger">*</span></label>
                                        <select name="operation_entity" class="form-select" required>
                                            <option value="">اختر</option>
                                            <option value="same_owner" {{ old('operation_entity', $operator->operation_entity) === 'same_owner' ? 'selected' : '' }}>نفس المالك</option>
                                            <option value="other_party" {{ old('operation_entity', $operator->operation_entity) === 'other_party' ? 'selected' : '' }}>طرف آخر</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">رقم هوية المشغل <span class="text-danger">*</span></label>
                                        <input type="text" name="operator_id_number" class="form-control"
                                               value="{{ old('operator_id_number', $operator->operator_id_number) }}" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">رقم الموبايل</label>
                                        <input type="text" name="phone" class="form-control"
                                               value="{{ old('phone', $operator->phone) }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">رقم بديل</label>
                                        <input type="text" name="phone_alt" class="form-control"
                                               value="{{ old('phone_alt', $operator->phone_alt) }}">
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-semibold">البريد الإلكتروني</label>
                                        <input type="email" name="email" class="form-control"
                                               value="{{ old('email', $operator->email) }}">
                                    </div>
                                </div>
                            </div>

                            {{-- TAB: LOCATION --}}
                            <div class="tab-pane fade" id="tab-location" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">المحافظة <span class="text-danger">*</span></label>
                                        <select name="governorate" id="governorate" class="form-select" required>
                                            <option value="">اختر</option>
                                            @foreach(App\Governorate::all() as $gov)
                                                <option value="{{ $gov->value }}"
                                                    {{ old('governorate', $operator->governorate?->value) == $gov->value ? 'selected' : '' }}>
                                                    {{ $gov->label() }} ({{ $gov->code() }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">المدينة <span class="text-danger">*</span></label>
                                        <input type="text" name="city" class="form-control"
                                               value="{{ old('city', $operator->city) }}" required>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">العنوان التفصيلي <span class="text-danger">*</span></label>
                                        <input type="text" name="detailed_address" class="form-control"
                                               value="{{ old('detailed_address', $operator->detailed_address) }}" required>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-semibold">تحديد الموقع على الخريطة <span class="text-danger">*</span></label>
                                        <div id="map" class="op-map"></div>
                                        <div class="form-text">اضغط على الخريطة لتحديد الموقع.</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Latitude <span class="text-danger">*</span></label>
                                        <input type="number" step="0.00000001" name="latitude" id="latitude" class="form-control"
                                               value="{{ old('latitude', $operator->latitude) }}" readonly required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Longitude <span class="text-danger">*</span></label>
                                        <input type="number" step="0.00000001" name="longitude" id="longitude" class="form-control"
                                               value="{{ old('longitude', $operator->longitude) }}" readonly required>
                                    </div>
                                </div>
                            </div>

                            {{-- TAB: TECH --}}
                            <div class="tab-pane fade" id="tab-tech" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">إجمالي القدرة (KVA)</label>
                                        <input type="number" step="0.01" name="total_capacity" class="form-control"
                                               value="{{ old('total_capacity', $operator->total_capacity) }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">عدد المولدات</label>
                                        <input type="number" name="generators_count" class="form-control" min="0"
                                               value="{{ old('generators_count', $operator->generators_count) }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">مزامنة المولدات</label>
                                        <select name="synchronization_available" class="form-select">
                                            <option value="0" {{ old('synchronization_available', $operator->synchronization_available ? '1':'0') == '0' ? 'selected':'' }}>غير متوفرة</option>
                                            <option value="1" {{ old('synchronization_available', $operator->synchronization_available ? '1':'0') == '1' ? 'selected':'' }}>متوفرة</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">قدرة المزامنة القصوى (KVA)</label>
                                        <input type="number" step="0.01" name="max_synchronization_capacity" class="form-control" min="0"
                                               value="{{ old('max_synchronization_capacity', $operator->max_synchronization_capacity) }}">
                                    </div>
                                </div>
                            </div>

                            {{-- TAB: BENEF --}}
                            <div class="tab-pane fade" id="tab-benef" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">عدد المستفيدين</label>
                                        <input type="number" name="beneficiaries_count" class="form-control" min="0"
                                               value="{{ old('beneficiaries_count', $operator->beneficiaries_count) }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">الامتثال البيئي</label>
                                        <select name="environmental_compliance_status" class="form-select">
                                            <option value="">اختر</option>
                                            <option value="compliant" {{ old('environmental_compliance_status', $operator->environmental_compliance_status) === 'compliant' ? 'selected':'' }}>ملتزم</option>
                                            <option value="under_monitoring" {{ old('environmental_compliance_status', $operator->environmental_compliance_status) === 'under_monitoring' ? 'selected':'' }}>تحت المراقبة</option>
                                            <option value="under_evaluation" {{ old('environmental_compliance_status', $operator->environmental_compliance_status) === 'under_evaluation' ? 'selected':'' }}>تحت التقييم</option>
                                            <option value="non_compliant" {{ old('environmental_compliance_status', $operator->environmental_compliance_status) === 'non_compliant' ? 'selected':'' }}>غير ملتزم</option>
                                        </select>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-semibold">وصف المستفيدين</label>
                                        <textarea name="beneficiaries_description" class="form-control" rows="3">{{ old('beneficiaries_description', $operator->beneficiaries_description) }}</textarea>
                                    </div>
                                </div>
                            </div>

                            {{-- TAB: STATUS --}}
                            <div class="tab-pane fade" id="tab-status" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">حالة الوحدة <span class="text-danger">*</span></label>
                                        <select name="status" class="form-select" required>
                                            <option value="active" {{ old('status', $operator->status ?? 'active') === 'active' ? 'selected':'' }}>فعّالة</option>
                                            <option value="inactive" {{ old('status', $operator->status ?? 'active') === 'inactive' ? 'selected':'' }}>غير فعّالة</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="d-none" id="hiddenSubmitBtn"></button>
                    </form>
                </div>

                <div class="op-loading d-none" id="profileLoading">
                    <div class="text-center">
                        <div class="spinner-border" role="status"></div>
                        <div class="mt-2 text-muted fw-semibold">جاري الحفظ...</div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
    function notify(type, msg, title) {
        if (window.adminNotifications && typeof window.adminNotifications[type] === 'function') {
            window.adminNotifications[type](msg, title);
            return;
        }
        alert(msg);
    }

    const form = document.getElementById('operatorProfileForm');
    const saveBtn = document.getElementById('saveProfileBtn');
    const loading = document.getElementById('profileLoading');

    function setLoading(on) {
        loading.classList.toggle('d-none', !on);
        saveBtn.disabled = on;
    }

    function clearErrors() {
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
    }

    function showErrors(errors) {
        const firstField = Object.keys(errors || {})[0];
        if (firstField) {
            const input = form.querySelector(`[name="${CSS.escape(firstField)}"]`);
            if (input) {
                input.classList.add('is-invalid');
                const div = document.createElement('div');
                div.className = 'invalid-feedback';
                div.textContent = errors[firstField][0];
                input.insertAdjacentElement('afterend', div);

                // افتح التاب اللي فيه الحقل
                const pane = input.closest('.tab-pane');
                if (pane && pane.id) {
                    const tabBtn = document.querySelector(`[data-bs-target="#${pane.id}"]`);
                    if (tabBtn) new bootstrap.Tab(tabBtn).show();
                }

                input.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        // بقية الأخطاء
        Object.keys(errors || {}).forEach(field => {
            const input = form.querySelector(`[name="${CSS.escape(field)}"]`);
            if (!input) return;
            input.classList.add('is-invalid');
            // لا تكرر feedback إذا موجود
            if (input.nextElementSibling && input.nextElementSibling.classList.contains('invalid-feedback')) return;
            const div = document.createElement('div');
            div.className = 'invalid-feedback';
            div.textContent = errors[field][0];
            input.insertAdjacentElement('afterend', div);
        });
    }

    // ====== Unit Number generation ======
    const governorateSelect = document.getElementById('governorate');
    const unitNumberInput = document.getElementById('unit_number');
    const unitCodeInput = document.querySelector('input[name="unit_code"]');

    async function generateUnitNumber() {
        const gov = governorateSelect.value;
        if (!gov) return;

        try {
            const res = await fetch(`{{ url('/admin/operators/next-unit-number') }}/${gov}`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            if (data && data.success) {
                unitNumberInput.value = data.unit_number;
                if (unitCodeInput) unitCodeInput.value = data.unit_number;
            } else {
                notify('warning', data.message || 'تعذر توليد رقم الوحدة');
            }
        } catch (e) {
            notify('error', 'حدث خطأ أثناء توليد رقم الوحدة');
        }
    }

    governorateSelect?.addEventListener('change', generateUnitNumber);

    // ====== Map (lazy init when tab opens) ======
    let mapInited = false;
    let map, marker;

    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');

    function initMap() {
        if (mapInited) return;
        mapInited = true;

        const defaultLat = parseFloat(latInput.value || '31.3547');
        const defaultLng = parseFloat(lngInput.value || '34.3088');

        map = L.map('map').setView([defaultLat, defaultLng], 11);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(map);

        function setMarker(lat, lng, popupText) {
            if (marker) map.removeLayer(marker);
            marker = L.marker([lat, lng], { draggable: true }).addTo(map);
            marker.bindPopup(popupText || 'موقع المشغل').openPopup();

            marker.on('dragend', function () {
                const p = marker.getLatLng();
                latInput.value = p.lat.toFixed(8);
                lngInput.value = p.lng.toFixed(8);
            });
        }

        if (latInput.value && lngInput.value) {
            setMarker(parseFloat(latInput.value), parseFloat(lngInput.value), 'موقع المشغل الحالي');
        }

        map.on('click', function (e) {
            const lat = e.latlng.lat;
            const lng = e.latlng.lng;
            latInput.value = lat.toFixed(8);
            lngInput.value = lng.toFixed(8);
            setMarker(lat, lng, 'موقع المشغل المحدد');
        });
    }

    // when location tab shows
    document.querySelector('[data-bs-target="#tab-location"]')?.addEventListener('shown.bs.tab', function () {
        initMap();
        setTimeout(() => { map && map.invalidateSize(); }, 200);
    });

    // ====== AJAX submit ======
    async function submitProfile() {
        clearErrors();
        setLoading(true);

        try {
            const fd = new FormData(form);

            const res = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: fd
            });

            const data = await res.json();

            if (res.status === 422) {
                showErrors(data.errors || {});
                notify('error', 'تحقق من الحقول المطلوبة');
                return;
            }

            if (data && data.success) {
                notify('success', data.message || 'تم الحفظ');
            } else {
                notify('error', (data && data.message) ? data.message : 'فشل الحفظ');
            }

        } catch (e) {
            notify('error', 'حدث خطأ أثناء الحفظ');
        } finally {
            setLoading(false);
        }
    }

    saveBtn.addEventListener('click', submitProfile);

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        submitProfile();
    });

})();
</script>
@endpush
