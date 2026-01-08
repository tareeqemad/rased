<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $siteName = $siteName ?? \App\Models\Setting::get('site_name', 'راصد');
    @endphp
    <title>@yield('title', $siteName . ' - منصة رقمية لإدارة سوق الطاقة')</title>
    <meta name="description" content="@yield('description', 'منصة رقمية متكاملة لتنظيم وإدارة سوق الطاقة في محافظات غزة')">
    
    <!-- Tajawal Font from Admin Panel -->
    <link rel="stylesheet" href="{{ asset('assets/admin/css/tajawal-font.css') }}" />
    
    <!-- Bootstrap 5 RTL -->
    <link href="{{ asset('assets/front/css/bootstrap.rtl.min.css') }}" rel="stylesheet">
    
    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('assets/front/css/main.css') }}">
    
    <style>
        /* Navigation background - تصميم أبيض/فضي أنيق */
        .public-nav {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 50%, #f1f5f9 100%) !important;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08), 0 0 0 1px rgba(0, 0, 0, 0.05) !important;
            border-bottom: 1px solid rgba(0, 0, 0, 0.08) !important;
            backdrop-filter: blur(12px) !important;
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            width: 100% !important;
            z-index: 9999 !important;
            padding: 0.85rem 0 !important;
        }
        
        /* إضافة padding للـ body لتجنب تغطية المحتوى */
        body {
            padding-top: 70px !important;
        }
        
        /* تطبيق الخط والأنماط */
        .public-nav,
        .public-nav * {
            font-family: 'Tajawal', sans-serif !important;
        }
        
        .brand-text {
            font-weight: 700 !important;
            font-size: 1.4rem !important;
            letter-spacing: -0.3px !important;
            color: #1e293b !important;
        }
        
        .brand-link {
            color: #1e293b !important;
        }
        
        .nav-link {
            font-weight: 600 !important;
            font-size: 0.95rem !important;
            padding: 0.5rem 1rem !important;
            border-radius: 8px !important;
            transition: all 0.2s ease !important;
            color: #475569 !important;
        }
        
        .nav-link:hover {
            background: rgba(59, 130, 246, 0.1) !important;
            color: #3b82f6 !important;
            transform: translateY(-1px) !important;
        }
        
        .nav-link.active {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important;
            color: white !important;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.25) !important;
        }
        
        .brand-icon {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important;
            border-radius: 10px !important;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2) !important;
        }
        
        .brand-icon svg {
            color: white !important;
        }
        
        .nav-toggle span {
            background-color: #1e293b !important;
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
                <li><a href="{{ route('front.join') }}" class="nav-link {{ request()->routeIs('front.join') ? 'active' : '' }}">طلب الانضمام</a></li>
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
            <p class="footer-copyright">جميع الحقوق محفوظة © 2026 {{ $siteName ?? 'راصد' }}.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="{{ asset('assets/front/js/bootstrap.bundle.min.js') }}"></script>
    
    <!-- Scripts -->
    <script src="{{ asset('assets/front/js/main.js') }}"></script>
    @stack('scripts')
</body>
</html>
