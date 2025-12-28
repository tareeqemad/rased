@extends('layouts.front')

@section('title', 'من نحن - راصد')
@section('description', 'تعرف على منصة راصد ورسالتنا وأهدافنا')

@push('styles')
<style>
    .about-page {
        padding: 3rem 0;
    }

    .about-header {
        text-align: center;
        margin-bottom: 4rem;
        padding: 3rem 0;
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
        color: white;
        border-radius: 20px;
    }

    .about-header h1 {
        font-size: 3rem;
        font-weight: 900;
        margin-bottom: 1rem;
    }

    .about-header p {
        font-size: 1.5rem;
        opacity: 0.95;
        max-width: 800px;
        margin: 0 auto;
    }

    .about-section {
        background: white;
        border-radius: 20px;
        padding: 3rem;
        margin-bottom: 2rem;
        box-shadow: var(--shadow-md);
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
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
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
        width: 24px;
        height: 24px;
        background: var(--primary-color);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin-top: 3px;
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
        background: var(--bg-light);
        border-radius: 16px;
        padding: 2rem;
        text-align: center;
        border: 2px solid var(--border-color);
        transition: all 0.3s;
    }

    .goal-card:hover {
        border-color: var(--primary-color);
        transform: translateY(-5px);
        box-shadow: var(--shadow-md);
    }

    .goal-icon {
        width: 70px;
        height: 70px;
        margin: 0 auto 1.5rem;
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
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
                راصد هي منصة رقمية متطورة تهدف إلى تنظيم وإدارة سوق الطاقة في محافظات غزة بشكل احترافي وشامل. 
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

