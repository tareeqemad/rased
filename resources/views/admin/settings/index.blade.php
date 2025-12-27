@extends('layouts.admin')

@section('title', 'إعدادات الموقع')

@php
    $breadcrumbTitle = 'إعدادات الموقع';
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/settings.css') }}">
@endpush

@section('content')
    @if(!auth()->user()->isSuperAdmin())
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i>
            غير مصرح لك بالوصول إلى هذه الصفحة
        </div>
    @else
    <div class="settings-page">
        <div class="row g-3">
            <div class="col-12">
                <div class="settings-card">
                    <div class="settings-card-header">
                        <h5 class="settings-card-title">
                            <i class="bi bi-gear me-2"></i>
                            إعدادات الموقع
                        </h5>
                        <div class="text-muted" style="margin-top: 0.25rem; font-size: 0.85rem;">
                            قم بتعديل إعدادات الموقع واللوجو والبيانات الأساسية
                        </div>
                    </div>

                    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" id="settingsForm">
                        @csrf
                        @method('PUT')

                        <div class="card-body">
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3">
                                    <i class="bi bi-image text-primary me-2"></i>
                                    إعدادات اللوجو والأيقونات
                                </h6>

                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">لوجو الموقع</label>
                                        <div>
                                            @php
                                                $currentLogo = \App\Models\Setting::get('site_logo', 'assets/admin/images/brand-logos/rased_logo.png');
                                            @endphp
                                            <div class="img-preview">
                                                <img src="{{ asset($currentLogo) }}" alt="Logo" id="logoPreview" style="max-height: 150px;" onerror="this.src='{{ asset('assets/admin/images/brand-logos/rased_logo.png') }}'">
                                            </div>
                                        </div>
                                        <input type="file" name="logo" id="logoInput" class="form-control" accept="image/png,image/jpeg,image/jpg,image/webp,image/svg+xml">
                                        <small class="text-muted d-block mt-2">الصيغ المدعومة: PNG, JPG, JPEG, WEBP, SVG (الحد الأقصى: 2MB)</small>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">أيقونة الموقع (Favicon)</label>
                                        <div>
                                            @php
                                                $currentFavicon = \App\Models\Setting::get('site_favicon', 'assets/admin/images/brand-logos/favicon.ico');
                                            @endphp
                                            <div class="img-preview">
                                                <img src="{{ asset($currentFavicon) }}" alt="Favicon" id="faviconPreview" style="max-height: 64px; max-width: 64px;" onerror="this.src='{{ asset('assets/admin/images/brand-logos/favicon.ico') }}'">
                                            </div>
                                        </div>
                                        <input type="file" name="favicon" id="faviconInput" class="form-control" accept="image/png,image/jpeg,image/x-icon,image/svg+xml">
                                        <small class="text-muted d-block mt-2">الصيغ المدعومة: PNG, ICO, SVG (الحد الأقصى: 512KB)</small>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="mb-4">
                                <h6 class="fw-bold mb-3">
                                    <i class="bi bi-info-circle text-info me-2"></i>
                                    المعلومات العامة
                                </h6>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">اسم الموقع</label>
                                        <input type="text" name="settings[site_name]" 
                                               class="form-control" 
                                               value="{{ \App\Models\Setting::get('site_name', 'راصد') }}"
                                               placeholder="اسم الموقع">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">وصف الموقع</label>
                                        <input type="text" name="settings[site_description]" 
                                               class="form-control" 
                                               value="{{ \App\Models\Setting::get('site_description', '') }}"
                                               placeholder="وصف مختصر للموقع">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">البريد الإلكتروني</label>
                                        <input type="email" name="settings[site_email]" 
                                               class="form-control" 
                                               value="{{ \App\Models\Setting::get('site_email', '') }}"
                                               placeholder="info@example.com">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">رقم الهاتف</label>
                                        <input type="text" name="settings[site_phone]" 
                                               class="form-control" 
                                               value="{{ \App\Models\Setting::get('site_phone', '') }}"
                                               placeholder="+970 59 123 4567">
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-semibold">العنوان</label>
                                        <textarea name="settings[site_address]" 
                                                  class="form-control" 
                                                  rows="2"
                                                  placeholder="عنوان الموقع الكامل">{{ \App\Models\Setting::get('site_address', '') }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="mb-4">
                                <h6 class="fw-bold mb-3">
                                    <i class="bi bi-share text-primary me-2"></i>
                                    وسائل التواصل الاجتماعي
                                </h6>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">فيسبوك</label>
                                        <input type="url" name="settings[social_facebook]" 
                                               class="form-control" 
                                               value="{{ \App\Models\Setting::get('social_facebook', '') }}"
                                               placeholder="https://facebook.com/...">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">تويتر</label>
                                        <input type="url" name="settings[social_twitter]" 
                                               class="form-control" 
                                               value="{{ \App\Models\Setting::get('social_twitter', '') }}"
                                               placeholder="https://twitter.com/...">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">إنستغرام</label>
                                        <input type="url" name="settings[social_instagram]" 
                                               class="form-control" 
                                               value="{{ \App\Models\Setting::get('social_instagram', '') }}"
                                               placeholder="https://instagram.com/...">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">لينكد إن</label>
                                        <input type="url" name="settings[social_linkedin]" 
                                               class="form-control" 
                                               value="{{ \App\Models\Setting::get('social_linkedin', '') }}"
                                               placeholder="https://linkedin.com/...">
                                    </div>
                                </div>
                            </div>

                            @if(isset($settings) && $settings->has('custom') && $settings['custom']->count() > 0)
                            <hr class="my-4">

                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="fw-bold mb-0">
                                        <i class="bi bi-sliders text-warning me-2"></i>
                                        إعدادات مخصصة
                                    </h6>
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addSettingModal">
                                        <i class="bi bi-plus-lg me-1"></i>
                                        إضافة إعداد جديد
                                    </button>
                                </div>

                                <div class="row g-3">
                                    @foreach($settings['custom'] as $setting)
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">
                                                {{ $setting->label ?? ucfirst(str_replace('_', ' ', $setting->key)) }}
                                                @if($setting->description)
                                                    <small class="text-muted d-block">{{ $setting->description }}</small>
                                                @endif
                                            </label>
                                            @if($setting->type === 'textarea')
                                                <textarea name="settings[{{ $setting->key }}]" 
                                                          class="form-control" 
                                                          rows="3">{{ $setting->value }}</textarea>
                                            @elseif($setting->type === 'number')
                                                <input type="number" name="settings[{{ $setting->key }}]" 
                                                       class="form-control" 
                                                       value="{{ $setting->value }}">
                                            @else
                                                <input type="text" name="settings[{{ $setting->key }}]" 
                                                       class="form-control" 
                                                       value="{{ $setting->value }}">
                                            @endif
                                            <div class="mt-2">
                                                <form action="{{ route('admin.settings.destroy', $setting) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا الإعداد؟');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-trash me-1"></i>
                                                        حذف
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <hr class="my-4">

                            <div class="d-flex justify-content-end align-items-center gap-2">
                                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle me-2"></i>
                                    إلغاء
                                </a>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="bi bi-check-lg me-2"></i>
                                    حفظ الإعدادات
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if(isset($settings) && $settings->has('custom'))
    <div class="modal fade" id="addSettingModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.settings.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">إضافة إعداد جديد</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">المفتاح (Key) <span class="text-danger">*</span></label>
                            <input type="text" name="key" class="form-control" required placeholder="custom_setting_key">
                            <small class="text-muted">استخدم أحرف إنجليزية وأرقام وشرطة سفلية فقط</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">القيمة</label>
                            <input type="text" name="value" class="form-control" placeholder="القيمة الافتراضية">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">النوع <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                <option value="text">نص</option>
                                <option value="textarea">نص طويل</option>
                                <option value="number">رقم</option>
                                <option value="email">بريد إلكتروني</option>
                                <option value="url">رابط</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">المجموعة <span class="text-danger">*</span></label>
                            <input type="text" name="group" class="form-control" required value="custom" placeholder="custom">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">التسمية</label>
                            <input type="text" name="label" class="form-control" placeholder="اسم الإعداد بالعربية">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">الوصف</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="وصف مختصر للإعداد"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">إضافة</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
    @endif
@endsection

@push('scripts')
<script src="{{ asset('assets/admin/libs/jquery/jquery.min.js') }}"></script>
<script>
    (function($) {
        $(document).ready(function() {
            const logoInput = $('#logoInput');
            const logoPreview = $('#logoPreview');
            const faviconInput = $('#faviconInput');
            const faviconPreview = $('#faviconPreview');
            const settingsForm = $('#settingsForm');
            const submitBtn = $('#submitBtn');

            if (logoInput.length && logoPreview.length) {
                logoInput.on('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            logoPreview.attr('src', e.target.result);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }

            if (faviconInput.length && faviconPreview.length) {
                faviconInput.on('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            faviconPreview.attr('src', e.target.result);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }

            if (settingsForm.length) {
                settingsForm.on('submit', function(e) {
                    if (logoInput.length && logoInput[0].files.length > 0) {
                        const logoFile = logoInput[0].files[0];
                        if (logoFile.size > 2 * 1024 * 1024) {
                            e.preventDefault();
                            alert('حجم ملف اللوجو كبير جداً. الحد الأقصى هو 2MB');
                            return false;
                        }
                    }

                    if (faviconInput.length && faviconInput[0].files.length > 0) {
                        const faviconFile = faviconInput[0].files[0];
                        if (faviconFile.size > 512 * 1024) {
                            e.preventDefault();
                            alert('حجم ملف الأيقونة كبير جداً. الحد الأقصى هو 512KB');
                            return false;
                        }
                    }

                    submitBtn.prop('disabled', true);
                    const originalText = submitBtn.html();
                    submitBtn.html('<span class="spinner-border spinner-border-sm me-2"></span>جاري الحفظ...');
                });
            }

            const phoneInput = $('input[name="settings[site_phone]"]');
            if (phoneInput.length) {
                phoneInput.on('input', function(e) {
                    let value = $(this).val().replace(/\D/g, '');
                    if (value.length > 0 && !value.startsWith('+')) {
                        if (value.startsWith('970')) {
                            value = '+' + value;
                        } else if (value.startsWith('0')) {
                            value = '+970' + value.substring(1);
                        }
                    }
                    $(this).val(value);
                });
            }

            const urlInputs = $('input[type="url"]');
            urlInputs.on('blur', function() {
                const value = $(this).val().trim();
                if (value && !value.match(/^https?:\/\//)) {
                    this.setCustomValidity('يجب أن يبدأ الرابط بـ http:// أو https://');
                } else {
                    this.setCustomValidity('');
                }
            });
        });
    })(jQuery);
</script>
@endpush
