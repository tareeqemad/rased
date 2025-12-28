<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>المقترحات والشكاوى - راصد</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Tajawal', sans-serif;
        }

        body {
            background: #f1f5f9;
            min-height: 100vh;
            padding: 40px 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }

        .header {
            background: #19228f;
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 14px;
            opacity: 0.9;
        }

        .form-container {
            padding: 40px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #19228f;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 30px;
            transition: color 0.3s;
        }

        .back-link:hover {
            color: #141a6b;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: #1e293b;
            font-weight: 700;
            font-size: 14px;
        }

        .form-label .required {
            color: #dc2626;
        }

        .type-selector {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 25px;
        }

        .type-option {
            position: relative;
        }

        .type-option input[type="radio"] {
            position: absolute;
            opacity: 0;
        }

        .type-option label {
            display: block;
            padding: 20px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: white;
        }

        .type-option input[type="radio"]:checked + label {
            border-color: #19228f;
            background: #eff6ff;
            color: #19228f;
            font-weight: 700;
        }

        .form-input,
        .form-textarea,
        .form-select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s;
            font-family: 'Tajawal', sans-serif;
            background: white;
        }

        .form-select {
            cursor: pointer;
        }

        .form-textarea {
            min-height: 120px;
            resize: vertical;
        }

        .form-input:focus,
        .form-textarea:focus,
        .form-select:focus {
            outline: none;
            border-color: #19228f;
            box-shadow: 0 0 0 4px rgba(25, 34, 143, 0.1);
        }

        .form-input[type="file"] {
            cursor: pointer;
        }

        .form-input[type="file"]::-webkit-file-upload-button {
            padding: 8px 16px;
            background: #19228f;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            margin-left: 10px;
        }

        .form-input[type="file"]::-webkit-file-upload-button:hover {
            background: #141a6b;
        }

        .submit-btn {
            width: 100%;
            padding: 14px;
            background: #19228f;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(25, 34, 143, 0.4);
        }

        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border-right: 4px solid #10b981;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border-right: 4px solid #dc2626;
        }

        .error-message {
            color: #dc2626;
            font-size: 12px;
            margin-top: 5px;
        }

        @media (max-width: 768px) {
            .form-container {
                padding: 30px 20px;
            }

            .type-selector {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>المقترحات والشكاوى</h1>
            <p>نقدر ملاحظاتك ونسعى لتحسين خدماتنا</p>
        </div>

        <div class="form-container">
            <a href="{{ route('front.home') }}" class="back-link">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                العودة للصفحة الرئيسية
            </a>

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-error">
                    <strong>⚠️ خطأ!</strong>
                    <ul style="margin-top: 8px; padding-right: 20px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('complaints-suggestions.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="type-selector">
                    <div class="type-option">
                        <input type="radio" id="complaint" name="type" value="complaint" {{ old('type') === 'complaint' ? 'checked' : '' }} required>
                        <label for="complaint">شكوى</label>
                    </div>
                    <div class="type-option">
                        <input type="radio" id="suggestion" name="type" value="suggestion" {{ old('type') === 'suggestion' ? 'checked' : '' }} required>
                        <label for="suggestion">مقترح</label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="name" class="form-label">
                        الاسم <span class="required">*</span>
                    </label>
                    <input type="text" id="name" name="name" class="form-input" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="phone" class="form-label">
                        رقم الهاتف <span class="required">*</span>
                    </label>
                    <input type="text" id="phone" name="phone" class="form-input" value="{{ old('phone') }}" required>
                    @error('phone')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">البريد الإلكتروني (اختياري)</label>
                    <input type="email" id="email" name="email" class="form-input" value="{{ old('email') }}">
                    @error('email')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="governorate" class="form-label">
                        المحافظة <span class="required">*</span>
                    </label>
                    <select id="governorate" name="governorate" class="form-select" required>
                        <option value="">اختر المحافظة</option>
                        @foreach($governorates as $gov)
                            <option value="{{ $gov->value }}" {{ old('governorate') == $gov->value ? 'selected' : '' }}>
                                {{ $gov->label }}
                            </option>
                        @endforeach
                    </select>
                    @error('governorate')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group" id="operator-group" style="display: none;">
                    <label for="operator_id" class="form-label">
                        المشغل <span class="required">*</span>
                    </label>
                    <select id="operator_id" name="operator_id" class="form-select">
                        <option value="">اختر المشغل</option>
                    </select>
                    @error('operator_id')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group" id="generator-group" style="display: none;">
                    <label for="generator_id" class="form-label">
                        المولد <span class="required">*</span>
                    </label>
                    <select id="generator_id" name="generator_id" class="form-select">
                        <option value="">اختر المولد</option>
                    </select>
                    @error('generator_id')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="message" class="form-label">
                        الرسالة <span class="required">*</span>
                    </label>
                    <textarea id="message" name="message" class="form-textarea" required>{{ old('message') }}</textarea>
                    @error('message')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="image" class="form-label">
                        إرفاق صورة (اختياري)
                    </label>
                    <input type="file" id="image" name="image" class="form-input" accept="image/*">
                    @error('image')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                    <div id="image-preview" style="margin-top: 10px; display: none;">
                        <img id="preview-img" src="" alt="معاينة الصورة" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #e2e8f0;">
                    </div>
                </div>

                <button type="submit" class="submit-btn">إرسال</button>
            </form>
        </div>
    </div>

    <!-- General Helpers JS -->
    <script src="{{ asset('assets/admin/js/general-helpers.js') }}"></script>

    <script>
        const governorateSelect = document.getElementById('governorate');
        const operatorGroup = document.getElementById('operator-group');
        const operatorSelect = document.getElementById('operator_id');
        const generatorGroup = document.getElementById('generator-group');
        const generatorSelect = document.getElementById('generator_id');

        // عند تغيير المحافظة
        governorateSelect.addEventListener('change', function() {
            const governorate = parseInt(this.value);

            // إخفاء حقول المشغل والمولد
            operatorGroup.style.display = 'none';
            operatorSelect.required = false;
            operatorSelect.innerHTML = '<option value="">اختر المشغل</option>';
            generatorGroup.style.display = 'none';
            generatorSelect.required = false;
            generatorSelect.innerHTML = '<option value="">اختر المولد</option>';

            if (governorate) {
                // إظهار حقل المشغل وملؤه بالمشغلين
                operatorGroup.style.display = 'block';
                operatorSelect.required = true;

                // مسح الخيارات السابقة
                operatorSelect.innerHTML = '<option value="">جاري التحميل...</option>';
                operatorSelect.disabled = true;

                // جلب المشغلين من السيرفر
                const operatorsUrl = `{{ route('complaints-suggestions.operators-by-governorate', ':governorate') }}`.replace(':governorate', governorate);

                fetch(operatorsUrl, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    operatorSelect.innerHTML = '<option value="">اختر المشغل</option>';
                    operatorSelect.disabled = false;

                    if (data.success && data.data && Array.isArray(data.data) && data.data.length > 0) {
                        data.data.forEach(operator => {
                            const option = document.createElement('option');
                            option.value = operator.id;
                            option.textContent = operator.name + (operator.city ? ` - ${operator.city}` : '');
                            @if(old('operator_id'))
                                if (operator.id == {{ old('operator_id') }}) {
                                    option.selected = true;
                                }
                            @endif
                            operatorSelect.appendChild(option);
                        });

                        // trigger change event إذا كان هناك مشغل محدد مسبقاً
                        @if(old('operator_id'))
                            const oldOperatorId = {{ old('operator_id') }};
                            if (oldOperatorId) {
                                operatorSelect.value = oldOperatorId;
                                operatorSelect.dispatchEvent(new Event('change'));
                            }
                        @endif
                    } else {
                        operatorSelect.innerHTML = '<option value="">لا توجد مشغلين في هذه المحافظة</option>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching operators:', error);
                    operatorSelect.innerHTML = '<option value="">حدث خطأ في تحميل المشغلين</option>';
                    operatorSelect.disabled = false;
                });
            }
        });

        // عند تغيير المشغل
        operatorSelect.addEventListener('change', function() {
            const operatorId = parseInt(this.value);

            // إخفاء حقل المولد
            generatorGroup.style.display = 'none';
            generatorSelect.required = false;
            generatorSelect.innerHTML = '<option value="">اختر المولد</option>';

            if (operatorId) {
                // إظهار حقل المولد
                generatorGroup.style.display = 'block';
                generatorSelect.required = true;

                // مسح الخيارات السابقة
                generatorSelect.innerHTML = '<option value="">جاري التحميل...</option>';

                // جلب المولدات من السيرفر حسب المشغل
                const url = `{{ route('complaints-suggestions.generators-by-operator') }}?operator_id=${encodeURIComponent(operatorId)}`;

                fetch(url, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    generatorSelect.innerHTML = '<option value="">اختر المولد</option>';

                    if (data && Array.isArray(data) && data.length > 0) {
                        data.forEach(generator => {
                            const option = document.createElement('option');
                            option.value = generator.id;
                            option.textContent = generator.name;
                            @if(old('generator_id'))
                                if (generator.id == {{ old('generator_id') }}) {
                                    option.selected = true;
                                }
                            @endif
                            generatorSelect.appendChild(option);
                        });
                    } else {
                        generatorSelect.innerHTML = '<option value="">لا توجد مولدات لهذا المشغل</option>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching generators:', error);
                    generatorSelect.innerHTML = '<option value="">حدث خطأ في تحميل المولدات</option>';
                });
            }
        });

        // إذا كانت المحافظة محددة مسبقاً (عند وجود أخطاء في النموذج)
        @if(old('governorate'))
            // انتظر تحميل GeneralHelpers ثم قم بملء المشغلين
            if (typeof GeneralHelpers !== 'undefined') {
                governorateSelect.dispatchEvent(new Event('change'));
            } else {
                // انتظر تحميل GeneralHelpers
                const checkGeneralHelpers = setInterval(function() {
                    if (typeof GeneralHelpers !== 'undefined') {
                        clearInterval(checkGeneralHelpers);
                        governorateSelect.dispatchEvent(new Event('change'));
                    }
                }, 100);

                // timeout بعد 5 ثوان
                setTimeout(function() {
                    clearInterval(checkGeneralHelpers);
                }, 5000);
            }
        @endif

        // معاينة الصورة
        const imageInput = document.getElementById('image');
        const imagePreview = document.getElementById('image-preview');
        const previewImg = document.getElementById('preview-img');

        if (imageInput && imagePreview && previewImg) {
            imageInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImg.src = e.target.result;
                        imagePreview.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                } else {
                    imagePreview.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>

