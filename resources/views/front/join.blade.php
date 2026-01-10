@extends('layouts.front')

@php
    $siteName = $siteName ?? \App\Models\Setting::get('site_name', 'راصد');
@endphp
@section('title', 'طلب الانضمام للمنصة - ' . $siteName)

@push('styles')
<style>
    .join-page {
        padding: 3rem 0 0;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 50%, #f8fafc 100%);
        min-height: calc(100vh - 70px);
        margin: 0;
        width: 100%;
        padding-bottom: 0;
    }

    .join-container {
        max-width: 900px;
        margin: 0 auto;
        padding: 0 2rem;
    }

    .join-header {
        text-align: center;
        margin-bottom: 3rem;
        padding: 0;
        background: transparent;
        border: none;
        box-shadow: none;
        position: relative;
    }

    .join-header h1 {
        font-size: 2.5rem;
        font-weight: 800;
        margin-bottom: 1rem;
        background: linear-gradient(135deg, #1e293b 0%, #3b82f6 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        position: relative;
        display: inline-block;
        padding-bottom: 1.5rem;
    }
    
    .join-header h1::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 120px;
        height: 4px;
        background: linear-gradient(90deg, transparent 0%, #3b82f6 20%, #10b981 50%, #3b82f6 80%, transparent 100%);
        border-radius: 2px;
    }

    .join-header p {
        font-size: 1.1rem;
        color: #64748b;
        font-weight: 500;
        position: relative;
        padding-bottom: 2rem;
        margin-top: 1rem;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
    }
    
    .join-header p::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 80%;
        max-width: 600px;
        height: 1px;
        background: linear-gradient(90deg, transparent 0%, rgba(59, 130, 246, 0.2) 20%, rgba(16, 185, 129, 0.3) 50%, rgba(59, 130, 246, 0.2) 80%, transparent 100%);
    }

    .form-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border-radius: 24px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(0, 0, 0, 0.05);
        padding: 3rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        color: #1e293b;
        font-weight: 600;
        font-size: 0.95rem;
    }

    .form-label .required {
        color: #ef4444;
    }

    .form-input {
        width: 100%;
        padding: 0.875rem 1rem;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        font-family: 'Tajawal', sans-serif;
        background: white;
        color: #1e293b;
    }

    .form-input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }

    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }
    }

    .form-hint {
        font-size: 0.85rem;
        color: #64748b;
        margin-top: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .form-hint svg {
        width: 16px;
        height: 16px;
        color: #3b82f6;
        flex-shrink: 0;
    }

    .checkbox-group {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 1.25rem;
        background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
        border-radius: 12px;
        border: 2px solid #e2e8f0;
        transition: all 0.3s ease;
    }

    .checkbox-group:hover {
        border-color: #3b82f6;
        background: linear-gradient(135deg, #eff6ff 0%, #f8fafc 100%);
    }

    .checkbox-group input[type="checkbox"] {
        width: 20px;
        height: 20px;
        margin-top: 2px;
        cursor: pointer;
        accent-color: #3b82f6;
    }

    .checkbox-group label {
        flex: 1;
        color: #1e293b;
        font-weight: 500;
        cursor: pointer;
        line-height: 1.6;
    }

    .checkbox-group .required {
        color: #ef4444;
    }

    .submit-btn {
        width: 100%;
        padding: 1rem;
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 1.1rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        font-family: 'Tajawal', sans-serif;
        box-shadow: 0 8px 20px rgba(59, 130, 246, 0.25);
        margin-top: 2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
    }

    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 30px rgba(59, 130, 246, 0.35);
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    }

    .submit-btn:active {
        transform: translateY(0);
    }

    .submit-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }

    .alert {
        padding: 1.25rem 1.5rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .alert-success {
        background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
        color: #065f46;
        border: 2px solid #10b981;
    }

    .alert-success svg {
        color: #10b981;
    }

    .alert-error {
        background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
        color: #991b1b;
        border: 2px solid #ef4444;
    }

    .alert-error svg {
        color: #ef4444;
    }

    .section-divider {
        margin: 2rem 0;
        height: 1px;
        background: linear-gradient(90deg, transparent 0%, rgba(0, 0, 0, 0.1) 50%, transparent 100%);
    }

    /* Toast Notification */
    .toast-notification {
        position: fixed;
        top: 90px;
        right: 2rem;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        padding: 1.25rem 1.5rem;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(16, 185, 129, 0.3);
        display: flex;
        align-items: center;
        gap: 1rem;
        z-index: 10000;
        min-width: 320px;
        max-width: 500px;
        animation: slideDown 0.4s ease-out, fadeOut 0.3s ease-in 4.7s forwards;
        transform: translateX(0);
    }

    @keyframes slideDown {
        from {
            transform: translateY(-100px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    @keyframes fadeOut {
        to {
            opacity: 0;
            transform: translateY(-20px);
        }
    }

    .toast-notification svg {
        width: 24px;
        height: 24px;
        flex-shrink: 0;
        color: white;
    }

    .toast-notification-content {
        flex: 1;
    }

    .toast-notification-title {
        font-weight: 700;
        font-size: 1rem;
        margin-bottom: 0.25rem;
    }

    .toast-notification-message {
        font-size: 0.9rem;
        opacity: 0.95;
    }

    @media (max-width: 768px) {
        .toast-notification {
            right: 1rem;
            left: 1rem;
            min-width: auto;
            max-width: none;
        }
    }

    @media (max-width: 768px) {
        .join-header h1 {
            font-size: 2rem;
        }

        .form-card {
            padding: 2rem 1.5rem;
        }
    }
</style>
@endpush

@section('content')
<div class="join-page">
    <div class="join-container">
        <div class="join-header">
            <h1>طلب الانضمام للمنصة</h1>
            <p>التقدم بطلب انضمام كـ مشغل وحدة توليد للمنصة الرقمية لإدارة سوق الطاقة</p>
        </div>

        <div class="form-card">

            @if($errors->any())
                <div class="alert alert-error">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 24px; height: 24px; flex-shrink: 0;">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <div style="flex: 1;">
                        <div style="font-weight: 700; margin-bottom: 0.75rem; font-size: 1rem;">حدث خطأ</div>
                        <ul style="margin: 0; padding: 0; list-style: none;">
                            @foreach($errors->all() as $error)
                                <li style="margin-bottom: 0.5rem; padding-right: 0.5rem; position: relative;">
                                    <span style="position: absolute; right: 0; top: 0.5rem; width: 6px; height: 6px; background: currentColor; border-radius: 50%;"></span>
                                    <span style="display: block; padding-right: 1rem;">{!! e($error) !!}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form action="{{ route('front.join.store') }}" method="POST" id="joinForm">
                @csrf

                <!-- بيانات المشغل الأساسية -->
                <h3 style="color: #1e293b; font-weight: 700; margin-bottom: 1.5rem; font-size: 1.3rem;">بيانات المشغل الأساسية</h3>

                <!-- الاسم بالعربية -->
                <div class="form-group">
                    <label for="name_ar" class="form-label">
                        الاسم رباعي بالعربية <span class="required">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="name_ar" 
                        name="name_ar" 
                        class="form-input" 
                        placeholder="أدخل الاسم رباعي بالعربية"
                        value="{{ e(old('name_ar', '')) }}"
                        required
                    >
                </div>

                <!-- الاسم بالإنجليزية -->
                <div class="form-group">
                    <label for="name_en" class="form-label">
                        الاسم رباعي بالإنجليزية <span class="required">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="name_en" 
                        name="name_en" 
                        class="form-input" 
                        placeholder="Enter full name in English"
                        value="{{ e(old('name_en', '')) }}"
                        required
                    >
                </div>

                <!-- رقم الهوية ورقم الموبايل -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="id_number" class="form-label">
                            رقم الهوية <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="id_number" 
                            name="id_number" 
                            class="form-input" 
                            placeholder="أدخل رقم الهوية"
                            value="{{ e(old('id_number', '')) }}"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="phone" class="form-label">
                            رقم الموبايل <span class="required">*</span>
                        </label>
                        <input 
                            type="tel" 
                            id="phone" 
                            name="phone" 
                            class="form-input" 
                            placeholder="0591234567 أو 0561234567"
                            value="{{ e(old('phone', '')) }}"
                            required
                        >
                    </div>
                </div>

                <!-- البريد الإلكتروني -->
                <div class="form-group">
                    <label for="email" class="form-label">
                        البريد الإلكتروني
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-input" 
                        placeholder="example@email.com"
                        value="{{ e(old('email', '')) }}"
                    >
                </div>

                <div class="section-divider"></div>

                <!-- إقرار بصحة البيانات -->
                <div class="form-group">
                    <div class="checkbox-group">
                        <input 
                            type="checkbox" 
                            id="data_accuracy" 
                            name="data_accuracy" 
                            value="1"
                            required
                        >
                        <label for="data_accuracy">
                            أقر بصحة جميع البيانات المقدمة في هذا الطلب <span class="required">*</span>
                        </label>
                    </div>
                </div>

                <button type="submit" class="submit-btn" id="submitBtn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px;">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    إرسال الطلب
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // عرض إشعار النجاح إذا كان موجوداً
        @if(session('success'))
            @php
                try {
                    $successMsg = session('success');
                    // Message is already cleaned by AppServiceProvider View Composer
                    // Just ensure it's safe for JSON encoding
                    if (!is_string($successMsg)) {
                        $successMsg = 'تم الإرسال بنجاح';
                    }
                    // Encode to JSON with UTF-8 support
                    $jsonMsg = json_encode($successMsg, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE | JSON_HEX_APOS | JSON_HEX_QUOT);
                    if ($jsonMsg === false || $jsonMsg === 'null') {
                        $jsonMsg = json_encode('تم الإرسال بنجاح', JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
                    }
                } catch (\Exception $e) {
                    $jsonMsg = json_encode('تم الإرسال بنجاح', JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
                }
            @endphp
            try {
                showToastNotification({!! $jsonMsg !!});
            } catch (e) {
                console.error('Error showing notification:', e);
            }
        @endif

        const form = document.getElementById('joinForm');
        const submitBtn = document.getElementById('submitBtn');
        const phoneInput = document.getElementById('phone');
        const idNumberInput = document.getElementById('id_number');

        // دالة لعرض إشعار منبثق
        function showToastNotification(message) {
            // Clean message to ensure valid UTF-8
            if (typeof message !== 'string') {
                message = String(message || '');
            }
            // Remove any invalid UTF-8 characters
            message = message.replace(/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/g, '');
            // Escape HTML to prevent XSS
            const messageText = document.createTextNode(message).textContent || message;
            
            // إزالة أي إشعار موجود مسبقاً
            const existingToast = document.querySelector('.toast-notification');
            if (existingToast) {
                existingToast.remove();
            }

            // إنشاء الإشعار
            const toast = document.createElement('div');
            toast.className = 'toast-notification';
            const titleElement = document.createElement('div');
            titleElement.className = 'toast-notification-title';
            titleElement.textContent = 'تم بنجاح!';
            const messageElement = document.createElement('div');
            messageElement.className = 'toast-notification-message';
            messageElement.textContent = messageText;
            
            const contentDiv = document.createElement('div');
            contentDiv.className = 'toast-notification-content';
            contentDiv.appendChild(titleElement);
            contentDiv.appendChild(messageElement);
            
            const svgElement = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            svgElement.setAttribute('viewBox', '0 0 24 24');
            svgElement.setAttribute('fill', 'none');
            svgElement.setAttribute('stroke', 'currentColor');
            svgElement.setAttribute('stroke-width', '2.5');
            const path1 = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            path1.setAttribute('d', 'M22 11.08V12a10 10 0 1 1-5.93-9.14');
            const path2 = document.createElementNS('http://www.w3.org/2000/svg', 'polyline');
            path2.setAttribute('points', '22 4 12 14.01 9 11.01');
            svgElement.appendChild(path1);
            svgElement.appendChild(path2);
            
            toast.appendChild(svgElement);
            toast.appendChild(contentDiv);

            // إضافة الإشعار للصفحة
            document.body.appendChild(toast);

            // إزالة الإشعار بعد 5 ثواني
            setTimeout(() => {
                toast.style.animation = 'fadeOut 0.3s ease-in forwards';
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }, 5000);
        }

        // تحقق من رقم الهوية (أرقام فقط)
        idNumberInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            e.target.value = value;
        });

        // تحقق من رقم الموبايل (يجب أن يبدأ بـ 059 أو 056)
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 0 && !value.startsWith('0')) {
                value = '0' + value;
            }
            // التحقق من أن الرقم يبدأ بـ 059 أو 056
            if (value.length >= 3) {
                const prefix = value.substring(0, 3);
                if (prefix !== '059' && prefix !== '056') {
                    // إذا لم يبدأ بـ 059 أو 056، نصححه
                    if (value.startsWith('05')) {
                        // إذا بدأ بـ 05، نتركه للمستخدم لإكماله
                        if (value.length > 2 && value[2] !== '9' && value[2] !== '6') {
                            // إذا لم يكن 9 أو 6، نحذف الرقم الخاطئ
                            value = value.substring(0, 2);
                        }
                    } else if (value.startsWith('0') && value.length > 1 && value[1] !== '5') {
                        // إذا لم يبدأ بـ 05، نحذف الرقم
                        value = '0';
                    }
                }
            }
            if (value.length > 10) {
                value = value.substring(0, 10);
            }
            e.target.value = value;
        });

        // منع الإرسال المزدوج
        form.addEventListener('submit', function(e) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <svg style="width: 20px; height: 20px;" class="animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" opacity="0.25"></circle>
                    <path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                جاري الإرسال...
            `;
        });
    });
</script>
@endpush

