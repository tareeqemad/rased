<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'راصد - منصة رقمية لإدارة سوق الطاقة')</title>
    <meta name="description" content="@yield('description', 'منصة رقمية متكاملة لتنظيم وإدارة سوق الطاقة في محافظات غزة')">
    
    <!-- Tajawal Font from Admin Panel -->
    <link rel="stylesheet" href="{{ asset('assets/admin/css/tajawal-font.css') }}" />
    
    <!-- Bootstrap 5 RTL -->
    <link href="{{ asset('assets/front/css/bootstrap.rtl.min.css') }}" rel="stylesheet">
    
    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('assets/front/css/main.css') }}">
    
    <style>
        /* Override navigation background with correct path */
        .public-nav {
            background: 
                url('{{ asset("assets/front/images/generator-pattern.svg") }}'),
                rgba(20, 26, 107, 0.95) !important;
            background-size: 200px 200px, cover !important;
            background-position: center, center !important;
            background-repeat: repeat, no-repeat !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15), 0 0 0 1px rgba(255, 255, 255, 0.1) !important;
            border-bottom: 2px solid rgba(255, 255, 255, 0.15) !important;
            backdrop-filter: blur(10px) !important;
        }
        
        /* تحسين التباين في الصفحة الرئيسية */
        body:has(.hero-section) .public-nav {
            background: 
                url('{{ asset("assets/front/images/generator-pattern.svg") }}'),
                rgba(15, 20, 80, 0.98) !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(255, 255, 255, 0.2) !important;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Navigation -->
    <nav class="public-nav">
        <div class="nav-container">
            <div class="nav-brand">
                <a href="{{ route('front.home') }}" class="brand-link">
                    @if(isset($logoUrl) && $logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $siteName ?? 'راصد' }}" class="brand-logo">
                    @else
                        <div class="brand-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"></path>
                            </svg>
                        </div>
                    @endif
                    <span class="brand-text">{{ $siteName ?? 'راصد' }}</span>
                </a>
            </div>
            
            <button class="nav-toggle" id="navToggle" aria-label="قائمة التنقل">
                <span></span>
                <span></span>
                <span></span>
            </button>
            
            <ul class="nav-menu" id="navMenu">
                <li><a href="{{ route('front.home') }}" class="nav-link {{ request()->routeIs('front.home') ? 'active' : '' }}">الرئيسية</a></li>
                <li><a href="{{ route('front.map') }}" class="nav-link {{ request()->routeIs('front.map') ? 'active' : '' }}">الخريطة</a></li>
                <li><a href="{{ route('front.stats') }}" class="nav-link {{ request()->routeIs('front.stats') ? 'active' : '' }}">الإحصائيات</a></li>
                <li><a href="{{ route('front.about') }}" class="nav-link {{ request()->routeIs('front.about') ? 'active' : '' }}">من نحن</a></li>
                <li><a href="{{ route('complaints-suggestions.index') }}" class="nav-link {{ request()->routeIs('complaints-suggestions.*') ? 'active' : '' }}">الشكاوي والمقترحات</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="public-main">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="public-footer">
        <div class="footer-container">
            <div class="footer-grid">
                <div class="footer-col">
                    <h3 class="footer-title">عن راصد</h3>
                    <p class="footer-text">
                        منصة رقمية متكاملة لتنظيم وإدارة سوق الطاقة في محافظات غزة. نسعى لتوفير خدمة أفضل للمواطنين والمشغلين.
                    </p>
                </div>
                
                <div class="footer-col">
                    <h3 class="footer-title">روابط سريعة</h3>
                    <ul class="footer-links">
                        <li><a href="{{ route('front.home') }}">الرئيسية</a></li>
                        <li><a href="{{ route('front.map') }}">خريطة المشغلين</a></li>
                        <li><a href="{{ route('front.stats') }}">الإحصائيات</a></li>
                        <li><a href="{{ route('complaints-suggestions.index') }}">الشكاوي والمقترحات</a></li>
                    </ul>
                </div>
                
                <div class="footer-col">
                    <h3 class="footer-title">تواصل معنا</h3>
                    <ul class="footer-links">
                        <li><a href="{{ route('complaints-suggestions.create') }}">إرسال شكوى</a></li>
                        <li><a href="{{ route('complaints-suggestions.create') }}">إرسال اقتراح</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} راصد. جميع الحقوق محفوظة.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="{{ asset('assets/front/js/bootstrap.bundle.min.js') }}"></script>
    
    <!-- Scripts -->
    <script src="{{ asset('assets/front/js/main.js') }}"></script>
    @stack('scripts')
</body>
</html>

