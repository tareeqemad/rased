@extends('layouts.admin')

@section('title', 'إكمال بيانات المشغل')

@php
    $breadcrumbTitle = 'إكمال بيانات المشغل';
@endphp

@section('content')
    <div class="container-fluid">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-warning border-bottom">
                <div class="d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle-fill me-2 fs-4"></i>
                    <div>
                        <h5 class="mb-0 fw-bold">يرجى إكمال بيانات المشغل</h5>
                        <small class="d-block mt-1">يجب إكمال جميع الحقول المطلوبة للاستمرار</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.operators.profile.update') }}" method="POST" id="profileForm">
                    @csrf
                    @method('PUT')

                    <!-- بيانات الوحدة -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-primary mb-3">
                            <i class="bi bi-info-circle me-2"></i>
                            بيانات وحدة التوليد
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">رقم الوحدة (رقم مرجعي فريد) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" name="unit_number" id="unit_number" class="form-control @error('unit_number') is-invalid @enderror" 
                                           value="{{ old('unit_number', $operator->unit_number) }}" required readonly>
                                    <button type="button" class="btn btn-outline-secondary" id="generateUnitNumberBtn" disabled>
                                        <i class="bi bi-arrow-clockwise me-1"></i>
                                        توليد تلقائي
                                    </button>
                                </div>
                                @error('unit_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    سيتم توليده تلقائياً بناءً على المحافظة المختارة
                                </small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">كود الوحدة (توليد تلقائي)</label>
                                <input type="text" name="unit_code" class="form-control @error('unit_code') is-invalid @enderror" 
                                       value="{{ old('unit_code', $operator->unit_code) }}" readonly>
                                @error('unit_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">سيتم توليده تلقائياً</small>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">اسم الوحدة (الاسم المتعارف عليه) <span class="text-danger">*</span></label>
                                <input type="text" name="unit_name" class="form-control @error('unit_name') is-invalid @enderror" 
                                       value="{{ old('unit_name', $operator->unit_name) }}" 
                                       placeholder="مثال: مولدات البابا" required>
                                @error('unit_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">الاسم المتعارف عليه بين الناس</small>
                            </div>
                        </div>
                    </div>

                    <!-- الملكية والتشغيل -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-primary mb-3">
                            <i class="bi bi-person-badge me-2"></i>
                            الملكية والتشغيل
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">اسم المالك <span class="text-danger">*</span></label>
                                <input type="text" name="owner_name" class="form-control @error('owner_name') is-invalid @enderror" 
                                       value="{{ old('owner_name', $operator->owner_name) }}" required>
                                @error('owner_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">رقم هوية المالك</label>
                                <input type="text" name="owner_id_number" class="form-control @error('owner_id_number') is-invalid @enderror" 
                                       value="{{ old('owner_id_number', $operator->owner_id_number) }}">
                                @error('owner_id_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">جهة التشغيل <span class="text-danger">*</span></label>
                                <select name="operation_entity" class="form-select @error('operation_entity') is-invalid @enderror" required>
                                    <option value="">اختر جهة التشغيل</option>
                                    <option value="same_owner" {{ old('operation_entity', $operator->operation_entity) === 'same_owner' ? 'selected' : '' }}>
                                        نفس المالك
                                    </option>
                                    <option value="other_party" {{ old('operation_entity', $operator->operation_entity) === 'other_party' ? 'selected' : '' }}>
                                        طرف آخر
                                    </option>
                                </select>
                                @error('operation_entity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">رقم هوية المشغل <span class="text-danger">*</span></label>
                                <input type="text" name="operator_id_number" class="form-control @error('operator_id_number') is-invalid @enderror" 
                                       value="{{ old('operator_id_number', $operator->operator_id_number) }}" required>
                                @error('operator_id_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">رقم الموبايل</label>
                                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                                       value="{{ old('phone', $operator->phone) }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">رقم الموبايل البديل</label>
                                <input type="text" name="phone_alt" class="form-control @error('phone_alt') is-invalid @enderror" 
                                       value="{{ old('phone_alt', $operator->phone_alt) }}">
                                @error('phone_alt')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">البريد الإلكتروني</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                       value="{{ old('email', $operator->email) }}">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- الموقع -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-primary mb-3">
                            <i class="bi bi-geo-alt me-2"></i>
                            الموقع
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">المحافظة <span class="text-danger">*</span></label>
                                <select name="governorate" id="governorate" class="form-select @error('governorate') is-invalid @enderror" required>
                                    <option value="">اختر المحافظة</option>
                                    @foreach(App\Governorate::all() as $gov)
                                        <option value="{{ $gov->value }}" 
                                                {{ old('governorate', $operator->governorate?->value) == $gov->value ? 'selected' : '' }}
                                                data-code="{{ $gov->code() }}">
                                            {{ $gov->label() }} ({{ $gov->code() }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('governorate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @if($operator->governorate)
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle me-1"></i>
                                        الترميز: <strong>{{ $operator->getGovernorateCode() }}</strong>
                                    </small>
                                @endif
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">المدينة <span class="text-danger">*</span></label>
                                <input type="text" name="city" class="form-control @error('city') is-invalid @enderror" 
                                       value="{{ old('city', $operator->city) }}" required>
                                @error('city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">العنوان التفصيلي <span class="text-danger">*</span></label>
                                <input type="text" name="detailed_address" class="form-control @error('detailed_address') is-invalid @enderror" 
                                       value="{{ old('detailed_address', $operator->detailed_address) }}" required>
                                @error('detailed_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">تحديد الموقع على الخريطة <span class="text-danger">*</span></label>
                                <div id="map" style="height: 400px; width: 100%; border-radius: 8px; border: 1px solid #dee2e6;"></div>
                                <small class="text-muted d-block mt-2">
                                    <i class="bi bi-info-circle me-1"></i>
                                    انقر على الخريطة لتحديد موقع المشغل
                                </small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">خط العرض (Latitude) <span class="text-danger">*</span></label>
                                <input type="number" step="0.00000001" name="latitude" id="latitude" class="form-control @error('latitude') is-invalid @enderror" 
                                       value="{{ old('latitude', $operator->latitude) }}" required readonly>
                                @error('latitude')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">خط الطول (Longitude) <span class="text-danger">*</span></label>
                                <input type="number" step="0.00000001" name="longitude" id="longitude" class="form-control @error('longitude') is-invalid @enderror" 
                                       value="{{ old('longitude', $operator->longitude) }}" required readonly>
                                @error('longitude')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- القدرة والقدرات الفنية -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-primary mb-3">
                            <i class="bi bi-lightning-charge me-2"></i>
                            القدرة والقدرات الفنية
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">إجمالي قدرة الوحدة (KVA)</label>
                                <input type="number" step="0.01" name="total_capacity" class="form-control @error('total_capacity') is-invalid @enderror" 
                                       value="{{ old('total_capacity', $operator->total_capacity) }}">
                                @error('total_capacity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">عدد المولدات</label>
                                <input type="number" name="generators_count" class="form-control @error('generators_count') is-invalid @enderror" 
                                       value="{{ old('generators_count', $operator->generators_count) }}" min="0">
                                @error('generators_count')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">إمكانية مزامنة المولدات</label>
                                <select name="synchronization_available" class="form-select @error('synchronization_available') is-invalid @enderror">
                                    <option value="0" {{ old('synchronization_available', $operator->synchronization_available ? '1' : '0') == '0' ? 'selected' : '' }}>
                                        غير متوفرة
                                    </option>
                                    <option value="1" {{ old('synchronization_available', $operator->synchronization_available ? '1' : '0') == '1' ? 'selected' : '' }}>
                                        متوفرة
                                    </option>
                                </select>
                                @error('synchronization_available')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">قدرة المزامنة القصوى (KVA)</label>
                                <input type="number" step="0.01" name="max_synchronization_capacity" class="form-control @error('max_synchronization_capacity') is-invalid @enderror" 
                                       value="{{ old('max_synchronization_capacity', $operator->max_synchronization_capacity) }}" min="0">
                                @error('max_synchronization_capacity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- المستفيدون والبيئة -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-primary mb-3">
                            <i class="bi bi-people me-2"></i>
                            المستفيدون والبيئة
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">عدد المستفيدين</label>
                                <input type="number" name="beneficiaries_count" class="form-control @error('beneficiaries_count') is-invalid @enderror" 
                                       value="{{ old('beneficiaries_count', $operator->beneficiaries_count) }}" min="0">
                                @error('beneficiaries_count')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">حالة الامتثال البيئي</label>
                                <select name="environmental_compliance_status" class="form-select @error('environmental_compliance_status') is-invalid @enderror">
                                    <option value="">اختر الحالة</option>
                                    <option value="compliant" {{ old('environmental_compliance_status', $operator->environmental_compliance_status) === 'compliant' ? 'selected' : '' }}>
                                        ملتزم
                                    </option>
                                    <option value="under_monitoring" {{ old('environmental_compliance_status', $operator->environmental_compliance_status) === 'under_monitoring' ? 'selected' : '' }}>
                                        تحت المراقبة
                                    </option>
                                    <option value="under_evaluation" {{ old('environmental_compliance_status', $operator->environmental_compliance_status) === 'under_evaluation' ? 'selected' : '' }}>
                                        تحت التقييم
                                    </option>
                                    <option value="non_compliant" {{ old('environmental_compliance_status', $operator->environmental_compliance_status) === 'non_compliant' ? 'selected' : '' }}>
                                        غير ملتزم
                                    </option>
                                </select>
                                @error('environmental_compliance_status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">وصف المستفيدين</label>
                                <textarea name="beneficiaries_description" class="form-control @error('beneficiaries_description') is-invalid @enderror" 
                                          rows="3">{{ old('beneficiaries_description', $operator->beneficiaries_description) }}</textarea>
                                @error('beneficiaries_description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- الحالة العامة -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-primary mb-3">
                            <i class="bi bi-info-circle me-2"></i>
                            الحالة العامة
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">حالة الوحدة <span class="text-danger">*</span></label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="active" {{ old('status', $operator->status ?? 'active') === 'active' ? 'selected' : '' }}>
                                        فعالة
                                    </option>
                                    <option value="inactive" {{ old('status', $operator->status ?? 'active') === 'inactive' ? 'selected' : '' }}>
                                        غير فعالة
                                    </option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-lg me-2"></i>
                            حفظ البيانات
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map {
        z-index: 1;
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const unitNumberInput = document.getElementById('unit_number');
        const unitCodeInput = document.querySelector('input[name="unit_code"]');
        const governorateSelect = document.getElementById('governorate');
        const generateBtn = document.getElementById('generateUnitNumberBtn');
        
        // دالة لتوليد رقم الوحدة
        function generateUnitNumber() {
            const governorateValue = governorateSelect.value;
            
            if (!governorateValue) {
                alert('يرجى اختيار المحافظة أولاً');
                return;
            }
            
            generateBtn.disabled = true;
            generateBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>جاري التوليد...';
            
            fetch(`{{ url('/admin/operators/next-unit-number') }}/${governorateValue}`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    unitNumberInput.value = data.unit_number;
                    
                    // توليد كود الوحدة بناءً على رقم الوحدة
                    if (unitCodeInput) {
                        unitCodeInput.value = data.unit_number;
                    }
                } else {
                    alert(data.message || 'حدث خطأ أثناء توليد رقم الوحدة');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('حدث خطأ أثناء توليد رقم الوحدة');
            })
            .finally(() => {
                generateBtn.disabled = false;
                generateBtn.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i>توليد تلقائي';
            });
        }
        
        // عند تغيير المحافظة، توليد رقم الوحدة تلقائياً
        governorateSelect.addEventListener('change', function() {
            if (this.value) {
                // توليد رقم الوحدة تلقائياً عند تغيير المحافظة
                generateUnitNumber();
            } else {
                // إذا لم تكن هناك محافظة محددة، تعطيل الزر ومسح الحقل
                generateBtn.disabled = true;
                unitNumberInput.value = '';
                if (unitCodeInput) {
                    unitCodeInput.value = '';
                }
            }
        });
        
        // زر التوليد اليدوي
        generateBtn.addEventListener('click', generateUnitNumber);
        
        // إذا كانت المحافظة محددة مسبقاً، تفعيل الزر
        if (governorateSelect.value) {
            generateBtn.disabled = false;
        }

        // تهيئة الخريطة
        const latitudeInput = document.getElementById('latitude');
        const longitudeInput = document.getElementById('longitude');
        
        // إحداثيات مركز قطاع غزة
        const defaultLat = {{ old('latitude', $operator->latitude ?? 31.3547) }};
        const defaultLng = {{ old('longitude', $operator->longitude ?? 34.3088) }};
        
        // إنشاء الخريطة
        const map = L.map('map').setView([defaultLat, defaultLng], 11);
        
        // إضافة طبقة OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(map);
        
        // Marker للموقع الحالي
        let marker = null;
        
        // إذا كانت هناك إحداثيات موجودة، إضافة marker
        if (latitudeInput.value && longitudeInput.value) {
            marker = L.marker([parseFloat(latitudeInput.value), parseFloat(longitudeInput.value)], {
                draggable: true
            }).addTo(map);
            
            marker.bindPopup('موقع المشغل الحالي').openPopup();
        }
        
        // عند النقر على الخريطة
        map.on('click', function(e) {
            const lat = e.latlng.lat;
            const lng = e.latlng.lng;
            
            // تحديث الحقول
            latitudeInput.value = lat.toFixed(8);
            longitudeInput.value = lng.toFixed(8);
            
            // إزالة marker القديم إذا كان موجوداً
            if (marker) {
                map.removeLayer(marker);
            }
            
            // إضافة marker جديد
            marker = L.marker([lat, lng], {
                draggable: true
            }).addTo(map);
            
            marker.bindPopup('موقع المشغل المحدد').openPopup();
            
            // عند سحب marker
            marker.on('dragend', function(e) {
                const position = marker.getLatLng();
                latitudeInput.value = position.lat.toFixed(8);
                longitudeInput.value = position.lng.toFixed(8);
            });
        });
        
        // عند سحب marker الموجود
        if (marker) {
            marker.on('dragend', function(e) {
                const position = marker.getLatLng();
                latitudeInput.value = position.lat.toFixed(8);
                longitudeInput.value = position.lng.toFixed(8);
            });
        }
    });
</script>
@endpush

