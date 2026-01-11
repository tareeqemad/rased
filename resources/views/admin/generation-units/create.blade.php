@extends('layouts.admin')

@section('title', 'إضافة وحدة توليد جديدة')

@php
    $breadcrumbTitle = 'إضافة وحدة توليد جديدة';
    $breadcrumbParent = 'وحدات التوليد';
    $breadcrumbParentUrl = route('admin.generation-units.index');
@endphp

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@section('content')
<div class="general-page">
    <div class="row g-3">
        <div class="col-12">
            <div class="general-card position-relative" id="generationUnitCard">
                <div class="general-card-header">
                    <div>
                        <h5 class="general-title">
                            <i class="bi bi-lightning-charge me-2"></i>
                            إضافة وحدة توليد جديدة
                        </h5>
                        <div class="general-subtitle">
                            إدخال بيانات وحدة التوليد
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.generation-units.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-right me-1"></i>
                            العودة
                        </a>
                        <button class="btn btn-primary" id="saveBtn" type="button">
                            <i class="bi bi-check-lg me-1"></i>
                            حفظ
                        </button>
                    </div>
                </div>

                <div class="card-body pb-4">
                    <form id="generationUnitForm" action="{{ route('admin.generation-units.store') }}" method="POST">
                        @csrf

                        @if(auth()->user()->isSuperAdmin() && isset($allOperators))
                            <div class="row g-3 mb-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">المشغل <span class="text-danger">*</span></label>
                                    <select name="operator_id" id="operator_id" class="form-select @error('operator_id') is-invalid @enderror" required>
                                        <option value="">اختر المشغل</option>
                                        @foreach($allOperators as $op)
                                            <option value="{{ $op->id }}" {{ (old('operator_id') == $op->id || (isset($operator) && $operator && $operator->id == $op->id)) ? 'selected' : '' }}>
                                                {{ $op->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('operator_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        @elseif($operator)
                            <input type="hidden" name="operator_id" id="operator_id" value="{{ $operator->id }}">
                        @endif

                        <ul class="nav nav-pills mb-3" id="profileTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-basic" type="button" role="tab">
                                    <i class="bi bi-info-circle me-1"></i> البيانات الأساسية
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
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-tanks" type="button" role="tab">
                                    <i class="bi bi-droplet me-1"></i> الخزانات
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content pt-3">
                            {{-- TAB: BASIC INFO --}}
                            <div class="tab-pane fade show active" id="tab-basic" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">اسم وحدة التوليد <span class="text-danger">*</span></label>
                                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                                               value="{{ old('name') }}"
                                               placeholder="مثال: وحدة التوليد الرئيسية" required>
                                        <div class="form-text">الاسم الرسمي لوحدة التوليد</div>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">عدد المولدات المطلوبة</label>
                                        <input type="number" name="generators_count" id="generators_count" class="form-control @error('generators_count') is-invalid @enderror"
                                               value="{{ old('generators_count', 1) }}" min="1" max="99">
                                        <div class="form-text">عدد المولدات التي يجب أن تكون في هذه الوحدة (يمكن تحديده لاحقاً)</div>
                                        @error('generators_count')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-12">
                                        <div class="alert alert-info">
                                            <i class="bi bi-info-circle me-2"></i>
                                            <strong>ملاحظة:</strong> رقم الوحدة وكود الوحدة يتم توليدهما تلقائياً بناءً على المحافظة والمدينة.
                                        </div>
                                        <div class="alert alert-warning">
                                            <i class="bi bi-exclamation-triangle me-2"></i>
                                            <strong>يمكنك إدخال الحد الأدنى من البيانات الآن:</strong> اسم الوحدة، المحافظة، المدينة، العنوان التفصيلي، والإحداثيات. باقي البيانات يمكن ملؤها لاحقاً عند التعديل.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- TAB: OWNER --}}
                            <div class="tab-pane fade" id="tab-owner" role="tabpanel">
                                <div class="row g-3">
                                    {{-- جهة التشغيل (أول حقل وإجباري) --}}
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">جهة التشغيل <span class="text-danger">*</span></label>
                                        <select name="operation_entity_id" id="operation_entity_id" class="form-select @error('operation_entity_id') is-invalid @enderror" required>
                                            <option value="">اختر جهة التشغيل</option>
                                            @foreach($constants['operation_entity'] as $entity)
                                                <option value="{{ $entity->id }}" data-code="{{ $entity->code }}" {{ old('operation_entity_id') == $entity->id ? 'selected' : '' }}>
                                                    {{ $entity->label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('operation_entity_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">اسم المالك</label>
                                        <input type="text" name="owner_name" class="form-control @error('owner_name') is-invalid @enderror"
                                               value="{{ old('owner_name') }}">
                                        <div class="form-text">يمكن ملؤه لاحقاً</div>
                                        @error('owner_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">رقم هوية المالك</label>
                                        <input type="text" name="owner_id_number" class="form-control @error('owner_id_number') is-invalid @enderror"
                                               value="{{ old('owner_id_number') }}">
                                        @error('owner_id_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">رقم هوية المشغل</label>
                                        <input type="text" name="operator_id_number" class="form-control @error('operator_id_number') is-invalid @enderror"
                                               value="{{ old('operator_id_number') }}">
                                        <div class="form-text">يمكن ملؤه لاحقاً</div>
                                        @error('operator_id_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">رقم الموبايل</label>
                                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                               value="{{ old('phone') }}">
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">رقم بديل</label>
                                        <input type="text" name="phone_alt" class="form-control @error('phone_alt') is-invalid @enderror"
                                               value="{{ old('phone_alt') }}">
                                        @error('phone_alt')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">البريد الإلكتروني</label>
                                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                               value="{{ old('email') }}">
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- TAB: LOCATION --}}
                            <div class="tab-pane fade" id="tab-location" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">المحافظة <span class="text-danger">*</span></label>
                                        <select name="governorate_id" id="governorate" class="form-select @error('governorate_id') is-invalid @enderror" required>
                                            <option value="">اختر</option>
                                            @forelse($governorates as $gov)
                                                <option value="{{ $gov->id }}"
                                                    data-governorate-code="{{ $gov->code }}"
                                                    {{ old('governorate_id', $selectedGovernorateId) == $gov->id ? 'selected' : '' }}>
                                                    {{ $gov->label }} ({{ $gov->code }})
                                                </option>
                                            @empty
                                                <option value="" disabled>لا توجد محافظات متاحة</option>
                                            @endforelse
                                        </select>
                                        @if($governorates->isEmpty())
                                            <div class="form-text text-danger">تحذير: لا توجد محافظات في الثوابت. يرجى تشغيل ConstantSeeder.</div>
                                        @endif
                                        @error('governorate_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">المدينة <span class="text-danger">*</span></label>
                                        <select name="city_id" id="city_id" class="form-select @error('city_id') is-invalid @enderror" required>
                                            <option value="">اختر المدينة</option>
                                            @foreach($cities as $city)
                                                <option value="{{ $city->id }}"
                                                    {{ old('city_id') == $city->id ? 'selected' : '' }}>
                                                    {{ $city->label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('city_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">العنوان التفصيلي <span class="text-danger">*</span></label>
                                        <input type="text" name="detailed_address" class="form-control @error('detailed_address') is-invalid @enderror"
                                               value="{{ old('detailed_address') }}" required>
                                        @error('detailed_address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-semibold">تحديد الموقع على الخريطة <span class="text-danger">*</span></label>
                                        <div id="map" class="op-map"></div>
                                        <div class="form-text">اضغط على الخريطة لتحديد الموقع.</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Latitude <span class="text-danger">*</span></label>
                                        <input type="number" step="0.00000001" name="latitude" id="latitude" class="form-control @error('latitude') is-invalid @enderror"
                                               value="{{ old('latitude') }}" readonly required>
                                        @error('latitude')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Longitude <span class="text-danger">*</span></label>
                                        <input type="number" step="0.00000001" name="longitude" id="longitude" class="form-control @error('longitude') is-invalid @enderror"
                                               value="{{ old('longitude') }}" readonly required>
                                        @error('longitude')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- TAB: TECH --}}
                            <div class="tab-pane fade" id="tab-tech" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">إجمالي القدرة (KVA)</label>
                                        <input type="number" step="0.01" name="total_capacity" class="form-control @error('total_capacity') is-invalid @enderror"
                                               value="{{ old('total_capacity') }}">
                                        @error('total_capacity')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">مزامنة المولدات</label>
                                        <select name="synchronization_available_id" class="form-select @error('synchronization_available_id') is-invalid @enderror">
                                            <option value="">اختر</option>
                                            @foreach($constants['synchronization_available'] as $sync)
                                                <option value="{{ $sync->id }}" {{ old('synchronization_available_id') == $sync->id ? 'selected' : '' }}>
                                                    {{ $sync->label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('synchronization_available_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">قدرة المزامنة القصوى (KVA)</label>
                                        <input type="number" step="0.01" name="max_synchronization_capacity" class="form-control @error('max_synchronization_capacity') is-invalid @enderror" min="0"
                                               value="{{ old('max_synchronization_capacity') }}">
                                        @error('max_synchronization_capacity')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- TAB: BENEF --}}
                            <div class="tab-pane fade" id="tab-benef" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">عدد المستفيدين</label>
                                        <input type="number" name="beneficiaries_count" class="form-control @error('beneficiaries_count') is-invalid @enderror" min="0"
                                               value="{{ old('beneficiaries_count') }}">
                                        @error('beneficiaries_count')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">الامتثال البيئي</label>
                                        <select name="environmental_compliance_status_id" class="form-select @error('environmental_compliance_status_id') is-invalid @enderror">
                                            <option value="">اختر</option>
                                            @foreach($constants['environmental_compliance_status'] as $compliance)
                                                <option value="{{ $compliance->id }}" {{ old('environmental_compliance_status_id') == $compliance->id ? 'selected' : '' }}>
                                                    {{ $compliance->label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('environmental_compliance_status_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-semibold">وصف المستفيدين</label>
                                        <textarea name="beneficiaries_description" class="form-control @error('beneficiaries_description') is-invalid @enderror" rows="3">{{ old('beneficiaries_description') }}</textarea>
                                        @error('beneficiaries_description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- TAB: STATUS --}}
                            <div class="tab-pane fade" id="tab-status" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">حالة الوحدة</label>
                                        <select name="status_id" class="form-select @error('status_id') is-invalid @enderror">
                                            <option value="">اختر</option>
                                            @foreach($constants['status'] as $status)
                                                <option value="{{ $status->id }}" {{ old('status_id', $constants['status']->firstWhere('code', 'ACTIVE')?->id) == $status->id ? 'selected' : '' }}>
                                                    {{ $status->label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="form-text">سيتم تعيين حالة "فعال" تلقائياً إذا لم يتم الاختيار</div>
                                        @error('status_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- TAB: TANKS --}}
                            <div class="tab-pane fade" id="tab-tanks" role="tabpanel">
                                <div class="mb-4">
                                    <!-- خزان وقود خارجي -->
                                    <div class="card mb-4 border-0 shadow-sm">
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">خزان وقود خارجي <span class="text-danger">*</span></label>
                                                    <select name="external_fuel_tank" id="external_fuel_tank" class="form-select @error('external_fuel_tank') is-invalid @enderror">
                                                        <option value="0" {{ old('external_fuel_tank', '0') == '0' ? 'selected' : '' }}>لا</option>
                                                        <option value="1" {{ old('external_fuel_tank') == '1' ? 'selected' : '' }}>نعم</option>
                                                    </select>
                                                    @error('external_fuel_tank')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-md-6" id="fuel_tanks_count_wrapper" style="display: none;">
                                                    <label class="form-label fw-semibold">عدد خزانات الوقود (1-10) <span class="text-danger">*</span></label>
                                                    <select name="fuel_tanks_count" id="fuel_tanks_count" class="form-select @error('fuel_tanks_count') is-invalid @enderror">
                                                        <option value="0">اختر العدد</option>
                                                        @for($i = 1; $i <= 10; $i++)
                                                            <option value="{{ $i }}" {{ old('fuel_tanks_count') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                                        @endfor
                                                    </select>
                                                    @error('fuel_tanks_count')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <!-- حقل hidden لإرسال القيمة الافتراضية عندما يكون external_fuel_tank = 0 -->
                                                <input type="hidden" id="fuel_tanks_count_hidden" name="fuel_tanks_count" value="{{ old('fuel_tanks_count', '0') }}">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- خزانات الوقود الديناميكية -->
                                    <div id="fuel_tanks_container"></div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="d-none" id="hiddenSubmitBtn"></button>
                    </form>
                </div>

                <div class="data-table-loading d-none" id="loading">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status"></div>
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
<script src="{{ asset('assets/admin/js/general-helpers.js') }}"></script>
<script>
(function () {
    function notify(type, msg, title) {
        if (window.adminNotifications && typeof window.adminNotifications[type] === 'function') {
            window.adminNotifications[type](msg, title);
            return;
        }
        alert(msg);
    }

    const form = document.getElementById('generationUnitForm');
    const saveBtn = document.getElementById('saveBtn');
    const loading = document.getElementById('loading');

    function setLoading(on) {
        loading.classList.toggle('d-none', !on);
        saveBtn.disabled = on;
    }

    function clearErrors() {
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
    }

    function showErrors(errors) {
        // خريطة أسماء الحقول العربية
        const fieldLabels = {
            'name': 'اسم وحدة التوليد',
            'operator_id': 'المشغل',
            'unit_number': 'رقم الوحدة',
            'unit_code': 'كود الوحدة',
            'generators_count': 'عدد المولدات',
            'status': 'الحالة',
            'owner_name': 'اسم المالك',
            'owner_id_number': 'رقم هوية المالك',
            'operation_entity_id': 'جهة التشغيل',
            'operation_entity': 'كيان التشغيل',
            'operator_id_number': 'رقم هوية المشغل',
            'phone': 'رقم الهاتف',
            'phone_alt': 'رقم الهاتف البديل',
            'email': 'البريد الإلكتروني',
            'governorate_id': 'المحافظة',
            'governorate': 'المحافظة',
            'city_id': 'المدينة',
            'detailed_address': 'العنوان التفصيلي',
            'latitude': 'خط العرض',
            'longitude': 'خط الطول',
            'total_capacity': 'السعة الإجمالية',
            'synchronization_available_id': 'مزامنة المولدات',
            'synchronization_available': 'التزامن متاح',
            'max_synchronization_capacity': 'السعة القصوى للتزامن',
            'beneficiaries_count': 'عدد المستفيدين',
            'beneficiaries_description': 'وصف المستفيدين',
            'environmental_compliance_status_id': 'الامتثال البيئي',
            'environmental_compliance_status': 'حالة الامتثال البيئي',
            'status_id': 'حالة الوحدة',
            'external_fuel_tank': 'خزان وقود خارجي',
            'fuel_tanks_count': 'عدد خزانات الوقود',
            'fuel_tanks': 'خزانات الوقود'
        };

        // ترتيب أولويات الحقول حسب التابات (من الأول للأخير)
        const fieldOrder = [
            // tab-basic: البيانات الأساسية
            'operator_id', 'name', 'generators_count',
            // tab-owner: الملكية
            'operation_entity_id', 'owner_name', 'owner_id_number', 'operator_id_number', 'phone', 'phone_alt', 'email',
            // tab-location: الموقع
            'governorate_id', 'city_id', 'detailed_address', 'latitude', 'longitude',
            // tab-tech: القدرات
            'total_capacity', 'synchronization_available_id', 'max_synchronization_capacity',
            // tab-benef: المستفيدون
            'beneficiaries_count', 'beneficiaries_description', 'environmental_compliance_status_id',
            // tab-status: الحالة
            'status_id',
            // tab-tanks: الخزانات
            'external_fuel_tank', 'fuel_tanks_count', 'fuel_tanks'
        ];

        // إيجاد أول حقل خطأ حسب ترتيب الأولويات
        let firstField = null;
        for (const field of fieldOrder) {
            if (errors[field]) {
                firstField = field;
                break;
            }
        }
        
        // إذا لم يتم العثور على الحقل في الترتيب المحدد، استخدم أول حقل في الأخطاء
        if (!firstField && Object.keys(errors).length > 0) {
            firstField = Object.keys(errors)[0];
        }

        const errorMessages = [];
        
        // جمع جميع رسائل الأخطاء حسب ترتيب الأولويات
        for (const field of fieldOrder) {
            if (errors[field]) {
                const errorMsg = Array.isArray(errors[field]) ? errors[field][0] : errors[field];
                const fieldLabel = fieldLabels[field] || field;
                errorMessages.push(fieldLabel + ': ' + errorMsg);
            }
        }
        
        // إضافة أي حقول خطأ أخرى غير موجودة في fieldOrder
        Object.keys(errors || {}).forEach(field => {
            if (!fieldOrder.includes(field)) {
                const errorMsg = Array.isArray(errors[field]) ? errors[field][0] : errors[field];
                const fieldLabel = fieldLabels[field] || field;
                errorMessages.push(fieldLabel + ': ' + errorMsg);
            }
        });

        // عرض جميع رسائل الأخطاء في إشعار أحمر
        if (errorMessages.length > 0) {
            let errorMessage = 'يرجى تصحيح الأخطاء التالية:\n\n';
            errorMessage += errorMessages.join('\n');
            notify('error', errorMessage, 'تحقق من الأخطاء');
        }

        // عرض أول حقل خطأ وفتح التاب المناسب
        if (firstField) {
            const input = form.querySelector(`[name="${CSS.escape(firstField)}"]`);
            if (input) {
                input.classList.add('is-invalid');
                const div = document.createElement('div');
                div.className = 'invalid-feedback';
                div.textContent = Array.isArray(errors[firstField]) ? errors[firstField][0] : errors[firstField];
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

        // عرض جميع الأخطاء على الحقول
        Object.keys(errors || {}).forEach(field => {
            const input = form.querySelector(`[name="${CSS.escape(field)}"]`);
            if (!input) return;
            input.classList.add('is-invalid');
            if (input.nextElementSibling && input.nextElementSibling.classList.contains('invalid-feedback')) return;
            const div = document.createElement('div');
            div.className = 'invalid-feedback';
            div.textContent = Array.isArray(errors[field]) ? errors[field][0] : errors[field];
            input.insertAdjacentElement('afterend', div);
        });
    }

    // ====== جلب بيانات المشغل تلقائياً عند اختيار "نفس المالك" ======
    const operationEntitySelect = document.getElementById('operation_entity_id');
    const operatorIdSelect = document.getElementById('operator_id');
    const ownerNameInput = form.querySelector('input[name="owner_name"]');
    const ownerIdNumberInput = form.querySelector('input[name="owner_id_number"]');
    const operatorIdNumberInput = form.querySelector('input[name="operator_id_number"]');

    function loadOperatorData(operatorId) {
        if (!operatorId) return;

        fetch(`{{ url('admin/operators') }}/${operatorId}/data`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.operator) {
                if (ownerNameInput) ownerNameInput.value = data.operator.owner_name || '';
                if (ownerIdNumberInput) ownerIdNumberInput.value = data.operator.owner_id_number || '';
                if (operatorIdNumberInput) operatorIdNumberInput.value = data.operator.operator_id_number || '';
            }
        })
        .catch(err => {
            console.error('Error loading operator data:', err);
        });
    }

    operationEntitySelect?.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const code = selectedOption ? selectedOption.getAttribute('data-code') : '';
        const isSameOwner = code === 'SAME_OWNER';

        if (isSameOwner) {
            // إذا كان "نفس المالك" وكان هناك مشغل محدد، جلب البيانات
            const operatorId = operatorIdSelect ? operatorIdSelect.value : 
                              (form.querySelector('input[name="operator_id"]') ? form.querySelector('input[name="operator_id"]').value : null);
            if (operatorId) {
                loadOperatorData(operatorId);
            }

            // تعطيل الحقول وجعلها للقراءة فقط
            if (ownerNameInput) {
                ownerNameInput.readOnly = true;
                ownerNameInput.style.backgroundColor = '#f8f9fa';
            }
            if (ownerIdNumberInput) {
                ownerIdNumberInput.readOnly = true;
                ownerIdNumberInput.style.backgroundColor = '#f8f9fa';
            }
            if (operatorIdNumberInput) {
                operatorIdNumberInput.readOnly = true;
                operatorIdNumberInput.style.backgroundColor = '#f8f9fa';
            }
        } else {
            // إذا كان "طرف آخر"، مسح الحقول وتمكينها للإدخال
            if (ownerNameInput) {
                ownerNameInput.value = '';
                ownerNameInput.readOnly = false;
                ownerNameInput.style.backgroundColor = '';
            }
            if (ownerIdNumberInput) {
                ownerIdNumberInput.value = '';
                ownerIdNumberInput.readOnly = false;
                ownerIdNumberInput.style.backgroundColor = '';
            }
            if (operatorIdNumberInput) {
                operatorIdNumberInput.value = '';
                operatorIdNumberInput.readOnly = false;
                operatorIdNumberInput.style.backgroundColor = '';
            }
        }
    });

    // عند تغيير المشغل (للسوبر أدمن فقط)
    operatorIdSelect?.addEventListener('change', function() {
        if (operationEntitySelect) {
            const selectedOption = operationEntitySelect.options[operationEntitySelect.selectedIndex];
            const code = selectedOption ? selectedOption.getAttribute('data-code') : '';
            if (code === 'SAME_OWNER') {
                loadOperatorData(this.value);
            }
        }
    });

    // عند تحميل الصفحة، إذا كان "نفس المالك" محدد مسبقاً
    document.addEventListener('DOMContentLoaded', function() {
        if (operationEntitySelect) {
            const selectedOption = operationEntitySelect.options[operationEntitySelect.selectedIndex];
            const code = selectedOption ? selectedOption.getAttribute('data-code') : '';
            if (code === 'SAME_OWNER') {
                const operatorId = operatorIdSelect ? operatorIdSelect.value : 
                                  (form.querySelector('input[name="operator_id"]') ? form.querySelector('input[name="operator_id"]').value : null);
                if (operatorId) {
                    loadOperatorData(operatorId);
                    // تعطيل الحقول
                    if (ownerNameInput) {
                        ownerNameInput.readOnly = true;
                        ownerNameInput.style.backgroundColor = '#f8f9fa';
                    }
                    if (ownerIdNumberInput) {
                        ownerIdNumberInput.readOnly = true;
                        ownerIdNumberInput.style.backgroundColor = '#f8f9fa';
                    }
                    if (operatorIdNumberInput) {
                        operatorIdNumberInput.readOnly = true;
                        operatorIdNumberInput.style.backgroundColor = '#f8f9fa';
                    }
                }
            } else {
                // إذا كان "طرف آخر"، التأكد من أن الحقول قابلة للتحرير
                if (ownerNameInput) {
                    ownerNameInput.readOnly = false;
                    ownerNameInput.style.backgroundColor = '';
                }
                if (ownerIdNumberInput) {
                    ownerIdNumberInput.readOnly = false;
                    ownerIdNumberInput.style.backgroundColor = '';
                }
                if (operatorIdNumberInput) {
                    operatorIdNumberInput.readOnly = false;
                    operatorIdNumberInput.style.backgroundColor = '';
                }
            }
        }
    });

    // ====== تحديث المدن عند تغيير المحافظة ======
    const governorateSelect = document.getElementById('governorate');
    const citySelect = document.getElementById('city_id');

    governorateSelect?.addEventListener('change', function() {
        if (typeof GeneralHelpers !== 'undefined' && GeneralHelpers.updateCitiesSelect) {
            // الآن governorateSelect.value هو ID وليس code
            const governorateId = this.value;
            if (governorateId) {
                // تفعيل المدينة قبل تحديثها (في حالة كانت معطلة)
                if (citySelect) {
                    citySelect.disabled = false;
                }
                GeneralHelpers.updateCitiesSelect('#governorate', '#city_id');
            } else {
                // إذا لم يتم اختيار محافظة، تعطيل المدينة
                if (citySelect) {
                    citySelect.innerHTML = '<option value="">اختر المدينة</option>';
                    citySelect.disabled = true;
                }
            }
        }
    });

    // تحميل المدن تلقائياً عند تحميل الصفحة إذا كانت المحافظة محددة
    document.addEventListener('DOMContentLoaded', function() {
        // تعطيل المدينة في البداية إذا لم تكن هناك مدن محملة (أي إذا كان هناك فقط option واحد "اختر المدينة")
        if (citySelect && citySelect.options.length <= 1) {
            citySelect.disabled = true;
        }
        
        if (governorateSelect && governorateSelect.value) {
            // الآن governorateSelect.value هو ID وليس code
            const governorateId = governorateSelect.value;
            if (governorateId && typeof GeneralHelpers !== 'undefined' && GeneralHelpers.updateCitiesSelect) {
                // تفعيل المدينة قبل تحديثها
                if (citySelect) {
                    citySelect.disabled = false;
                }
                const cityId = citySelect ? citySelect.value : null;
                GeneralHelpers.updateCitiesSelect('#governorate', '#city_id', {
                    selectedValue: cityId
                });
            }
        }
    });

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
            marker.bindPopup(popupText || 'موقع وحدة التوليد').openPopup();

            marker.on('dragend', function () {
                const p = marker.getLatLng();
                latInput.value = p.lat.toFixed(8);
                lngInput.value = p.lng.toFixed(8);
            });
        }

        if (latInput.value && lngInput.value) {
            setMarker(parseFloat(latInput.value), parseFloat(lngInput.value), 'موقع وحدة التوليد الحالي');
        } else {
            // تعيين موقع افتراضي
            setMarker(defaultLat, defaultLng, 'موقع وحدة التوليد');
            latInput.value = defaultLat.toFixed(8);
            lngInput.value = defaultLng.toFixed(8);
        }

        map.on('click', function (e) {
            const lat = e.latlng.lat;
            const lng = e.latlng.lng;
            latInput.value = lat.toFixed(8);
            lngInput.value = lng.toFixed(8);
            setMarker(lat, lng, 'موقع وحدة التوليد المحدد');
        });
    }

    // when location tab shows
    document.querySelector('[data-bs-target="#tab-location"]')?.addEventListener('shown.bs.tab', function () {
        initMap();
        setTimeout(() => { map && map.invalidateSize(); }, 200);
    });

    // ====== AJAX submit ======
    async function submitForm() {
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
                setTimeout(() => {
                    window.location.href = '{{ route('admin.generation-units.index') }}';
                }, 1500);
            } else {
                notify('error', (data && data.message) ? data.message : 'فشل الحفظ');
            }

        } catch (e) {
            notify('error', 'حدث خطأ أثناء الحفظ');
        } finally {
            setLoading(false);
        }
    }

    saveBtn.addEventListener('click', submitForm);

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        submitForm();
    });

    // ====== تمرير الثوابت للـ JavaScript ======
    window.GENERATION_UNIT_CONSTANTS = {
        location: @json(($constants['location'] ?? collect())->map(fn($c) => ['id' => $c->id, 'label' => $c->label])->values()),
        material: @json(($constants['material'] ?? collect())->map(fn($c) => ['id' => $c->id, 'label' => $c->label])->values()),
        usage: @json(($constants['usage'] ?? collect())->map(fn($c) => ['id' => $c->id, 'label' => $c->label])->values()),
        measurement_method: @json(($constants['measurement_method'] ?? collect())->map(fn($c) => ['id' => $c->id, 'label' => $c->label])->values()),
    };

    // ====== إدارة خزانات الوقود الديناميكية ======
    const externalFuelTankSelect = document.getElementById('external_fuel_tank');
    const fuelTanksCountWrapper = document.getElementById('fuel_tanks_count_wrapper');
    const fuelTanksCountSelect = document.getElementById('fuel_tanks_count');
    const fuelTanksCountHidden = document.getElementById('fuel_tanks_count_hidden');
    const fuelTanksContainer = document.getElementById('fuel_tanks_container');

    // عند تغيير "خزان وقود خارجي"
    if (externalFuelTankSelect) {
        externalFuelTankSelect.addEventListener('change', function() {
            if (this.value === '1') {
                fuelTanksCountWrapper.style.display = 'block';
                if (fuelTanksCountSelect) fuelTanksCountSelect.required = true;
                if (fuelTanksCountHidden) {
                    fuelTanksCountHidden.removeAttribute('name');
                    fuelTanksCountHidden.disabled = true;
                }
                if (fuelTanksCountSelect) fuelTanksCountSelect.disabled = false;
            } else {
                fuelTanksCountWrapper.style.display = 'none';
                if (fuelTanksCountSelect) fuelTanksCountSelect.required = false;
                if (fuelTanksCountSelect) fuelTanksCountSelect.value = '0';
                if (fuelTanksContainer) fuelTanksContainer.innerHTML = '';
                if (fuelTanksCountHidden) {
                    fuelTanksCountHidden.setAttribute('name', 'fuel_tanks_count');
                    fuelTanksCountHidden.disabled = false;
                }
                if (fuelTanksCountSelect) fuelTanksCountSelect.disabled = true;
            }
        });
    }

    // عند تغيير عدد الخزانات
    if (fuelTanksCountSelect) {
        fuelTanksCountSelect.addEventListener('change', function() {
            const count = parseInt(this.value);
            if (count > 0 && count <= 10) {
                renderFuelTanks(count);
            } else {
                if (fuelTanksContainer) fuelTanksContainer.innerHTML = '';
            }
        });
    }

    // دالة لرسم خزانات الوقود
    function renderFuelTanks(count) {
        if (!fuelTanksContainer) return;
        fuelTanksContainer.innerHTML = '';

        for (let i = 1; i <= count; i++) {
            const tankHtml = `
                <div class="card mb-3 border-0 shadow-sm" id="tank_${i}">
                    <div class="card-header" style="background: linear-gradient(135deg, #2563eb 0%, #60a5fa 100%); padding: 1rem;">
                        <h6 class="mb-0 fw-bold text-white">
                            <i class="bi bi-droplet-fill me-2"></i>خزان الوقود ${i}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">سعة الخزان ${i} (لتر) <span class="text-danger">*</span></label>
                                <input type="number" 
                                       name="fuel_tanks[${i-1}][capacity]" 
                                       class="form-control" 
                                       min="0" 
                                       max="10000" 
                                       step="1"
                                       placeholder="أدخل السعة باللتر">
                                <small class="form-text text-muted">يمكن إدخال سعة تصل إلى 10000 لتر</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">موقع الخزان ${i} <span class="text-danger">*</span></label>
                                <select name="fuel_tanks[${i-1}][location_id]" class="form-select" required>
                                    <option value="">اختر الموقع</option>
                                    ${(window.GENERATION_UNIT_CONSTANTS.location && window.GENERATION_UNIT_CONSTANTS.location.length > 0) 
                                        ? window.GENERATION_UNIT_CONSTANTS.location.map(loc => `<option value="${loc.id}">${loc.label}</option>`).join('')
                                        : ''
                                    }
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">نظام الفلترة ${i}</label>
                                <select name="fuel_tanks[${i-1}][filtration_system_available]" class="form-select">
                                    <option value="0">غير متوفر</option>
                                    <option value="1">متوفر</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">حالة الخزان ${i}</label>
                                <input type="text" name="fuel_tanks[${i-1}][condition]" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">مادة التصنيع ${i}</label>
                                <select name="fuel_tanks[${i-1}][material_id]" class="form-select">
                                    <option value="">اختر المادة</option>
                                    ${(window.GENERATION_UNIT_CONSTANTS.material && window.GENERATION_UNIT_CONSTANTS.material.length > 0) 
                                        ? window.GENERATION_UNIT_CONSTANTS.material.map(mat => `<option value="${mat.id}">${mat.label}</option>`).join('')
                                        : ''
                                    }
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">استخدامه ${i}</label>
                                <select name="fuel_tanks[${i-1}][usage_id]" class="form-select">
                                    <option value="">اختر الاستخدام</option>
                                    ${(window.GENERATION_UNIT_CONSTANTS.usage && window.GENERATION_UNIT_CONSTANTS.usage.length > 0) 
                                        ? window.GENERATION_UNIT_CONSTANTS.usage.map(use => `<option value="${use.id}">${use.label}</option>`).join('')
                                        : ''
                                    }
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">طريقة القياس ${i}</label>
                                <select name="fuel_tanks[${i-1}][measurement_method_id]" class="form-select">
                                    <option value="">اختر الطريقة</option>
                                    ${(window.GENERATION_UNIT_CONSTANTS.measurement_method && window.GENERATION_UNIT_CONSTANTS.measurement_method.length > 0) 
                                        ? window.GENERATION_UNIT_CONSTANTS.measurement_method.map(method => `<option value="${method.id}">${method.label}</option>`).join('')
                                        : ''
                                    }
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            fuelTanksContainer.insertAdjacentHTML('beforeend', tankHtml);
        }
    }

    // تهيئة أولية إذا كان هناك old data
    @if(old('external_fuel_tank') == '1' && old('fuel_tanks_count'))
        renderFuelTanks({{ old('fuel_tanks_count') }});
    @endif

})();
</script>
@endpush

