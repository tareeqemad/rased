@extends('layouts.front')

@php
    $siteName = $siteName ?? \App\Models\Setting::get('site_name', 'راصد');
@endphp
@section('title', $siteName . ' - منصة رقمية لإدارة سوق الطاقة')
@section('description', 'منصة رقمية متكاملة لتنظيم وإدارة سوق الطاقة في محافظات غزة')

@push('meta')
    @php
        $siteName = $siteName ?? \App\Models\Setting::get('site_name', 'راصد');
    @endphp
    <meta name="keywords" content="{{ $siteName }}, منصة الطاقة, مولدات كهرباء, غزة, محافظات غزة, مشغلين, إدارة الطاقة">
    <meta name="author" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $siteName }} - منصة رقمية لإدارة سوق الطاقة">
    <meta property="og:description" content="منصة رقمية متكاملة لتنظيم وإدارة سوق الطاقة في محافظات غزة">
    <meta property="og:type" content="website">
    <link rel="canonical" href="{{ url('/') }}">
@endpush

@push('styles')
<style>
    /* Hero Section */
    .hero-section {
        background: #19228f;
        color: white;
        padding: 6rem 0 4rem;
        position: relative;
        overflow: hidden;
        margin-top: -1px; /* لإزالة أي فجوة بين الناف بار والـ hero */
    }

    .hero-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg width="100" height="100" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="100" height="100" patternUnits="userSpaceOnUse"><path d="M 100 0 L 0 0 0 100" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
        opacity: 0.3;
    }

    .hero-section::after {
        content: '';
        position: absolute;
        bottom: -50px;
        left: 0;
        right: 0;
        height: 200px;
        background: url('{{ asset("assets/front/images/generator-pattern.svg") }}');
        background-size: 300px 300px;
        background-repeat: repeat-x;
        background-position: center bottom;
        opacity: 0.15;
        z-index: 0;
    }

    .hero-content {
        position: relative;
        z-index: 1;
        text-align: center;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 2rem;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 3rem;
        align-items: center;
    }

    .hero-text {
        text-align: right;
    }

    .hero-image {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .hero-image-wrapper {
        position: relative;
        width: 100%;
        max-width: 500px;
        margin: 0 auto;
    }

    .hero-image-main {
        width: 100%;
        height: auto;
        filter: drop-shadow(0 20px 40px rgba(0, 0, 0, 0.3));
        animation: float 6s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% {
            transform: translateY(0px);
        }
        50% {
            transform: translateY(-20px);
        }
    }

    .hero-image-pattern {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 120%;
        height: 120%;
        background: url('{{ asset("assets/front/images/generator-pattern.svg") }}');
        background-size: 200px 200px;
        background-repeat: repeat;
        opacity: 0.2;
        z-index: -1;
        animation: rotate 20s linear infinite;
    }

    @keyframes rotate {
        from {
            transform: translate(-50%, -50%) rotate(0deg);
        }
        to {
            transform: translate(-50%, -50%) rotate(360deg);
        }
    }

    .hero-title {
        font-size: 3.5rem;
        font-weight: 900;
        margin-bottom: 1.5rem;
        line-height: 1.2;
        text-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    }

    .hero-subtitle {
        font-size: 1.5rem;
        margin-bottom: 2.5rem;
        opacity: 0.95;
        line-height: 1.6;
    }

    .hero-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
        flex-wrap: wrap;
    }

    .btn-hero {
        padding: 1rem 2.5rem;
        font-size: 1.1rem;
        font-weight: 700;
        border-radius: 12px;
        transition: all 0.3s;
    }

    .btn-hero-primary {
        background: white;
        color: var(--primary-color);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    }

    .btn-hero-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.3);
    }

    .btn-hero-secondary {
        background: rgba(255, 255, 255, 0.15);
        color: white;
        border: 2px solid rgba(255, 255, 255, 0.3);
        backdrop-filter: blur(10px);
    }

    .btn-hero-secondary:hover {
        background: rgba(255, 255, 255, 0.25);
        border-color: rgba(255, 255, 255, 0.5);
    }

    /* Statistics Section */
    .stats-section {
        padding: 4rem 0;
        background: white;
        margin-top: -3rem;
        position: relative;
        z-index: 2;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 2rem;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 2rem;
    }

    .stat-card {
        background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
        border-radius: 16px;
        padding: 2.5rem 2rem;
        text-align: center;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        border: 1px solid var(--border-color);
        transition: all 0.3s;
    }

    .stat-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
    }

    .stat-icon {
        width: 70px;
        height: 70px;
        margin: 0 auto 1.5rem;
        background: var(--primary-color);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 20px rgba(25, 34, 143, 0.3);
    }

    .stat-icon svg {
        width: 36px;
        height: 36px;
        color: white;
    }

    .stat-value {
        font-size: 3rem;
        font-weight: 900;
        color: var(--primary-color);
        margin-bottom: 0.5rem;
        line-height: 1;
    }

    .stat-label {
        font-size: 1.1rem;
        color: var(--text-gray);
        font-weight: 600;
    }

    /* Features Section */
    .features-section {
        padding: 5rem 0;
        background: var(--bg-light);
    }

    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2.5rem;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 2rem;
    }

    .feature-card {
        background: white;
        border-radius: 20px;
        padding: 3rem 2rem;
        text-align: center;
        box-shadow: var(--shadow-md);
        transition: all 0.3s;
        border: 1px solid var(--border-color);
    }

    .feature-card:hover {
        transform: translateY(-10px);
        box-shadow: var(--shadow-xl);
    }

    .feature-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 1.5rem;
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .feature-icon svg {
        width: 40px;
        height: 40px;
        color: var(--primary-color);
    }

    .feature-title {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 1rem;
        color: var(--text-dark);
    }

    .feature-text {
        color: var(--text-gray);
        line-height: 1.8;
        font-size: 1.05rem;
    }

    /* CTA Section */
    .cta-section {
        padding: 5rem 0;
        background: var(--primary-color);
        color: white;
        text-align: center;
    }

    .cta-title {
        font-size: 2.5rem;
        font-weight: 800;
        margin-bottom: 1rem;
    }

    .cta-text {
        font-size: 1.25rem;
        margin-bottom: 2.5rem;
        opacity: 0.95;
    }

    @media (max-width: 968px) {
        .hero-content {
            grid-template-columns: 1fr;
            gap: 2rem;
        }

        .hero-text {
            text-align: center;
        }

        .hero-image {
            order: -1;
        }

        .hero-image-wrapper {
            max-width: 300px;
        }
    }

    @media (max-width: 768px) {
        .hero-title {
            font-size: 2.5rem;
        }

        .hero-subtitle {
            font-size: 1.2rem;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .features-grid {
            grid-template-columns: 1fr;
        }

        .cta-title {
            font-size: 2rem;
        }

        .hero-image-wrapper {
            max-width: 250px;
        }
    }
</style>
@endpush

@section('content')
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <div class="hero-text">
                <h1 class="hero-title">منصة {{ $siteName ?? 'راصد' }}</h1>
                <p class="hero-subtitle">
                    منصة رقمية متكاملة لتنظيم وإدارة سوق الطاقة في محافظات غزة
                    <br>
                    نوفر لك المعلومات والأدوات اللازمة للوصول إلى أفضل المشغلين
                </p>
                <div class="hero-actions">
                    <a href="{{ route('front.map') }}" class="btn btn-hero btn-hero-primary">
                        <svg style="width: 20px; height: 20px; display: inline-block; margin-left: 8px; vertical-align: middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                        استكشف الخريطة
                    </a>
                    <a href="{{ route('front.stats') }}" class="btn btn-hero btn-hero-secondary">
                        <svg style="width: 20px; height: 20px; display: inline-block; margin-left: 8px; vertical-align: middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="20" x2="12" y2="10"></line>
                            <line x1="18" y1="20" x2="18" y2="4"></line>
                            <line x1="6" y1="20" x2="6" y2="16"></line>
                        </svg>
                        الإحصائيات
                    </a>
                </div>
            </div>
            <div class="hero-image">
                <div class="hero-image-wrapper">
                    <div class="hero-image-pattern"></div>
                    <svg class="hero-image-main" viewBox="0 0 400 400" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <!-- Generator Body -->
                        <rect x="80" y="120" width="240" height="180" rx="15" fill="rgba(255,255,255,0.95)" stroke="rgba(255,255,255,0.5)" stroke-width="2"/>
                        <rect x="100" y="140" width="200" height="140" rx="10" fill="rgba(25,34,143,0.1)"/>
                        
                        <!-- Control Panel -->
                        <rect x="120" y="160" width="160" height="100" rx="8" fill="rgba(25,34,143,0.2)" stroke="rgba(255,255,255,0.3)" stroke-width="1"/>
                        <circle cx="150" cy="190" r="8" fill="rgba(255,255,255,0.8)"/>
                        <circle cx="180" cy="190" r="8" fill="rgba(255,255,255,0.8)"/>
                        <circle cx="210" cy="190" r="8" fill="rgba(255,255,255,0.8)"/>
                        <rect x="140" y="210" width="80" height="30" rx="4" fill="rgba(255,255,255,0.6)"/>
                        
                        <!-- Engine -->
                        <rect x="140" y="240" width="120" height="20" rx="4" fill="rgba(25,34,143,0.3)"/>
                        <circle cx="160" cy="250" r="4" fill="rgba(255,255,255,0.7)"/>
                        <circle cx="180" cy="250" r="4" fill="rgba(255,255,255,0.7)"/>
                        <circle cx="200" cy="250" r="4" fill="rgba(255,255,255,0.7)"/>
                        <circle cx="220" cy="250" r="4" fill="rgba(255,255,255,0.7)"/>
                        <circle cx="240" cy="250" r="4" fill="rgba(255,255,255,0.7)"/>
                        
                        <!-- Fuel Tank -->
                        <ellipse cx="200" cy="340" rx="100" ry="30" fill="rgba(255,255,255,0.9)" stroke="rgba(255,255,255,0.5)" stroke-width="2"/>
                        <rect x="120" y="320" width="160" height="40" rx="20" fill="rgba(255,255,255,0.9)"/>
                        <rect x="130" y="330" width="140" height="20" rx="10" fill="rgba(25,34,143,0.2)"/>
                        
                        <!-- Exhaust Pipe -->
                        <rect x="300" y="180" width="40" height="8" rx="4" fill="rgba(255,255,255,0.8)"/>
                        <circle cx="340" cy="184" r="6" fill="rgba(255,255,255,0.6)"/>
                        
                        <!-- Power Lines -->
                        <path d="M 80 200 L 40 200 L 40 100 L 20 100" stroke="rgba(255,255,255,0.8)" stroke-width="3" fill="none" stroke-linecap="round"/>
                        <circle cx="20" cy="100" r="8" fill="rgba(255,255,255,0.9)"/>
                        <path d="M 320 200 L 360 200 L 360 100 L 380 100" stroke="rgba(255,255,255,0.8)" stroke-width="3" fill="none" stroke-linecap="round"/>
                        <circle cx="380" cy="100" r="8" fill="rgba(255,255,255,0.9)"/>
                        
                        <!-- Lightning Bolt (Power Symbol) -->
                        <path d="M 200 60 L 180 100 L 200 100 L 220 140 L 200 100 L 220 100 Z" fill="rgba(255,255,255,0.9)" opacity="0.9"/>
                    </svg>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="stats-section">
        <div class="stats-grid">
            <div class="stat-card animate-on-scroll">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <div class="stat-value">{{ number_format($stats['total_operators']) }}</div>
                <div class="stat-label">مشغل نشط</div>
            </div>

            <div class="stat-card animate-on-scroll">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                </div>
                <div class="stat-value">{{ number_format($stats['total_generators']) }}</div>
                <div class="stat-label">مولد مسجل</div>
            </div>

            <div class="stat-card animate-on-scroll">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"></path>
                    </svg>
                </div>
                <div class="stat-value">{{ number_format($stats['total_capacity'] / 1000, 1) }}K</div>
                <div class="stat-label">كيلو فولت أمبير (KVA)</div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <h2 class="section-title">مميزات المنصة</h2>
            <p class="section-subtitle">نوفر لك كل ما تحتاجه للوصول إلى أفضل الخدمات</p>
            
            <div class="features-grid">
                <div class="feature-card animate-on-scroll">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                    </div>
                    <h3 class="feature-title">خريطة تفاعلية</h3>
                    <p class="feature-text">
                        استكشف المشغلين على خريطة تفاعلية سهلة الاستخدام مع معلومات كاملة عن كل مشغل
                    </p>
                </div>

                <div class="feature-card animate-on-scroll">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="20" x2="12" y2="10"></line>
                            <line x1="18" y1="20" x2="18" y2="4"></line>
                            <line x1="6" y1="20" x2="6" y2="16"></line>
                        </svg>
                    </div>
                    <h3 class="feature-title">إحصائيات شاملة</h3>
                    <p class="feature-text">
                        احصل على إحصائيات مفصلة عن المشغلين والمولدات في جميع محافظات غزة
                    </p>
                </div>

                <div class="feature-card animate-on-scroll">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                            <line x1="9" y1="10" x2="15" y2="10"></line>
                            <line x1="12" y1="7" x2="12" y2="13"></line>
                        </svg>
                    </div>
                    <h3 class="feature-title">شكاوي ومقترحات</h3>
                    <p class="feature-text">
                        أرسل شكاويك ومقترحاتك بسهولة واطلع على حالة متابعة شكاواك
                    </p>
                </div>

                <div class="feature-card animate-on-scroll">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                        </svg>
                    </div>
                    <h3 class="feature-title">معلومات الاتصال</h3>
                    <p class="feature-text">
                        احصل على معلومات الاتصال الكاملة لكل مشغل بسهولة وسرعة
                    </p>
                </div>

                <div class="feature-card animate-on-scroll">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        </svg>
                    </div>
                    <h3 class="feature-title">بيانات موثوقة</h3>
                    <p class="feature-text">
                        جميع البيانات محدثة وموثوقة ومتابعة بشكل مستمر لضمان دقتها
                    </p>
                </div>

                <div class="feature-card animate-on-scroll">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                            <line x1="8" y1="21" x2="16" y2="21"></line>
                            <line x1="12" y1="17" x2="12" y2="21"></line>
                        </svg>
                    </div>
                    <h3 class="feature-title">سهل الاستخدام</h3>
                    <p class="feature-text">
                        واجهة بسيطة وسهلة الاستخدام تعمل على جميع الأجهزة والهواتف الذكية
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <h2 class="cta-title">ابدأ رحلتك الآن</h2>
            <p class="cta-text">
                استكشف خريطة المشغلين واحصل على أفضل الخدمات في محافظتك
            </p>
            <div class="hero-actions">
                <a href="{{ route('front.map') }}" class="btn btn-hero btn-hero-primary">
                    <svg style="width: 20px; height: 20px; display: inline-block; margin-left: 8px; vertical-align: middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                    ابدأ الاستكشاف
                </a>
                <a href="{{ route('complaints-suggestions.create') }}" class="btn btn-hero btn-hero-secondary">
                    <svg style="width: 20px; height: 20px; display: inline-block; margin-left: 8px; vertical-align: middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        <line x1="9" y1="10" x2="15" y2="10"></line>
                        <line x1="12" y1="7" x2="12" y2="13"></line>
                    </svg>
                    أرسل شكوى
                </a>
            </div>
        </div>
    </section>
@endsection

