@extends('layouts.front')

@php
    $siteName = $siteName ?? \App\Models\Setting::get('site_name', 'راصد');
@endphp
@section('title', 'المقترحات والشكاوى - ' . $siteName)

@push('styles')
<style>
    .complaints-page {
        padding: 3rem 0 5rem;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 50%, #f1f5f9 100%);
        min-height: calc(100vh - 70px);
    }

    .complaints-container {
        max-width: 1000px;
        margin: 0 auto;
        padding: 0 2rem;
    }

    .complaints-header {
        text-align: center;
        margin-bottom: 4rem;
        padding: 0;
        background: transparent;
        border: none;
        box-shadow: none;
        position: relative;
    }

    .complaints-header h1 {
        font-size: 3rem;
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
    
    .complaints-header h1::after {
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

    .complaints-header p {
        font-size: 1.2rem;
        color: #64748b;
        font-weight: 500;
        max-width: 600px;
        margin: 2.5rem auto 0;
        position: relative;
        padding-bottom: 2rem;
    }
    
    .complaints-header p::after {
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


    .options-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 2rem;
    }

    .option-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border: 1px solid rgba(59, 130, 246, 0.1);
        border-radius: 20px;
        padding: 3rem 2.5rem;
        text-align: center;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        text-decoration: none;
        color: inherit;
        display: block;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
        position: relative;
        overflow: hidden;
    }
    
    .option-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        transform: scaleX(0);
        transition: transform 0.4s ease;
    }
    
    .option-card:nth-child(1)::before {
        background: linear-gradient(90deg, #3b82f6 0%, #2563eb 50%, #1d4ed8 100%);
    }
    
    .option-card:nth-child(2)::before {
        background: linear-gradient(90deg, #10b981 0%, #059669 50%, #047857 100%);
    }

    .option-card:hover {
        transform: translateY(-10px) scale(1.02);
    }
    
    .option-card:nth-child(1):hover {
        box-shadow: 0 20px 40px rgba(59, 130, 246, 0.15);
        border-color: rgba(59, 130, 246, 0.3);
    }
    
    .option-card:nth-child(2):hover {
        box-shadow: 0 20px 40px rgba(16, 185, 129, 0.15);
        border-color: rgba(16, 185, 129, 0.3);
    }
    
    .option-card:hover::before {
        transform: scaleX(1);
    }

    .option-icon {
        width: 90px;
        height: 90px;
        margin: 0 auto 1.5rem;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        transition: all 0.4s ease;
        position: relative;
        z-index: 1;
    }
    
    .option-card:nth-child(1) .option-icon {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        box-shadow: 0 8px 20px rgba(59, 130, 246, 0.25);
    }
    
    .option-card:nth-child(1):hover .option-icon {
        transform: rotate(5deg) scale(1.1);
        box-shadow: 0 12px 30px rgba(59, 130, 246, 0.35);
    }
    
    .option-card:nth-child(2) .option-icon {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        box-shadow: 0 8px 20px rgba(16, 185, 129, 0.25);
    }
    
    .option-card:nth-child(2):hover .option-icon {
        transform: rotate(-5deg) scale(1.1);
        box-shadow: 0 12px 30px rgba(16, 185, 129, 0.35);
    }

    .option-icon svg {
        width: 45px;
        height: 45px;
    }

    .option-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.75rem;
        position: relative;
        z-index: 1;
    }

    .option-description {
        font-size: 1rem;
        color: #64748b;
        line-height: 1.7;
        position: relative;
        z-index: 1;
    }

    @media (max-width: 768px) {
        .complaints-page {
            padding: 2rem 0 4rem;
        }
        
        .complaints-header {
            margin-bottom: 2rem;
        }
        
        .complaints-header h1 {
            font-size: 2rem;
        }
        
        .complaints-header p {
            font-size: 1rem;
        }

        .options-grid {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        .option-card {
            padding: 2rem 1.5rem;
        }
        
        .back-link {
            margin-bottom: 2rem;
        }
    }
</style>
@endpush

@section('content')
<div class="complaints-page">
    <div class="complaints-container">
        <div class="complaints-header">
            <h1>المقترحات والشكاوى</h1>
            <p>نقدر ملاحظاتك ونسعى لتحسين خدماتنا</p>
        </div>

        <div>
            <div class="options-grid">
                <a href="{{ route('complaints-suggestions.create') }}" class="option-card">
                    <div class="option-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
</div>
@endsection

