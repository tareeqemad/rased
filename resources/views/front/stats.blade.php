@extends('layouts.front')

@php
    $siteName = $siteName ?? \App\Models\Setting::get('site_name', 'راصد');
@endphp
@section('title', 'الإحصائيات - ' . $siteName)
@section('description', 'إحصائيات شاملة عن المشغلين والمولدات في محافظات غزة')

@push('styles')
<style>
    .stats-page {
        padding: 3rem 0;
    }

    .stats-header {
        text-align: center;
        margin-bottom: 4rem;
    }

    .stats-header h1 {
        font-size: 3rem;
        font-weight: 900;
        margin-bottom: 1rem;
        color: var(--text-dark);
        position: relative;
        display: inline-block;
        padding-bottom: 1.5rem;
    }
    
    .stats-header h1::after {
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

    .stats-header p {
        font-size: 1.25rem;
        color: var(--text-gray);
        position: relative;
        padding-bottom: 2.5rem;
        margin-top: 1rem;
        max-width: 800px;
        margin-left: auto;
        margin-right: auto;
    }
    
    .stats-header p::after {
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

    .main-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 2rem;
        margin-bottom: 4rem;
    }

    .main-stat-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        color: var(--text-dark);
        border-radius: 24px;
        padding: 3.5rem 2.5rem;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(59, 130, 246, 0.1);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        font-family: 'Tajawal', sans-serif;
        position: relative;
        overflow: hidden;
    }
    
    .main-stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        transition: all 0.4s ease;
    }
    
    .main-stat-card:nth-child(1)::before {
        background: linear-gradient(90deg, #3b82f6 0%, #2563eb 100%);
    }
    
    .main-stat-card:nth-child(2)::before {
        background: linear-gradient(90deg, #10b981 0%, #059669 100%);
    }
    
    .main-stat-card:nth-child(3)::before {
        background: linear-gradient(90deg, #f59e0b 0%, #d97706 100%);
    }
    
    .main-stat-card:nth-child(4)::before {
        background: linear-gradient(90deg, #8b5cf6 0%, #7c3aed 100%);
    }

    .main-stat-card:hover {
        transform: translateY(-12px) scale(1.02);
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
        border-color: rgba(59, 130, 246, 0.3);
    }

    .main-stat-icon {
        width: 90px;
        height: 90px;
        margin: 0 auto 1.5rem;
        border-radius: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.4s ease;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }
    
    .main-stat-card:nth-child(1) .main-stat-icon {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        box-shadow: 0 8px 20px rgba(59, 130, 246, 0.25);
    }
    
    .main-stat-card:nth-child(2) .main-stat-icon {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        box-shadow: 0 8px 20px rgba(16, 185, 129, 0.25);
    }
    
    .main-stat-card:nth-child(3) .main-stat-icon {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        box-shadow: 0 8px 20px rgba(245, 158, 11, 0.25);
    }
    
    .main-stat-card:nth-child(4) .main-stat-icon {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        box-shadow: 0 8px 20px rgba(139, 92, 246, 0.25);
    }
    
    .main-stat-card:hover .main-stat-icon {
        transform: rotate(5deg) scale(1.15);
    }

    .main-stat-icon svg {
        width: 45px;
        height: 45px;
        color: white;
    }

    .main-stat-value {
        font-size: 4rem;
        font-weight: 800;
        margin-bottom: 0.5rem;
        line-height: 1;
        background: linear-gradient(135deg, #1e293b 0%, #3b82f6 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .main-stat-card:nth-child(1) .main-stat-value {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .main-stat-card:nth-child(2) .main-stat-value {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .main-stat-card:nth-child(3) .main-stat-value {
        background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .main-stat-card:nth-child(4) .main-stat-value {
        background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .main-stat-label {
        font-size: 1.3rem;
        color: #64748b;
        font-weight: 600;
    }

    .governorate-stats-section {
        background: white;
        border-radius: 20px;
        padding: 3rem;
        box-shadow: var(--shadow-md);
        margin-bottom: 3rem;
    }

    .governorate-stats-title {
        font-size: 2.5rem;
        font-weight: 800;
        margin-bottom: 2rem;
        color: var(--text-dark);
        text-align: center;
        position: relative;
        display: inline-block;
        width: 100%;
        padding-bottom: 1.5rem;
    }
    
    .governorate-stats-title::after {
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

    .governorate-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
    }

    .governorate-stat-card {
        background: var(--bg-light);
        border-radius: 16px;
        padding: 2rem 1.5rem;
        text-align: center;
        border: 2px solid var(--border-color);
        transition: all 0.3s;
    }

    .governorate-stat-card:hover {
        border-color: #cbd5e1;
        transform: translateY(-5px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .governorate-stat-label {
        font-size: 1.1rem;
        color: var(--text-gray);
        margin-bottom: 1rem;
        font-weight: 600;
        font-family: 'Tajawal', sans-serif;
    }

    .governorate-stat-value {
        font-size: 2.5rem;
        font-weight: 700;
        color: #1e293b;
        font-family: 'Tajawal', sans-serif;
    }

    .info-section {
        background: linear-gradient(135deg, var(--bg-light) 0%, white 100%);
        border-radius: 20px;
        padding: 3rem;
        box-shadow: var(--shadow-md);
    }

    .info-section h2 {
        font-size: 2rem;
        font-weight: 800;
        margin-bottom: 1.5rem;
        color: var(--text-dark);
    }

    .info-list {
        list-style: none;
        padding: 0;
    }

    .info-list li {
        padding: 1rem 0;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .info-list li:last-child {
        border-bottom: none;
    }

    .info-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin-left: 15px;
        transition: all 0.3s ease;
    }
    
    .info-list li:nth-child(1) .info-icon {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }
    
    .info-list li:nth-child(1) .info-icon svg {
        width: 20px;
        height: 20px;
        color: white;
    }
    
    .info-list li:nth-child(2) .info-icon {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }
    
    .info-list li:nth-child(2) .info-icon svg {
        width: 20px;
        height: 20px;
        color: white;
    }
    
    .info-list li:nth-child(3) .info-icon {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
    }
    
    .info-list li:nth-child(3) .info-icon svg {
        width: 20px;
        height: 20px;
        color: white;
    }
    
    .info-list li:nth-child(4) .info-icon {
        background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        box-shadow: 0 4px 12px rgba(6, 182, 212, 0.3);
    }
    
    .info-list li:nth-child(4) .info-icon svg {
        width: 20px;
        height: 20px;
        color: white;
    }
    
    .info-list li:hover .info-icon {
        transform: scale(1.1);
    }

    .info-text {
        flex: 1;
        color: var(--text-dark);
        font-size: 1.05rem;
    }

    @media (max-width: 768px) {
        .stats-header h1 {
            font-size: 2rem;
        }

        .main-stats-grid {
            grid-template-columns: 1fr;
        }

        .governorate-stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>
@endpush

@section('content')
<div class="stats-page">
    <div class="container">
        <div class="stats-header">
            <h1>الإحصائيات الشاملة</h1>
            <p>نظرة شاملة على بيانات المشغلين والمولدات في جميع محافظات غزة</p>
        </div>

        <!-- Main Statistics -->
        <div class="main-stats-grid">
            <div class="main-stat-card animate-on-scroll">
                <div class="main-stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <div class="main-stat-value">{{ number_format($stats['total_operators']) }}</div>
                <div class="main-stat-label">مشغل نشط</div>
            </div>

            <div class="main-stat-card animate-on-scroll">
                <div class="main-stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                </div>
                <div class="main-stat-value">{{ number_format($stats['total_generators']) }}</div>
                <div class="main-stat-label">مولد مسجل</div>
            </div>

            <div class="main-stat-card animate-on-scroll">
                <div class="main-stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"></path>
                    </svg>
                </div>
                <div class="main-stat-value">{{ number_format($stats['total_capacity'] / 1000, 1) }}K</div>
                <div class="main-stat-label">كيلو فولت أمبير (KVA)</div>
            </div>

            <div class="main-stat-card animate-on-scroll">
                <div class="main-stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </div>
                <div class="main-stat-value">{{ number_format($stats['active_generators']) }}</div>
                <div class="main-stat-label">مولد نشط</div>
            </div>
        </div>

        <!-- Governorate Statistics -->
        @if($stats['operators_by_governorate']->count() > 0)
        <div class="governorate-stats-section animate-on-scroll">
            <h2 class="governorate-stats-title">توزيع المشغلين حسب المحافظة</h2>
            <div class="governorate-stats-grid">
                @foreach($stats['operators_by_governorate'] as $governorate => $count)
                <div class="governorate-stat-card">
                    <div class="governorate-stat-label">{{ $governorate }}</div>
                    <div class="governorate-stat-value">{{ number_format($count) }}</div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Information Section -->
        <div class="info-section animate-on-scroll">
            <h2>معلومات مهمة</h2>
            <ul class="info-list">
                <li>
                    <div class="info-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                    </div>
                    <div class="info-text">جميع البيانات محدثة ومتابعة بشكل مستمر</div>
                </li>
                <li>
                    <div class="info-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        </svg>
                    </div>
                    <div class="info-text">البيانات موثوقة وتم التحقق منها</div>
                </li>
                <li>
                    <div class="info-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                    </div>
                    <div class="info-text">تحديث البيانات يتم بشكل دوري ومنتظم</div>
                </li>
                <li>
                    <div class="info-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                        </svg>
                    </div>
                    <div class="info-text">يمكنك التواصل مع المشغلين مباشرة من خلال المعلومات المتوفرة</div>
                </li>
            </ul>
        </div>

        <!-- CTA -->
        <div class="text-center" style="margin-top: 3rem;">
            <a href="{{ route('front.map') }}" class="btn btn-primary btn-lg">
                <svg style="width: 20px; height: 20px; display: inline-block; margin-left: 8px; vertical-align: middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                    <circle cx="12" cy="10" r="3"></circle>
                </svg>
                استكشف الخريطة
            </a>
        </div>
    </div>
</div>
@endsection
