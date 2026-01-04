@extends('layouts.front')

@php
    $siteName = $siteName ?? \App\Models\Setting::get('site_name', 'راصد');
@endphp
@section('title', 'خريطة المشغلين - ' . $siteName)

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="{{ asset('assets/front/css/map.css') }}">
<style>
    /* Modern Map Page Redesign */
    .map-page {
        min-height: 100vh;
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    }

    /* Hero Section with Gradient */
    .map-hero {
        background: linear-gradient(135deg, #19228f 0%, #1e3a8a 100%);
        color: white;
        padding: 5rem 0 4rem;
        position: relative;
        overflow: hidden;
    }

    .map-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('{{ asset("assets/front/images/generator-pattern.svg") }}');
        background-size: 250px 250px;
        background-repeat: repeat;
        opacity: 0.08;
        z-index: 0;
    }

    .map-hero::after {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 600px;
        height: 600px;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        border-radius: 50%;
        z-index: 0;
    }

    .gaza-map-background {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 100%;
        height: 100%;
        opacity: 0.25;
        z-index: 0;
        pointer-events: none;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .gaza-map-background svg {
        width: 100%;
        height: auto;
        max-width: 1000px;
        max-height: 600px;
        min-width: 600px;
    }

    .map-hero-content {
        position: relative;
        z-index: 1;
        text-align: center;
        max-width: 800px;
        margin: 0 auto;
        padding: 0 2rem;
    }

    .map-hero-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 2rem;
        background: rgba(255, 255, 255, 0.15);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(10px);
        border: 2px solid rgba(255, 255, 255, 0.2);
        animation: float 3s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
    }

    .map-hero-icon svg {
        width: 45px;
        height: 45px;
        color: white;
    }

    .map-title {
        font-size: 3rem;
        font-weight: 900;
        margin-bottom: 1rem;
        text-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        letter-spacing: -1px;
    }

    .map-subtitle {
        font-size: 1.35rem;
        opacity: 0.95;
        line-height: 1.6;
        font-weight: 400;
    }

    /* Search Card - Modern Design */
    .search-section {
        margin-top: -3rem;
        position: relative;
        z-index: 10;
    }

    .search-card {
        background: white;
        border-radius: 24px;
        padding: 2.5rem;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1), 0 0 0 1px rgba(0, 0, 0, 0.05);
        backdrop-filter: blur(10px);
        transition: all 0.3s ease;
    }

    .search-card:hover {
        box-shadow: 0 25px 80px rgba(0, 0, 0, 0.15), 0 0 0 1px rgba(0, 0, 0, 0.05);
        transform: translateY(-2px);
    }

    .search-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 2px solid #f1f5f9;
    }

    .search-icon {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #19228f 0%, #1e3a8a 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 20px rgba(25, 34, 143, 0.3);
    }

    .search-icon svg {
        width: 24px;
        height: 24px;
        color: white;
    }

    .search-header h2 {
        font-size: 1.5rem;
        font-weight: 800;
        color: #1e293b;
        margin: 0;
    }

    .search-form-modern {
        display: grid;
        grid-template-columns: 1fr auto auto;
        gap: 1rem;
        align-items: end;
    }

    .form-group-modern {
        display: flex;
        flex-direction: column;
    }

    .form-group-modern label {
        font-weight: 700;
        color: #334155;
        margin-bottom: 0.75rem;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .form-group-modern label svg {
        width: 18px;
        height: 18px;
        color: #19228f;
    }

    .form-select-modern {
        width: 100%;
        padding: 1rem 1.25rem;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        font-size: 1rem;
        font-family: 'Tajawal', sans-serif;
        background: white;
        color: #1e293b;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .form-select-modern:focus {
        outline: none;
        border-color: #19228f;
        box-shadow: 0 0 0 4px rgba(25, 34, 143, 0.1);
        transform: translateY(-1px);
    }

    .btn-search-modern {
        padding: 1rem 2rem;
        background: linear-gradient(135deg, #19228f 0%, #1e3a8a 100%);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 700;
        font-family: 'Tajawal', sans-serif;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        box-shadow: 0 8px 20px rgba(25, 34, 143, 0.3);
        white-space: nowrap;
    }

    .btn-search-modern svg {
        width: 18px;
        height: 18px;
        flex-shrink: 0;
        display: block;
    }

    .btn-search-modern:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 30px rgba(25, 34, 143, 0.4);
    }

    .btn-search-modern:active {
        transform: translateY(0);
    }

    .btn-clear-modern {
        padding: 1rem 1.5rem;
        background: #f8fafc;
        color: #64748b;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 600;
        font-family: 'Tajawal', sans-serif;
        cursor: pointer;
        transition: all 0.3s ease;
        white-space: nowrap;
    }

    .btn-clear-modern:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        transform: translateY(-1px);
    }

    /* Stats Preview - Modern Cards */
    .stats-preview-modern {
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 2px solid #f1f5f9;
        display: none;
    }

    .stats-preview-modern.active {
        display: block;
        animation: fadeInUp 0.5s ease;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .stats-grid-modern {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1.5rem;
    }

    .stat-card-modern {
        background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
        border-radius: 16px;
        padding: 1.5rem;
        text-align: center;
        border: 2px solid #f1f5f9;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stat-card-modern::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #19228f 0%, #1e3a8a 100%);
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .stat-card-modern:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
        border-color: #19228f;
    }

    .stat-card-modern:hover::before {
        transform: scaleX(1);
    }

    .stat-icon-modern {
        width: 60px;
        height: 60px;
        margin: 0 auto 1rem;
        background: linear-gradient(135deg, #19228f 0%, #1e3a8a 100%);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 20px rgba(25, 34, 143, 0.25);
    }

    .stat-icon-modern svg {
        width: 28px;
        height: 28px;
        color: white;
    }

    .stat-value-modern {
        font-size: 2rem;
        font-weight: 900;
        color: #19228f;
        margin-bottom: 0.5rem;
        line-height: 1;
    }

    .stat-label-modern {
        font-size: 0.95rem;
        color: #64748b;
        font-weight: 600;
    }

    /* Map Layout - Modern */
    .map-section {
        padding: 3rem 0;
    }

    .map-layout-modern {
        display: grid;
        grid-template-columns: 1fr 240px;
        gap: 1rem;
        margin-top: 2rem;
    }

    .map-sidebar-modern {
        background: white;
        border-radius: 12px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
        padding: 0;
        height: 750px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        border: 1px solid #f1f5f9;
    }

    .sidebar-header-modern {
        background: linear-gradient(135deg, #19228f 0%, #1e3a8a 100%);
        color: white;
        padding: 0.85rem 1rem;
        border-radius: 12px 12px 0 0;
    }

    .sidebar-header-modern h3 {
        font-size: 1rem;
        font-weight: 800;
        margin-bottom: 0.3rem;
        line-height: 1.3;
    }

    .sidebar-header-modern .count {
        font-size: 0.8rem;
        opacity: 0.9;
        display: flex;
        align-items: center;
        gap: 0.35rem;
    }

    .sidebar-header-modern .count svg {
        width: 14px;
        height: 14px;
    }

    .sidebar-content {
        padding: 0.75rem;
        overflow-y: auto;
        flex: 1;
    }

    .operators-list-modern {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .operators-list-modern li {
        padding: 0.75rem;
        margin-bottom: 0.6rem;
        background: #f8fafc;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid transparent;
        position: relative;
    }

    .operators-list-modern li::before {
        content: '';
        position: absolute;
        right: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: #19228f;
        border-radius: 0 12px 12px 0;
        transform: scaleY(0);
        transition: transform 0.3s ease;
    }

    .operators-list-modern li:hover {
        background: #f1f5f9;
        border-color: #e2e8f0;
        transform: translateX(-5px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .operators-list-modern li:hover::before {
        transform: scaleY(1);
    }

    .operators-list-modern li.active {
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        border-color: #19228f;
        box-shadow: 0 4px 12px rgba(25, 34, 143, 0.15);
    }

    .operators-list-modern li.active::before {
        transform: scaleY(1);
    }

    .operator-name-modern,
    .operators-list-modern li .operator-name {
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.35rem;
        font-size: 0.9rem;
        line-height: 1.3;
    }

    .operator-details-modern,
    .operators-list-modern li .operator-details {
        font-size: 0.75rem;
        color: #64748b;
        line-height: 1.4;
    }

    .operators-list-modern li .operator-details br {
        display: none;
    }

    .operators-list-modern li .operator-details {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .map-wrapper-modern {
        position: relative;
    }

    .map-container-modern {
        background: white;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        height: 750px;
        position: relative;
        border: 1px solid #f1f5f9;
    }

    #map {
        width: 100%;
        height: 100%;
        z-index: 1;
    }

    .loading-overlay-modern,
    .no-data-overlay-modern {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(10px);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        border-radius: 20px;
    }

    .spinner-modern {
        width: 50px;
        height: 50px;
        border: 4px solid #f1f5f9;
        border-top-color: #19228f;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-bottom: 1.5rem;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .loading-overlay-modern p,
    .no-data-overlay-modern p {
        color: #64748b;
        font-size: 1.1rem;
        font-weight: 500;
    }

    .no-data-overlay-modern h3 {
        color: #1e293b;
        font-size: 1.75rem;
        font-weight: 800;
        margin-bottom: 0.75rem;
    }

    .no-data-overlay-modern svg {
        width: 80px;
        height: 80px;
        color: #cbd5e1;
        margin-bottom: 1.5rem;
    }

    /* Welcome Message */
    .welcome-message-modern {
        text-align: center;
        padding: 4rem 2rem;
        color: #64748b;
    }

    .welcome-message-modern svg {
        width: 100px;
        height: 100px;
        margin: 0 auto 2rem;
        color: #19228f;
        opacity: 0.6;
    }

    .welcome-message-modern h3 {
        font-size: 1.75rem;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 1rem;
    }

    .welcome-message-modern p {
        font-size: 1.1rem;
        line-height: 1.8;
        max-width: 500px;
        margin: 0 auto;
    }

    /* Responsive Design */
    @media (max-width: 1200px) {
        .map-layout-modern {
            grid-template-columns: 1fr 220px;
        }
    }

    @media (max-width: 1024px) {
        .map-layout-modern {
            grid-template-columns: 1fr;
        }

        .map-sidebar-modern {
            max-height: 400px;
            order: 2;
        }

        .map-wrapper-modern {
            order: 1;
        }
    }

    @media (max-width: 768px) {
        .map-hero {
            padding: 3rem 0 2.5rem;
        }

        .gaza-map-background svg {
            max-width: 700px;
            min-width: 400px;
        }

        .map-title {
            font-size: 2rem;
        }

        .map-subtitle {
            font-size: 1.1rem;
        }
    }

        .search-card {
            padding: 1.5rem;
        }

        .search-form-modern {
            grid-template-columns: 1fr;
        }

        .search-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .map-container-modern {
            height: 500px;
        }

        .stats-grid-modern {
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }
    }

    @media (max-width: 480px) {
        .map-hero-icon {
            width: 60px;
            height: 60px;
        }

        .map-hero-icon svg {
            width: 32px;
            height: 32px;
        }

        .stats-grid-modern {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="map-page">
    <!-- Hero Section -->
    <div class="map-hero">
        <div class="gaza-map-background">
            <svg viewBox="0 0 1000 600" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Gaza Strip Map Outline - More Detailed -->
                <path d="M 120 250 Q 180 200 250 230 Q 320 260 400 280 Q 480 290 560 300 Q 640 310 720 320 L 750 350 L 740 400 L 680 420 L 600 410 L 520 400 L 440 390 L 360 380 L 280 370 L 200 360 L 120 340 Z" 
                      fill="rgba(255,255,255,0.12)" 
                      stroke="rgba(255,255,255,0.5)" 
                      stroke-width="5" 
                      stroke-linejoin="round"
                      stroke-linecap="round"/>
                
                <!-- Governorate boundaries - More detailed -->
                <path d="M 250 230 L 250 370" stroke="rgba(255,255,255,0.4)" stroke-width="4" stroke-dasharray="8,6"/>
                <path d="M 400 280 L 400 390" stroke="rgba(255,255,255,0.4)" stroke-width="4" stroke-dasharray="8,6"/>
                <path d="M 560 300 L 560 400" stroke="rgba(255,255,255,0.4)" stroke-width="4" stroke-dasharray="8,6"/>
                <path d="M 680 320 L 680 420" stroke="rgba(255,255,255,0.4)" stroke-width="4" stroke-dasharray="8,6"/>
                
                <!-- Major cities/governorates markers -->
                <circle cx="185" cy="300" r="12" fill="rgba(255,255,255,0.7)" stroke="rgba(255,255,255,0.95)" stroke-width="3"/>
                <circle cx="325" cy="320" r="12" fill="rgba(255,255,255,0.7)" stroke="rgba(255,255,255,0.95)" stroke-width="3"/>
                <circle cx="480" cy="335" r="12" fill="rgba(255,255,255,0.7)" stroke="rgba(255,255,255,0.95)" stroke-width="3"/>
                <circle cx="620" cy="350" r="12" fill="rgba(255,255,255,0.7)" stroke="rgba(255,255,255,0.95)" stroke-width="3"/>
                <circle cx="710" cy="365" r="12" fill="rgba(255,255,255,0.7)" stroke="rgba(255,255,255,0.95)" stroke-width="3"/>
                
                <!-- Connection lines between major locations -->
                <path d="M 185 300 L 325 320 L 480 335 L 620 350 L 710 365" 
                      stroke="rgba(255,255,255,0.35)" 
                      stroke-width="4" 
                      fill="none" 
                      stroke-dasharray="12,8"
                      stroke-linecap="round"/>
                
                <!-- Coastal line -->
                <path d="M 120 250 Q 200 240 300 250 Q 400 260 500 270 Q 600 280 700 290 L 750 300" 
                      stroke="rgba(255,255,255,0.3)" 
                      stroke-width="3" 
                      fill="none"
                      stroke-linecap="round"/>
                
                <!-- Grid pattern for map detail -->
                <defs>
                    <pattern id="gazaMapGrid" x="0" y="0" width="60" height="60" patternUnits="userSpaceOnUse">
                        <path d="M 60 0 L 0 0 0 60" fill="none" stroke="rgba(255,255,255,0.08)" stroke-width="1"/>
                    </pattern>
                </defs>
                <rect x="120" y="230" width="630" height="190" fill="url(#gazaMapGrid)" opacity="0.4"/>
                
                <!-- Compass rose - Enhanced -->
                <g transform="translate(850, 150)">
                    <circle r="35" fill="rgba(255,255,255,0.15)" stroke="rgba(255,255,255,0.4)" stroke-width="2.5"/>
                    <line x1="0" y1="-28" x2="0" y2="28" stroke="rgba(255,255,255,0.5)" stroke-width="3" stroke-linecap="round"/>
                    <line x1="-28" y1="0" x2="28" y2="0" stroke="rgba(255,255,255,0.5)" stroke-width="3" stroke-linecap="round"/>
                    <polygon points="0,-35 8,-20 -8,-20" fill="rgba(255,255,255,0.6)"/>
                    <text x="0" y="50" text-anchor="middle" fill="rgba(255,255,255,0.6)" font-size="14" font-weight="bold">ش</text>
                </g>
                
                <!-- Additional detail lines for geographic features -->
                <path d="M 200 280 Q 250 270 300 275" stroke="rgba(255,255,255,0.2)" stroke-width="2" fill="none" stroke-dasharray="4,4"/>
                <path d="M 450 300 Q 500 295 550 305" stroke="rgba(255,255,255,0.2)" stroke-width="2" fill="none" stroke-dasharray="4,4"/>
            </svg>
        </div>
        <div class="map-hero-content">
            <div class="map-hero-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                    <circle cx="12" cy="10" r="3"></circle>
                </svg>
            </div>
            <h1 class="map-title">خريطة المشغلين</h1>
            <p class="map-subtitle">استكشف مواقع المشغلين على الخريطة وابحث عن أفضل الخدمات في محافظتك</p>
        </div>
    </div>

    <!-- Search Section -->
    <div class="search-section">
        <div class="container">
            <div class="search-card">
                <div class="search-header">
                    <div class="search-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.35-4.35"></path>
                        </svg>
                    </div>
                    <h2>ابحث عن المشغلين</h2>
                </div>

                <div class="search-form-modern">
                    <div class="form-group-modern">
                        <label for="governorate">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                            المحافظة
                        </label>
                        <select id="governorate" name="governorate" class="form-select-modern">
                            <option value="">-- اختر المحافظة --</option>
                            @foreach($governorates as $governorate)
                                <option value="{{ $governorate->value }}">{{ $governorate->label }}</option>
                            @endforeach
                            <option value="all">جميع المحافظات</option>
                        </select>
                    </div>
                    <button type="button" class="btn-search-modern" id="searchBtn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width: 18px; height: 18px; display: block;">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.35-4.35"></path>
                        </svg>
                        <span>بحث</span>
                    </button>
                    <button type="button" class="btn-clear-modern" id="clearBtn" style="display: none;">
                        مسح
                    </button>
                </div>

                <div class="stats-preview-modern" id="statsPreview">
                    <!-- سيتم ملؤها ديناميكياً -->
                </div>

                <!-- Map and Operators List inside search card -->
                <div class="map-layout-modern" id="mapLayout" style="display: none; margin-top: 2rem;">
                <div class="map-sidebar-modern" id="sidebar">
                    <div class="sidebar-header-modern">
                        <h3>قائمة المشغلين</h3>
                        <div class="count">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 16px; height: 16px;">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                            عدد المشغلين: <span id="sidebarCount">0</span>
                        </div>
                    </div>
                    <div class="sidebar-content">
                        <ul class="operators-list-modern" id="operatorsList">
                            <!-- سيتم ملؤها ديناميكياً -->
                        </ul>
                    </div>
                </div>

                <div class="map-wrapper-modern">
                    <div class="map-container-modern">
                        <div class="loading-overlay-modern" id="loading">
                            <div class="spinner-modern"></div>
                            <p>جاري تحميل البيانات...</p>
                        </div>

                        <div class="no-data-overlay-modern" id="noOperators" style="display: none;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                            <h3>لا توجد مشغلين</h3>
                            <p>لا توجد مشغلين في المحافظة المختارة</p>
                        </div>

                        <div id="map"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Welcome Message -->
    <div class="map-section">
        <div class="container">
            <div class="welcome-message-modern" id="welcomeMessage">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                    <circle cx="12" cy="10" r="3"></circle>
                </svg>
                <h3>مرحباً بك في خريطة المشغلين</h3>
                <p>اختر المحافظة من القائمة أعلاه واضغط على زر البحث لعرض المشغلين على الخريطة</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // Define variables before map.js loads
    const mapApiUrl = '{{ route("front.operators.map") }}';
    const defaultLat = 31.3547;
    const defaultLng = 34.3088;
    const defaultZoom = 10.5;
    
    // Show/hide welcome message and map layout
    document.addEventListener('DOMContentLoaded', function() {
        const searchBtn = document.getElementById('searchBtn');
        const clearBtn = document.getElementById('clearBtn');
        const welcomeMessage = document.getElementById('welcomeMessage');
        const mapLayout = document.getElementById('mapLayout');
        const governorate = document.getElementById('governorate');
        
        if (searchBtn) {
            searchBtn.addEventListener('click', function() {
                if (governorate.value) {
                    welcomeMessage.style.display = 'none';
                    mapLayout.style.display = 'grid';
                    clearBtn.style.display = 'block';
                }
            });
        }
        
        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                governorate.value = '';
                welcomeMessage.style.display = 'block';
                mapLayout.style.display = 'none';
                clearBtn.style.display = 'none';
            });
        }
    });
</script>
<script src="{{ asset('assets/front/js/map.js') }}"></script>
@endpush
