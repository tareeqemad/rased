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
    /* Hero Section - تصميم متناسق مع الهيدر الجديد */
    .hero-section {
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 50%, #f1f5f9 100%);
        color: #1e293b;
        padding: 5rem 0 6rem;
        position: relative;
        overflow: hidden;
        margin-top: -1px;
    }

    .hero-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: 
            radial-gradient(circle at 15% 25%, rgba(59, 130, 246, 0.06) 0%, transparent 50%),
            radial-gradient(circle at 85% 75%, rgba(139, 92, 246, 0.04) 0%, transparent 50%);
        pointer-events: none;
    }

    .hero-section::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent 0%, rgba(0, 0, 0, 0.05) 50%, transparent 100%);
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
        position: relative;
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
        filter: drop-shadow(0 20px 50px rgba(59, 130, 246, 0.15)) drop-shadow(0 5px 15px rgba(0, 0, 0, 0.1));
        animation: float 6s ease-in-out infinite, pulse 4s ease-in-out infinite;
        opacity: 1;
    }
    
    @keyframes pulse {
        0%, 100% {
            filter: drop-shadow(0 20px 50px rgba(59, 130, 246, 0.15)) drop-shadow(0 5px 15px rgba(0, 0, 0, 0.1));
        }
        50% {
            filter: drop-shadow(0 25px 60px rgba(59, 130, 246, 0.25)) drop-shadow(0 8px 20px rgba(0, 0, 0, 0.15));
        }
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
        opacity: 0.03;
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
        font-size: 3.8rem;
        font-weight: 800;
        margin-bottom: 1.5rem;
        line-height: 1.2;
        color: #1e293b;
        position: relative;
        padding-bottom: 2rem;
    }
    
    .hero-title::before {
        content: '';
        position: absolute;
        bottom: 0.5rem;
        right: 0;
        width: 8px;
        height: 8px;
        background: #3b82f6;
        border-radius: 50%;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
    }
    
    .hero-title::after {
        content: '';
        position: absolute;
        bottom: 0.5rem;
        right: 12px;
        width: 280px;
        height: 4px;
        background: linear-gradient(270deg, #3b82f6 0%, #10b981 100%);
        border-radius: 4px;
    }
    
    .hero-title span {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .hero-subtitle {
        font-size: 1.3rem;
        margin-bottom: 2.5rem;
        color: #64748b;
        line-height: 1.9;
        font-weight: 500;
        max-width: 90%;
        position: relative;
        padding-bottom: 2.5rem;
    }
    
    .hero-subtitle::after {
        content: '';
        position: absolute;
        bottom: 0;
        right: 0;
        width: 85%;
        max-width: 600px;
        height: 2px;
        background: repeating-linear-gradient(
            90deg,
            transparent,
            transparent 6px,
            rgba(59, 130, 246, 0.5) 6px,
            rgba(59, 130, 246, 0.5) 10px
        );
        border-radius: 1px;
    }

    .hero-actions {
        display: flex;
        gap: 1rem;
        justify-content: flex-start;
        flex-wrap: wrap;
    }

    .btn-hero {
        padding: 1rem 2.5rem;
        font-size: 1.1rem;
        font-weight: 600;
        border-radius: 8px;
        transition: all 0.2s ease;
        font-family: 'Tajawal', sans-serif;
    }

    .btn-hero-primary {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        box-shadow: 0 8px 20px rgba(59, 130, 246, 0.25);
        border: none;
    }

    .btn-hero-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 30px rgba(59, 130, 246, 0.35);
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        color: white !important;
    }

    .btn-hero-secondary {
        background: white;
        color: #475569;
        border: 2px solid #e2e8f0;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .btn-hero-secondary:hover {
        background: #f8fafc;
        border-color: #3b82f6;
        color: #3b82f6;
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(59, 130, 246, 0.15);
    }
    
    /* Hero Stats Cards */
    .hero-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.5rem;
        margin-top: 3rem;
        max-width: 600px;
    }
    
    .hero-stat-item {
        background: white;
        padding: 1.5rem;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
        border: 1px solid #f1f5f9;
        text-align: center;
        transition: all 0.3s ease;
    }
    
    .hero-stat-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(59, 130, 246, 0.12);
        border-color: rgba(59, 130, 246, 0.2);
    }
    
    .hero-stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: #3b82f6;
        margin-bottom: 0.5rem;
    }
    
    .hero-stat-label {
        font-size: 0.9rem;
        color: #64748b;
        font-weight: 500;
    }

    /* Statistics Section */
    .stats-section {
        padding: 5rem 0;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 50%, #ffffff 100%);
        margin-top: -3rem;
        position: relative;
        z-index: 2;
    }
    
    .stats-section::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent 0%, rgba(0, 0, 0, 0.08) 20%, rgba(0, 0, 0, 0.12) 50%, rgba(0, 0, 0, 0.08) 80%, transparent 100%);
    }
    
    .stats-section .section-title {
        position: relative;
        padding-bottom: 1.5rem;
        margin-bottom: 1rem;
    }
    
    .stats-section .section-title::after {
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
    
    .stats-section .section-subtitle {
        position: relative;
        padding-bottom: 2.5rem;
        margin-bottom: 3rem;
    }
    
    .stats-section .section-subtitle::after {
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

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 2rem;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 2rem;
    }

    .stat-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border-radius: 20px;
        padding: 2.5rem 2rem;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(59, 130, 246, 0.1);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        transform: scaleX(0);
        transition: transform 0.4s ease;
    }
    
    .stat-card:nth-child(1)::before {
        background: linear-gradient(90deg, #3b82f6 0%, #2563eb 50%, #1d4ed8 100%);
    }
    
    .stat-card:nth-child(2)::before {
        background: linear-gradient(90deg, #10b981 0%, #059669 50%, #047857 100%);
    }
    
    .stat-card:nth-child(3)::before {
        background: linear-gradient(90deg, #f59e0b 0%, #d97706 50%, #b45309 100%);
    }

    .stat-card:hover {
        transform: translateY(-10px) scale(1.02);
    }
    
    .stat-card:nth-child(1):hover {
        box-shadow: 0 20px 40px rgba(59, 130, 246, 0.15);
        border-color: rgba(59, 130, 246, 0.3);
    }
    
    .stat-card:nth-child(2):hover {
        box-shadow: 0 20px 40px rgba(16, 185, 129, 0.15);
        border-color: rgba(16, 185, 129, 0.3);
    }
    
    .stat-card:nth-child(3):hover {
        box-shadow: 0 20px 40px rgba(245, 158, 11, 0.15);
        border-color: rgba(245, 158, 11, 0.3);
    }
    
    .stat-card:hover::before {
        transform: scaleX(1);
    }

    .stat-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 1.5rem;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.4s ease;
    }
    
    .stat-card:nth-child(1) .stat-icon {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        box-shadow: 0 8px 20px rgba(59, 130, 246, 0.25);
    }
    
    .stat-card:nth-child(1):hover .stat-icon {
        transform: rotate(5deg) scale(1.1);
        box-shadow: 0 12px 30px rgba(59, 130, 246, 0.35);
    }
    
    .stat-card:nth-child(2) .stat-icon {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        box-shadow: 0 8px 20px rgba(16, 185, 129, 0.25);
    }
    
    .stat-card:nth-child(2):hover .stat-icon {
        transform: rotate(-5deg) scale(1.1);
        box-shadow: 0 12px 30px rgba(16, 185, 129, 0.35);
    }
    
    .stat-card:nth-child(3) .stat-icon {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        box-shadow: 0 8px 20px rgba(245, 158, 11, 0.25);
    }
    
    .stat-card:nth-child(3):hover .stat-icon {
        transform: rotate(5deg) scale(1.1);
        box-shadow: 0 12px 30px rgba(245, 158, 11, 0.35);
    }

    .stat-icon svg {
        width: 40px;
        height: 40px;
        color: white;
    }

    .stat-value {
        font-size: 3.5rem;
        font-weight: 800;
        background: linear-gradient(135deg, #1e293b 0%, #3b82f6 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 0.5rem;
        line-height: 1;
        font-family: 'Tajawal', sans-serif;
    }

    .stat-label {
        font-size: 1.15rem;
        color: #64748b;
        font-weight: 600;
        font-family: 'Tajawal', sans-serif;
    }

    /* Features Section */
    .features-section {
        padding: 6rem 0;
        background: linear-gradient(135deg, #f8fafc 0%, #ffffff 50%, #f0f9ff 100%);
        position: relative;
    }
    
    .features-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><g fill="%233b82f6" fill-opacity="0.03"><circle cx="30" cy="30" r="2"/></g></svg>');
        opacity: 0.5;
    }
    
    .features-section .section-title {
        position: relative;
        padding-bottom: 1.5rem;
        margin-bottom: 1rem;
    }
    
    .features-section .section-title::after {
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
    
    .features-section .section-subtitle {
        position: relative;
        padding-bottom: 2.5rem;
        margin-bottom: 3rem;
    }
    
    .features-section .section-subtitle::after {
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

    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2.5rem;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 2rem;
        position: relative;
        z-index: 1;
    }

    .feature-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border-radius: 20px;
        padding: 2.5rem 2rem;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(59, 130, 246, 0.1);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    
    .feature-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        transform: scaleX(0);
        transition: transform 0.4s ease;
    }
    
    .feature-card:nth-child(1)::before {
        background: linear-gradient(90deg, #3b82f6 0%, #2563eb 50%, #1d4ed8 100%);
    }
    
    .feature-card:nth-child(2)::before {
        background: linear-gradient(90deg, #10b981 0%, #059669 50%, #047857 100%);
    }
    
    .feature-card:nth-child(3)::before {
        background: linear-gradient(90deg, #f59e0b 0%, #d97706 50%, #b45309 100%);
    }
    
    .feature-card:nth-child(4)::before {
        background: linear-gradient(90deg, #8b5cf6 0%, #7c3aed 50%, #6d28d9 100%);
    }
    
    .feature-card:nth-child(5)::before {
        background: linear-gradient(90deg, #ef4444 0%, #dc2626 50%, #b91c1c 100%);
    }
    
    .feature-card:nth-child(6)::before {
        background: linear-gradient(90deg, #06b6d4 0%, #0891b2 50%, #0e7490 100%);
    }

    .feature-card:hover {
        transform: translateY(-10px) scale(1.02);
    }
    
    .feature-card:nth-child(1):hover {
        box-shadow: 0 20px 40px rgba(59, 130, 246, 0.15);
        border-color: rgba(59, 130, 246, 0.3);
    }
    
    .feature-card:nth-child(2):hover {
        box-shadow: 0 20px 40px rgba(16, 185, 129, 0.15);
        border-color: rgba(16, 185, 129, 0.3);
    }
    
    .feature-card:nth-child(3):hover {
        box-shadow: 0 20px 40px rgba(245, 158, 11, 0.15);
        border-color: rgba(245, 158, 11, 0.3);
    }
    
    .feature-card:nth-child(4):hover {
        box-shadow: 0 20px 40px rgba(139, 92, 246, 0.15);
        border-color: rgba(139, 92, 246, 0.3);
    }
    
    .feature-card:nth-child(5):hover {
        box-shadow: 0 20px 40px rgba(239, 68, 68, 0.15);
        border-color: rgba(239, 68, 68, 0.3);
    }
    
    .feature-card:nth-child(6):hover {
        box-shadow: 0 20px 40px rgba(6, 182, 212, 0.15);
        border-color: rgba(6, 182, 212, 0.3);
    }
    
    .feature-card:hover::before {
        transform: scaleX(1);
    }

    .feature-icon {
        width: 70px;
        height: 70px;
        margin: 0 auto 1.5rem;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.4s ease;
        position: relative;
        z-index: 1;
    }
    
    .feature-card:nth-child(1) .feature-icon {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }
    
    .feature-card:nth-child(1) .feature-icon svg {
        color: white;
        width: 36px;
        height: 36px;
    }
    
    .feature-card:nth-child(1):hover .feature-icon {
        transform: rotate(5deg) scale(1.1);
        box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4);
    }
    
    .feature-card:nth-child(2) .feature-icon {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }
    
    .feature-card:nth-child(2) .feature-icon svg {
        color: white;
        width: 36px;
        height: 36px;
    }
    
    .feature-card:nth-child(2):hover .feature-icon {
        transform: rotate(-5deg) scale(1.1);
        box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
    }
    
    .feature-card:nth-child(3) .feature-icon {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
    }
    
    .feature-card:nth-child(3) .feature-icon svg {
        color: white;
        width: 36px;
        height: 36px;
    }
    
    .feature-card:nth-child(3):hover .feature-icon {
        transform: rotate(5deg) scale(1.1);
        box-shadow: 0 8px 20px rgba(245, 158, 11, 0.4);
    }
    
    .feature-card:nth-child(4) .feature-icon {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
    }
    
    .feature-card:nth-child(4) .feature-icon svg {
        color: white;
        width: 36px;
        height: 36px;
    }
    
    .feature-card:nth-child(4):hover .feature-icon {
        transform: rotate(-5deg) scale(1.1);
        box-shadow: 0 8px 20px rgba(139, 92, 246, 0.4);
    }
    
    .feature-card:nth-child(5) .feature-icon {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    }
    
    .feature-card:nth-child(5) .feature-icon svg {
        color: white;
        width: 36px;
        height: 36px;
    }
    
    .feature-card:nth-child(5):hover .feature-icon {
        transform: rotate(5deg) scale(1.1);
        box-shadow: 0 8px 20px rgba(239, 68, 68, 0.4);
    }
    
    .feature-card:nth-child(6) .feature-icon {
        background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        box-shadow: 0 4px 12px rgba(6, 182, 212, 0.3);
    }
    
    .feature-card:nth-child(6) .feature-icon svg {
        color: white;
        width: 36px;
        height: 36px;
    }
    
    .feature-card:nth-child(6):hover .feature-icon {
        transform: rotate(-5deg) scale(1.1);
        box-shadow: 0 8px 20px rgba(6, 182, 212, 0.4);
    }

    .feature-title {
        font-size: 1.3rem;
        font-weight: 700;
        margin-bottom: 0.75rem;
        color: #1e293b;
        position: relative;
        z-index: 1;
    }

    .feature-text {
        color: #64748b;
        line-height: 1.7;
        font-size: 0.95rem;
        position: relative;
        z-index: 1;
    }


    @media (max-width: 968px) {
        .hero-content {
            grid-template-columns: 1fr;
            gap: 2rem;
        }

        .hero-text {
            text-align: center;
        }
        
        .hero-actions {
            justify-content: center;
        }
        
        .hero-stats {
            max-width: 100%;
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
                    <svg class="hero-image-main" viewBox="0 0 500 400" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <linearGradient id="screenGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:#ffffff;stop-opacity:0.98" />
                                <stop offset="100%" style="stop-color:#f8fafc;stop-opacity:0.95" />
                            </linearGradient>
                            <linearGradient id="cardGradient1" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:#3b82f6;stop-opacity:1" />
                                <stop offset="100%" style="stop-color:#2563eb;stop-opacity:1" />
                            </linearGradient>
                            <linearGradient id="cardGradient2" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:#10b981;stop-opacity:1" />
                                <stop offset="100%" style="stop-color:#059669;stop-opacity:1" />
                            </linearGradient>
                            <linearGradient id="cardGradient3" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:#f59e0b;stop-opacity:1" />
                                <stop offset="100%" style="stop-color:#d97706;stop-opacity:1" />
                            </linearGradient>
                            <filter id="glow">
                                <feGaussianBlur stdDeviation="3" result="coloredBlur"/>
                                <feMerge>
                                    <feMergeNode in="coloredBlur"/>
                                    <feMergeNode in="SourceGraphic"/>
                                </feMerge>
                            </filter>
                        </defs>
                        
                        <!-- Main Dashboard Screen -->
                        <rect x="30" y="40" width="440" height="320" rx="20" fill="url(#screenGradient)" stroke="rgba(59, 130, 246, 0.2)" stroke-width="2"/>
                        <rect x="50" y="60" width="400" height="280" rx="12" fill="rgba(255,255,255,0.95)"/>
                        
                        <!-- Header Bar -->
                        <rect x="50" y="60" width="400" height="50" rx="12" fill="linear-gradient(135deg, #f8fafc 0%, #ffffff 100%)"/>
                        <rect x="70" y="75" width="120" height="20" rx="4" fill="rgba(59, 130, 246, 0.1)"/>
                        <circle cx="200" cy="85" r="6" fill="#3b82f6"/>
                        <circle cx="220" cy="85" r="6" fill="#10b981"/>
                        <circle cx="240" cy="85" r="6" fill="#f59e0b"/>
                        
                        <!-- Map Section (Left Side) -->
                        <rect x="70" y="130" width="180" height="180" rx="10" fill="rgba(241, 245, 249, 0.8)" stroke="rgba(59, 130, 246, 0.15)" stroke-width="1.5"/>
                        
                        <!-- Gaza Strip Map Outline -->
                        <path d="M 90 200 Q 120 170 150 185 T 200 200 T 230 210" 
                              fill="rgba(59, 130, 246, 0.1)" 
                              stroke="rgba(59, 130, 246, 0.4)" 
                              stroke-width="2.5" 
                              stroke-linejoin="round"/>
                        
                        <!-- Map Markers -->
                        <circle cx="120" cy="190" r="6" fill="#3b82f6" filter="url(#glow)"/>
                        <circle cx="160" cy="200" r="6" fill="#10b981" filter="url(#glow)"/>
                        <circle cx="200" cy="210" r="6" fill="#f59e0b" filter="url(#glow)"/>
                        
                        <!-- Connection Lines -->
                        <path d="M 120 190 L 160 200 L 200 210" stroke="rgba(59, 130, 246, 0.3)" stroke-width="1.5" stroke-dasharray="4,2"/>
                        
                        <!-- Map Title -->
                        <rect x="90" y="140" width="140" height="12" rx="2" fill="rgba(59, 130, 246, 0.15)"/>
                        
                        <!-- Statistics Cards (Right Side) -->
                        <!-- Card 1: Operators -->
                        <rect x="270" y="130" width="160" height="50" rx="8" fill="url(#cardGradient1)" opacity="0.95"/>
                        <rect x="280" y="140" width="30" height="30" rx="6" fill="rgba(255,255,255,0.3)"/>
                        <rect x="320" y="145" width="100" height="8" rx="2" fill="rgba(255,255,255,0.9)"/>
                        <rect x="320" y="158" width="80" height="6" rx="2" fill="rgba(255,255,255,0.6)"/>
                        
                        <!-- Card 2: Generators -->
                        <rect x="270" y="190" width="160" height="50" rx="8" fill="url(#cardGradient2)" opacity="0.95"/>
                        <rect x="280" y="200" width="30" height="30" rx="6" fill="rgba(255,255,255,0.3)"/>
                        <rect x="320" y="205" width="100" height="8" rx="2" fill="rgba(255,255,255,0.9)"/>
                        <rect x="320" y="218" width="70" height="6" rx="2" fill="rgba(255,255,255,0.6)"/>
                        
                        <!-- Card 3: Capacity -->
                        <rect x="270" y="250" width="160" height="50" rx="8" fill="url(#cardGradient3)" opacity="0.95"/>
                        <rect x="280" y="260" width="30" height="30" rx="6" fill="rgba(255,255,255,0.3)"/>
                        <rect x="320" y="265" width="100" height="8" rx="2" fill="rgba(255,255,255,0.9)"/>
                        <rect x="320" y="278" width="90" height="6" rx="2" fill="rgba(255,255,255,0.6)"/>
                        
                        <!-- Chart/Graph Section (Bottom) -->
                        <rect x="70" y="320" width="360" height="15" rx="4" fill="rgba(59, 130, 246, 0.08)"/>
                        <rect x="80" y="325" width="8" height="5" rx="2" fill="#3b82f6"/>
                        <rect x="100" y="323" width="8" height="7" rx="2" fill="#10b981"/>
                        <rect x="120" y="324" width="8" height="6" rx="2" fill="#3b82f6"/>
                        <rect x="140" y="322" width="8" height="8" rx="2" fill="#f59e0b"/>
                        <rect x="160" y="325" width="8" height="5" rx="2" fill="#3b82f6"/>
                        <rect x="180" y="323" width="8" height="7" rx="2" fill="#10b981"/>
                        <rect x="200" y="321" width="8" height="9" rx="2" fill="#8b5cf6"/>
                        <rect x="220" y="324" width="8" height="6" rx="2" fill="#3b82f6"/>
                        <rect x="240" y="322" width="8" height="8" rx="2" fill="#10b981"/>
                        <rect x="260" y="325" width="8" height="5" rx="2" fill="#3b82f6"/>
                        
                        <!-- Floating Elements (Icons) -->
                        <circle cx="420" cy="200" r="20" fill="rgba(59, 130, 246, 0.1)" stroke="rgba(59, 130, 246, 0.3)" stroke-width="1.5"/>
                        <path d="M 415 195 L 425 200 L 415 205 Z" fill="#3b82f6" opacity="0.8"/>
                        
                        <circle cx="420" cy="250" r="20" fill="rgba(16, 185, 129, 0.1)" stroke="rgba(16, 185, 129, 0.3)" stroke-width="1.5"/>
                        <rect x="412" y="242" width="16" height="16" rx="2" fill="#10b981" opacity="0.8"/>
                        
                        <!-- Decorative Elements -->
                        <circle cx="460" cy="100" r="3" fill="#3b82f6" opacity="0.4"/>
                        <circle cx="475" cy="110" r="2" fill="#10b981" opacity="0.4"/>
                        <circle cx="470" cy="125" r="2.5" fill="#f59e0b" opacity="0.4"/>
                        
                        <!-- Notification Badge -->
                        <circle cx="470" cy="85" r="8" fill="#ef4444"/>
                        <text x="470" y="90" text-anchor="middle" fill="white" font-size="10" font-weight="bold">3</text>
                        
                        <!-- Generator Icons on Map -->
                        <!-- Generator 1 -->
                        <g transform="translate(110, 180)">
                            <rect x="-8" y="-8" width="16" height="16" rx="3" fill="rgba(59, 130, 246, 0.15)" stroke="#3b82f6" stroke-width="1.5"/>
                            <circle cx="0" cy="-2" r="3" fill="#3b82f6"/>
                            <rect x="-4" y="2" width="8" height="4" rx="1" fill="#3b82f6"/>
                            <line x1="-6" y1="6" x2="6" y2="6" stroke="#3b82f6" stroke-width="1"/>
                        </g>
                        
                        <!-- Generator 2 -->
                        <g transform="translate(150, 190)">
                            <rect x="-8" y="-8" width="16" height="16" rx="3" fill="rgba(16, 185, 129, 0.15)" stroke="#10b981" stroke-width="1.5"/>
                            <circle cx="0" cy="-2" r="3" fill="#10b981"/>
                            <rect x="-4" y="2" width="8" height="4" rx="1" fill="#10b981"/>
                            <line x1="-6" y1="6" x2="6" y2="6" stroke="#10b981" stroke-width="1"/>
                        </g>
                        
                        <!-- Generator 3 -->
                        <g transform="translate(190, 200)">
                            <rect x="-8" y="-8" width="16" height="16" rx="3" fill="rgba(245, 158, 11, 0.15)" stroke="#f59e0b" stroke-width="1.5"/>
                            <circle cx="0" cy="-2" r="3" fill="#f59e0b"/>
                            <rect x="-4" y="2" width="8" height="4" rx="1" fill="#f59e0b"/>
                            <line x1="-6" y1="6" x2="6" y2="6" stroke="#f59e0b" stroke-width="1"/>
                        </g>
                        
                        <!-- Power Grid Lines -->
                        <!-- Horizontal Power Lines -->
                        <line x1="70" y1="200" x2="250" y2="200" stroke="rgba(59, 130, 246, 0.4)" stroke-width="2" stroke-dasharray="3,2"/>
                        <line x1="70" y1="220" x2="250" y2="220" stroke="rgba(16, 185, 129, 0.4)" stroke-width="2" stroke-dasharray="3,2"/>
                        <line x1="70" y1="240" x2="250" y2="240" stroke="rgba(245, 158, 11, 0.4)" stroke-width="2" stroke-dasharray="3,2"/>
                        
                        <!-- Vertical Power Lines -->
                        <line x1="120" y1="130" x2="120" y2="310" stroke="rgba(59, 130, 246, 0.3)" stroke-width="1.5" stroke-dasharray="2,3"/>
                        <line x1="160" y1="130" x2="160" y2="310" stroke="rgba(16, 185, 129, 0.3)" stroke-width="1.5" stroke-dasharray="2,3"/>
                        <line x1="200" y1="130" x2="200" y2="310" stroke="rgba(245, 158, 11, 0.3)" stroke-width="1.5" stroke-dasharray="2,3"/>
                        
                        <!-- Power Transmission Towers (Small) -->
                        <g transform="translate(90, 280)">
                            <line x1="0" y1="0" x2="0" y2="-15" stroke="#64748b" stroke-width="2"/>
                            <line x1="-5" y1="-5" x2="0" y2="-15" stroke="#64748b" stroke-width="1.5"/>
                            <line x1="5" y1="-5" x2="0" y2="-15" stroke="#64748b" stroke-width="1.5"/>
                            <line x1="-3" y1="-10" x2="3" y2="-10" stroke="#64748b" stroke-width="1"/>
                        </g>
                        
                        <g transform="translate(130, 285)">
                            <line x1="0" y1="0" x2="0" y2="-15" stroke="#64748b" stroke-width="2"/>
                            <line x1="-5" y1="-5" x2="0" y2="-15" stroke="#64748b" stroke-width="1.5"/>
                            <line x1="5" y1="-5" x2="0" y2="-15" stroke="#64748b" stroke-width="1.5"/>
                            <line x1="-3" y1="-10" x2="3" y2="-10" stroke="#64748b" stroke-width="1"/>
                        </g>
                        
                        <g transform="translate(170, 290)">
                            <line x1="0" y1="0" x2="0" y2="-15" stroke="#64748b" stroke-width="2"/>
                            <line x1="-5" y1="-5" x2="0" y2="-15" stroke="#64748b" stroke-width="1.5"/>
                            <line x1="5" y1="-5" x2="0" y2="-15" stroke="#64748b" stroke-width="1.5"/>
                            <line x1="-3" y1="-10" x2="3" y2="-10" stroke="#64748b" stroke-width="1"/>
                        </g>
                        
                        <!-- Power Lines Connecting Towers -->
                        <path d="M 90 280 Q 110 275 130 285 Q 150 290 170 290" 
                              stroke="rgba(59, 130, 246, 0.5)" 
                              stroke-width="2" 
                              fill="none" 
                              stroke-linecap="round"/>
                        
                        <!-- Lightning/Energy Symbols -->
                        <g transform="translate(140, 160)">
                            <path d="M 0 -8 L -6 0 L 0 0 L 6 8 L 0 0 L 6 0 Z" fill="#fbbf24" opacity="0.8"/>
                        </g>
                        
                        <g transform="translate(180, 170)">
                            <path d="M 0 -8 L -6 0 L 0 0 L 6 8 L 0 0 L 6 0 Z" fill="#10b981" opacity="0.8"/>
                        </g>
                        
                        <!-- Power Station Icon (Bottom Left) -->
                        <g transform="translate(100, 300)">
                            <rect x="-12" y="-12" width="24" height="20" rx="3" fill="rgba(59, 130, 246, 0.2)" stroke="#3b82f6" stroke-width="2"/>
                            <rect x="-8" y="-8" width="16" height="12" rx="2" fill="rgba(255,255,255,0.9)"/>
                            <circle cx="-4" cy="-2" r="2" fill="#3b82f6"/>
                            <circle cx="4" cy="-2" r="2" fill="#10b981"/>
                            <rect x="-6" y="4" width="12" height="3" rx="1" fill="#3b82f6"/>
                            <line x1="0" y1="-12" x2="0" y2="-18" stroke="#3b82f6" stroke-width="2"/>
                            <circle cx="0" cy="-20" r="3" fill="#3b82f6" opacity="0.6"/>
                        </g>
                    </svg>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="stats-section">
        <div class="container">
            <h2 class="section-title">الإحصائيات</h2>
            <p class="section-subtitle">إحصائيات شاملة عن المشغلين والمولدات في محافظات غزة</p>
        </div>
        
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

@endsection
