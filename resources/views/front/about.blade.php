@extends('layouts.front')

@php
    $siteName = $siteName ?? \App\Models\Setting::get('site_name', 'راصد');
@endphp
@section('title', 'من نحن - ' . $siteName)
@section('description', 'تعرف على منصة راصد ورسالتنا وأهدافنا')

@push('styles')
<style>
    .about-page {
        padding: 3rem 0;
    }

    .about-header {
        text-align: center;
        margin-bottom: 5rem;
        padding: 0;
        background: transparent;
        border: none;
        box-shadow: none;
        font-family: 'Tajawal', sans-serif;
        position: relative;
    }

    .about-header h1 {
        font-size: 3.5rem;
        font-weight: 900;
        margin-bottom: 1.5rem;
        color: #1e293b;
        position: relative;
        display: inline-block;
    }
    
    .about-header h1::after {
        content: '';
        position: absolute;
        bottom: -15px;
        left: 50%;
        transform: translateX(-50%);
        width: 120px;
        height: 4px;
        background: linear-gradient(90deg, transparent 0%, #3b82f6 20%, #10b981 50%, #3b82f6 80%, transparent 100%);
        border-radius: 2px;
    }

    .about-header p {
        font-size: 1.5rem;
        color: #64748b;
        max-width: 800px;
        margin: 2.5rem auto 0;
        font-weight: 500;
        line-height: 1.8;
        position: relative;
        padding-bottom: 2rem;
    }
    
    .about-header p::after {
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

    .about-section {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border-radius: 24px;
        padding: 3.5rem;
        margin-bottom: 2.5rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(59, 130, 246, 0.1);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .about-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        transform: scaleX(0);
        transition: transform 0.4s ease;
    }
    
    .about-section:nth-of-type(1)::before {
        background: linear-gradient(90deg, #3b82f6 0%, #2563eb 50%, #1d4ed8 100%);
    }
    
    .about-section:nth-of-type(2)::before {
        background: linear-gradient(90deg, #10b981 0%, #059669 50%, #047857 100%);
    }
    
    .about-section:nth-of-type(3)::before {
        background: linear-gradient(90deg, #f59e0b 0%, #d97706 50%, #b45309 100%);
    }
    
    .about-section:nth-of-type(4)::before {
        background: linear-gradient(90deg, #8b5cf6 0%, #7c3aed 50%, #6d28d9 100%);
    }
    
    .about-section:hover {
        box-shadow: 0 15px 40px rgba(59, 130, 246, 0.15);
        transform: translateY(-3px);
    }
    
    .about-section:nth-of-type(1):hover {
        box-shadow: 0 15px 40px rgba(59, 130, 246, 0.15);
        border-color: rgba(59, 130, 246, 0.3);
    }
    
    .about-section:nth-of-type(2):hover {
        box-shadow: 0 15px 40px rgba(16, 185, 129, 0.15);
        border-color: rgba(16, 185, 129, 0.3);
    }
    
    .about-section:nth-of-type(3):hover {
        box-shadow: 0 15px 40px rgba(245, 158, 11, 0.15);
        border-color: rgba(245, 158, 11, 0.3);
    }
    
    .about-section:nth-of-type(4):hover {
        box-shadow: 0 15px 40px rgba(139, 92, 246, 0.15);
        border-color: rgba(139, 92, 246, 0.3);
    }
    
    .about-section:hover::before {
        transform: scaleX(1);
    }

    .about-section h2 {
        font-size: 2rem;
        font-weight: 800;
        margin-bottom: 1.5rem;
        color: var(--text-dark);
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .about-section-icon {
        width: 60px;
        height: 60px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: all 0.3s ease;
    }
    
    .about-section:nth-of-type(1) .about-section-icon {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        box-shadow: 0 8px 20px rgba(59, 130, 246, 0.25);
    }
    
    .about-section:nth-of-type(1):hover .about-section-icon {
        transform: rotate(5deg) scale(1.1);
        box-shadow: 0 12px 30px rgba(59, 130, 246, 0.35);
    }
    
    .about-section:nth-of-type(2) .about-section-icon {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        box-shadow: 0 8px 20px rgba(16, 185, 129, 0.25);
    }
    
    .about-section:nth-of-type(2):hover .about-section-icon {
        transform: rotate(-5deg) scale(1.1);
        box-shadow: 0 12px 30px rgba(16, 185, 129, 0.35);
    }
    
    .about-section:nth-of-type(3) .about-section-icon {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        box-shadow: 0 8px 20px rgba(245, 158, 11, 0.25);
    }
    
    .about-section:nth-of-type(3):hover .about-section-icon {
        transform: rotate(5deg) scale(1.1);
        box-shadow: 0 12px 30px rgba(245, 158, 11, 0.35);
    }
    
    .about-section:nth-of-type(4) .about-section-icon {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        box-shadow: 0 8px 20px rgba(139, 92, 246, 0.3);
    }
    
    .about-section:nth-of-type(4):hover .about-section-icon {
        transform: rotate(-5deg) scale(1.1);
        box-shadow: 0 12px 30px rgba(139, 92, 246, 0.4);
    }
    
    .about-section-icon svg {
        width: 28px;
        height: 28px;
        color: white;
    }

    .about-section p {
        font-size: 1.1rem;
        line-height: 2;
        color: var(--text-gray);
        margin-bottom: 1.5rem;
    }

    .features-list {
        list-style: none;
        padding: 0;
        margin: 1.5rem 0;
    }

    .features-list li {
        padding: 1rem 0;
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        border-bottom: 1px solid var(--border-color);
    }

    .features-list li:last-child {
        border-bottom: none;
    }

    .feature-icon {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin-top: 3px;
        transition: all 0.3s ease;
    }
    
    .features-list li:nth-child(1) .feature-icon {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }
    
    .features-list li:nth-child(2) .feature-icon {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }
    
    .features-list li:nth-child(3) .feature-icon {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
    }
    
    .features-list li:nth-child(4) .feature-icon {
        background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        box-shadow: 0 4px 12px rgba(6, 182, 212, 0.3);
    }
    
    .features-list li:hover .feature-icon {
        transform: scale(1.15);
    }
    
    .feature-icon svg {
        width: 14px;
        height: 14px;
        color: white;
    }

    .feature-text {
        flex: 1;
        font-size: 1.05rem;
        color: var(--text-dark);
        line-height: 1.8;
    }

    .goals-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 2rem;
        margin-top: 2rem;
    }

    .goal-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border-radius: 20px;
        padding: 2.5rem 2rem;
        text-align: center;
        border: 1px solid rgba(59, 130, 246, 0.1);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
        position: relative;
        overflow: hidden;
    }
    
    .goal-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        transform: scaleX(0);
        transition: transform 0.4s ease;
    }
    
    .goal-card:nth-child(1)::before {
        background: linear-gradient(90deg, #3b82f6 0%, #2563eb 50%, #1d4ed8 100%);
    }
    
    .goal-card:nth-child(2)::before {
        background: linear-gradient(90deg, #10b981 0%, #059669 50%, #047857 100%);
    }
    
    .goal-card:nth-child(3)::before {
        background: linear-gradient(90deg, #f59e0b 0%, #d97706 50%, #b45309 100%);
    }
    
    .goal-card:nth-child(4)::before {
        background: linear-gradient(90deg, #8b5cf6 0%, #7c3aed 50%, #6d28d9 100%);
    }

    .goal-card:hover {
        transform: translateY(-10px) scale(1.02);
    }
    
    .goal-card:nth-child(1):hover {
        box-shadow: 0 20px 40px rgba(59, 130, 246, 0.15);
        border-color: rgba(59, 130, 246, 0.3);
    }
    
    .goal-card:nth-child(2):hover {
        box-shadow: 0 20px 40px rgba(16, 185, 129, 0.15);
        border-color: rgba(16, 185, 129, 0.3);
    }
    
    .goal-card:nth-child(3):hover {
        box-shadow: 0 20px 40px rgba(245, 158, 11, 0.15);
        border-color: rgba(245, 158, 11, 0.3);
    }
    
    .goal-card:nth-child(4):hover {
        box-shadow: 0 20px 40px rgba(139, 92, 246, 0.15);
        border-color: rgba(139, 92, 246, 0.3);
    }
    
    .goal-card:hover::before {
        transform: scaleX(1);
    }

    .goal-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 1.5rem;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.4s ease;
    }
    
    .goal-card:nth-child(1) .goal-icon {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        box-shadow: 0 8px 20px rgba(59, 130, 246, 0.25);
    }
    
    .goal-card:nth-child(1):hover .goal-icon {
        transform: rotate(5deg) scale(1.15);
        box-shadow: 0 12px 30px rgba(59, 130, 246, 0.35);
    }
    
    .goal-card:nth-child(2) .goal-icon {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        box-shadow: 0 8px 20px rgba(16, 185, 129, 0.25);
    }
    
    .goal-card:nth-child(2):hover .goal-icon {
        transform: rotate(-5deg) scale(1.15);
        box-shadow: 0 12px 30px rgba(16, 185, 129, 0.35);
    }
    
    .goal-card:nth-child(3) .goal-icon {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        box-shadow: 0 8px 20px rgba(245, 158, 11, 0.25);
    }
    
    .goal-card:nth-child(3):hover .goal-icon {
        transform: rotate(5deg) scale(1.15);
        box-shadow: 0 12px 30px rgba(245, 158, 11, 0.35);
    }
    
    .goal-card:nth-child(4) .goal-icon {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        box-shadow: 0 8px 20px rgba(139, 92, 246, 0.25);
    }
    
    .goal-card:nth-child(4):hover .goal-icon {
        transform: rotate(-5deg) scale(1.15);
        box-shadow: 0 12px 30px rgba(139, 92, 246, 0.35);
    }
    
    .goal-icon svg {
        width: 36px;
        height: 36px;
        color: white;
    }

    .goal-title {
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: 1rem;
        color: var(--text-dark);
    }

    .goal-text {
        color: var(--text-gray);
        line-height: 1.8;
    }

    @media (max-width: 768px) {
        .about-header h1 {
            font-size: 2rem;
        }

        .about-header p {
            font-size: 1.2rem;
        }

        .goals-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="about-page">
    <div class="container">
        <div class="about-header">
            <h1>من نحن</h1>
            <p>منصة رقمية متكاملة لتنظيم وإدارة سوق الطاقة في محافظات غزة</p>
        </div>

        <!-- About Section -->
        <div class="about-section animate-on-scroll">
            <h2>
                <div class="about-section-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                </div>
                من نحن
            </h2>
            <p>
                {{ $siteName ?? 'راصد' }} هي منصة رقمية متطورة تهدف إلى تنظيم وإدارة سوق الطاقة في محافظات غزة بشكل احترافي وشامل. 
                نقدم خدمات متكاملة تربط بين المواطنين والمشغلين لتسهيل الوصول إلى أفضل الخدمات.
            </p>
            <p>
                نسعى جاهدين لتوفير بيئة رقمية متقدمة تسهل عملية إدارة وتنظيم سوق الطاقة، مع ضمان الشفافية 
                والموثوقية في جميع البيانات المقدمة.
            </p>
        </div>

        <!-- Mission Section -->
        <div class="about-section animate-on-scroll">
            <h2>
                <div class="about-section-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                        <line x1="12" y1="22.08" x2="12" y2="12"></line>
                    </svg>
                </div>
                رسالتنا
            </h2>
            <p>
                رسالتنا هي توفير منصة رقمية شاملة وموثوقة تسهل التواصل بين المواطنين والمشغلين، 
                وتعزز الشفافية والكفاءة في إدارة سوق الطاقة.
            </p>
            <ul class="features-list">
                <li>
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <div class="feature-text">تسهيل الوصول إلى المعلومات والخدمات</div>
                </li>
                <li>
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <div class="feature-text">ضمان دقة وموثوقية البيانات</div>
                </li>
                <li>
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <div class="feature-text">تعزيز التواصل والشفافية</div>
                </li>
                <li>
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <div class="feature-text">تطوير وتحسين الخدمات باستمرار</div>
                </li>
            </ul>
        </div>

        <!-- Goals Section -->
        <div class="about-section animate-on-scroll">
            <h2>
                <div class="about-section-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </div>
                أهدافنا
            </h2>
            <div class="goals-grid">
                <div class="goal-card">
                    <div class="goal-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                    </div>
                    <h3 class="goal-title">الشمولية</h3>
                    <p class="goal-text">تغطية جميع محافظات غزة والمشغلين النشطين</p>
                </div>

                <div class="goal-card">
                    <div class="goal-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        </svg>
                    </div>
                    <h3 class="goal-title">الموثوقية</h3>
                    <p class="goal-text">ضمان دقة وموثوقية جميع البيانات المقدمة</p>
                </div>

                <div class="goal-card">
                    <div class="goal-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                            <line x1="8" y1="21" x2="16" y2="21"></line>
                            <line x1="12" y1="17" x2="12" y2="21"></line>
                        </svg>
                    </div>
                    <h3 class="goal-title">سهولة الاستخدام</h3>
                    <p class="goal-text">واجهة بسيطة وسهلة الاستخدام لجميع المستخدمين</p>
                </div>

                <div class="goal-card">
                    <div class="goal-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        </svg>
                    </div>
                    <h3 class="goal-title">التواصل</h3>
                    <p class="goal-text">تسهيل التواصل بين المواطنين والمشغلين</p>
                </div>
            </div>
        </div>

        <!-- Services Section -->
        <div class="about-section animate-on-scroll">
            <h2>
                <div class="about-section-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                </div>
                خدماتنا
            </h2>
            <p>نوفر مجموعة واسعة من الخدمات الرقمية التي تسهل إدارة وتنظيم سوق الطاقة:</p>
            <ul class="features-list">
                <li>
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <div class="feature-text"><strong>خريطة تفاعلية:</strong> استكشف مواقع المشغلين على خريطة تفاعلية سهلة الاستخدام</div>
                </li>
                <li>
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <div class="feature-text"><strong>إحصائيات شاملة:</strong> احصل على إحصائيات مفصلة عن المشغلين والمولدات</div>
                </li>
                <li>
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <div class="feature-text"><strong>الشكاوي والمقترحات:</strong> أرسل شكاويك ومقترحاتك بسهولة واطلع على متابعة شكاواك</div>
                </li>
                <li>
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <div class="feature-text"><strong>معلومات الاتصال:</strong> احصل على معلومات الاتصال الكاملة لكل مشغل</div>
                </li>
            </ul>
        </div>

        <!-- CTA -->
        <div class="text-center" style="margin-top: 3rem;">
            <a href="{{ route('front.map') }}" class="btn btn-primary btn-lg" style="font-size: 1.1rem; padding: 1rem 2.5rem;">
                <svg style="width: 20px; height: 20px; display: inline-block; margin-left: 8px; vertical-align: middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                    <circle cx="12" cy="10" r="3"></circle>
                </svg>
                ابدأ الاستكشاف
            </a>
        </div>
    </div>
</div>
@endsection
