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
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            max-width: 900px;
            width: 100%;
        }

        .header {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            padding: 40px;
            text-align: center;
            border-radius: 16px 16px 0 0;
        }

        .header h1 {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 16px;
            opacity: 0.95;
        }

        .content {
            background: white;
            border-radius: 0 0 16px 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
            padding: 50px 40px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #3b82f6;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 40px;
            transition: color 0.3s;
        }

        .back-link:hover {
            color: #2563eb;
        }

        .options-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
            margin-top: 30px;
        }

        .option-card {
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            padding: 40px 30px;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .option-card:hover {
            border-color: #3b82f6;
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.2);
        }

        .option-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 36px;
        }

        .option-card:hover .option-icon {
            transform: scale(1.1);
        }

        .option-title {
            font-size: 22px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 10px;
        }

        .option-description {
            font-size: 14px;
            color: #64748b;
            line-height: 1.6;
        }

        @media (max-width: 768px) {
            .options-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .content {
                padding: 40px 30px;
            }

            .header {
                padding: 30px 20px;
            }

            .header h1 {
                font-size: 24px;
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

        <div class="content">
            <a href="{{ route('public.home') }}" class="back-link">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                العودة للصفحة الرئيسية
            </a>

            <div class="options-grid">
                <a href="{{ route('complaints-suggestions.create') }}" class="option-card">
                    <div class="option-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 40px; height: 40px;">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                            <line x1="9" y1="10" x2="15" y2="10"></line>
                            <line x1="12" y1="7" x2="12" y2="13"></line>
                        </svg>
                    </div>
                    <div class="option-title">تقديم شكوى أو مقترح</div>
                    <div class="option-description">قدم شكواك أو اقتراحك وسنقوم بمراجعته والرد عليك في أقرب وقت</div>
                </a>

                <a href="{{ route('complaints-suggestions.track') }}" class="option-card">
                    <div class="option-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 40px; height: 40px;">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                    </div>
                    <div class="option-title">متابعة طلب سابق</div>
                    <div class="option-description">تابع حالة شكواك أو مقترحك السابق باستخدام رمز التتبع</div>
                </a>
            </div>
        </div>
    </div>
</body>
</html>

