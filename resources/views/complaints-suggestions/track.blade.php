<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>متابعة الطلب - راصد</title>
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

        .content {
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

        .search-form {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
        }

        .form-group {
            display: flex;
            gap: 10px;
        }

        .form-input {
            flex: 1;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
        }

        .form-input:focus {
            outline: none;
            border-color: #19228f;
        }

        .search-btn {
            padding: 12px 24px;
            background: #19228f;
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
        }

        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(25, 34, 143, 0.4);
        }

        .request-card {
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
        }

        .request-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f1f5f9;
        }

        .request-title {
            font-size: 20px;
            font-weight: 700;
            color: #1e293b;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-in_progress {
            background: #dbeafe;
            color: #19228f;
        }

        .status-resolved {
            background: #d1fae5;
            color: #065f46;
        }

        .status-rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        .info-row {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .info-item {
            flex: 1;
            min-width: 200px;
        }

        .info-label {
            font-size: 12px;
            color: #64748b;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 14px;
            color: #1e293b;
            font-weight: 600;
        }

        .tracking-code {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 20px;
        }

        .tracking-code-label {
            font-size: 12px;
            color: #64748b;
            margin-bottom: 5px;
        }

        .tracking-code-value {
            font-size: 24px;
            font-weight: 800;
            color: #19228f;
            letter-spacing: 2px;
        }

        .message-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .message-label {
            font-size: 12px;
            color: #64748b;
            margin-bottom: 8px;
        }

        .message-content {
            font-size: 14px;
            color: #1e293b;
            line-height: 1.6;
        }

        .response-box {
            background: #eff6ff;
            border-right: 4px solid #19228f;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }

        .response-label {
            font-size: 14px;
            font-weight: 700;
            color: #19228f;
            margin-bottom: 10px;
        }

        .section-title {
            font-size: 16px;
            font-weight: 700;
            color: #1e293b;
            margin: 25px 0 15px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #e2e8f0;
        }

        .section-title:first-of-type {
            margin-top: 20px;
        }

        .response-content {
            font-size: 14px;
            color: #1e293b;
            line-height: 1.6;
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

        .no-result {
            text-align: center;
            padding: 60px 20px;
            color: #64748b;
        }

        @media (max-width: 768px) {
            .content {
                padding: 30px 20px;
            }

            .form-group {
                flex-direction: column;
            }

            .request-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>متابعة الطلب</h1>
            <p>تابع حالة شكواك أو مقترحك</p>
        </div>

        <div class="content">
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

            @if(session('error'))
                <div class="alert alert-error" id="errorAlert">
                    {{ session('error') }}
                </div>
            @endif

            <div class="search-form">
                <form method="POST" action="{{ route('complaints-suggestions.search') }}">
                    @csrf
                    <div class="form-group">
                        <input type="text" name="code" id="trackingCodeInput" class="form-input" placeholder="أدخل رمز التتبع" value="{{ $code ?? '' }}" required>
                        <button type="submit" class="search-btn">بحث</button>
                    </div>
                </form>
            </div>

            @if($complaintSuggestion)
                <div class="request-card" id="requestCard">
                    <div class="tracking-code">
                        <div class="tracking-code-label">رمز التتبع</div>
                        <div class="tracking-code-value">{{ $complaintSuggestion->tracking_code }}</div>
                    </div>

                    <div class="request-header">
                        <div class="request-title">بيانات الطلب</div>
                        <div class="status-badge status-{{ $complaintSuggestion->status }}">
                            {{ $complaintSuggestion->status_label }}
                        </div>
                    </div>

                    <!-- معلومات مقدم الطلب -->
                    <div class="section-title">معلومات مقدم الطلب</div>
                    <div class="info-row">
                        <div class="info-item">
                            <div class="info-label">الاسم</div>
                            <div class="info-value">{{ $complaintSuggestion->name }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">رقم الهاتف</div>
                            <div class="info-value">{{ $complaintSuggestion->phone }}</div>
                        </div>
                    </div>
                    @if($complaintSuggestion->email)
                    <div class="info-row">
                        <div class="info-item">
                            <div class="info-label">البريد الإلكتروني</div>
                            <div class="info-value">{{ $complaintSuggestion->email }}</div>
                        </div>
                    </div>
                    @endif

                    <!-- معلومات الطلب -->
                    <div class="section-title">معلومات الطلب</div>
                    <div class="info-row">
                        <div class="info-item">
                            <div class="info-label">النوع</div>
                            <div class="info-value">{{ $complaintSuggestion->type_label }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">المحافظة</div>
                            <div class="info-value">{{ $complaintSuggestion->getGovernorateLabel() ?? 'غير محدد' }}</div>
                        </div>
                    </div>
                    @if($complaintSuggestion->generator)
                    <div class="info-row">
                        <div class="info-item">
                            <div class="info-label">المولد</div>
                            <div class="info-value">{{ $complaintSuggestion->generator->name }}</div>
                        </div>
                    </div>
                    @endif
                    <div class="info-row">
                        <div class="info-item">
                            <div class="info-label">تاريخ الإرسال</div>
                            <div class="info-value">{{ $complaintSuggestion->created_at->format('Y-m-d H:i') }}</div>
                        </div>
                    </div>

                    <!-- الرسالة -->
                    <div class="message-box">
                        <div class="message-label">الرسالة</div>
                        <div class="message-content">{{ $complaintSuggestion->message }}</div>
                    </div>

                    <!-- الصورة المرفقة -->
                    @if($complaintSuggestion->image)
                    <div class="message-box">
                        <div class="message-label">الصورة المرفقة</div>
                        <div style="margin-top: 10px;">
                            <img src="{{ asset('storage/' . $complaintSuggestion->image) }}" alt="صورة مرفقة" style="max-width: 100%; border-radius: 8px; border: 2px solid #e2e8f0;">
                        </div>
                    </div>
                    @endif

                    <!-- رد الإدارة -->
                    @if($complaintSuggestion->response)
                        <div class="response-box">
                            <div class="response-label">رد الإدارة</div>
                            <div class="response-content">{{ $complaintSuggestion->response }}</div>
                            @if($complaintSuggestion->responded_at)
                                <div style="margin-top: 10px; font-size: 12px; color: #64748b;">
                                    بتاريخ: {{ $complaintSuggestion->responded_at->format('Y-m-d H:i') }}
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="message-box" style="background: #fef3c7; border-right: 4px solid #f59e0b;">
                            <div style="font-size: 14px; color: #92400e;">
                                ⏳ الطلب قيد المراجعة. سيتم الرد عليك قريباً.
                            </div>
                        </div>
                    @endif
                </div>
            @elseif($code)
                <div class="no-result" id="noResult">
                    <p>لم يتم العثور على طلب بهذا الرمز. يرجى التحقق من الرمز والمحاولة مرة أخرى.</p>
                </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchForm = document.querySelector('.search-form form');
            const requestCard = document.getElementById('requestCard');
            const noResult = document.getElementById('noResult');
            const codeInput = document.getElementById('trackingCodeInput');
            const errorAlert = document.getElementById('errorAlert');

            // حذف النتائج السابقة عند بدء بحث جديد
            if (searchForm) {
                searchForm.addEventListener('submit', function(e) {
                    const code = codeInput ? codeInput.value.trim() : '';
                    
                    if (!code) {
                        e.preventDefault();
                        return;
                    }

                    // إخفاء النتائج السابقة قبل إرسال النموذج
                    if (requestCard) {
                        requestCard.remove();
                    }
                    if (noResult) {
                        noResult.remove();
                    }
                    if (errorAlert) {
                        errorAlert.remove();
                    }
                });
            }

            // عند تحميل الصفحة بدون نتائج، تأكد من عدم وجود نتائج سابقة
            @if($code && !$complaintSuggestion)
                // إذا كان هناك code ولكن لا توجد نتائج، احذف requestCard إذا كان موجوداً
                if (requestCard) {
                    requestCard.remove();
                }
            @endif
        });
    </script>
</body>
</html>

