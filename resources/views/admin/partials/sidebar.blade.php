<aside class="app-sidebar sticky" id="sidebar">
    <div class="main-sidebar-header">
        <a href="{{ route('admin.dashboard') }}" class="header-logo">
            @php
                $logo = \App\Models\Setting::get('site_logo', 'assets/admin/images/brand-logos/rased_logo.png');
                $logoUrl = str_starts_with($logo, 'http') ? $logo : asset($logo);
            @endphp
            <img src="{{ $logoUrl }}" alt="{{ \App\Models\Setting::get('site_name', 'راصد') }}" class="desktop-logo">
            <img src="{{ $logoUrl }}" alt="{{ \App\Models\Setting::get('site_name', 'راصد') }}" class="toggle-logo">
            <img src="{{ $logoUrl }}" alt="{{ \App\Models\Setting::get('site_name', 'راصد') }}" class="desktop-dark">
            <img src="{{ $logoUrl }}" alt="{{ \App\Models\Setting::get('site_name', 'راصد') }}" class="toggle-dark">
            <img src="{{ $logoUrl }}" alt="{{ \App\Models\Setting::get('site_name', 'راصد') }}" class="desktop-white">
            <img src="{{ $logoUrl }}" alt="{{ \App\Models\Setting::get('site_name', 'راصد') }}" class="toggle-white">
        </a>
    </div>

    <div class="main-sidebar" id="sidebar-scroll">
        <nav class="main-menu-container nav nav-pills flex-column sub-open">

            <div class="slide-left" id="slide-left">
                <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
                    <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z"></path>
                </svg>
            </div>

            @php
                $u = auth()->user();
                $isActive = fn($routes) => request()->routeIs($routes) ? 'active' : '';
                $isOpen   = fn($routes) => request()->routeIs($routes) ? 'open'   : '';
                $show     = fn($routes) => request()->routeIs($routes) ? 'display:block' : '';
            @endphp

            <ul class="main-menu">

                {{-- Dashboard --}}
                <li class="slide {{ $isActive('admin.dashboard') }}">
                    <a href="{{ route('admin.dashboard') }}" class="side-menu__item">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M3 13h1v7c0 1.103.897 2 2 2h12c1.103 0 2-.897 2-2v-7h1a1 1 0 0 0 .707-1.707l-9-9a.999.999 0 0 0-1.414 0l-9 9A1 1 0 0 0 3 13zm7 7v-5h4v5h-4zm2-15.586 6 6V15l.001 5H16v-5c0-1.103-.897-2-2-2h-4c-1.103 0-2 .897-2 2v5H6v-9.586l6-6z"/></svg>
                        <span class="side-menu__label">لوحة التحكم</span>
                    </a>
                </li>

                {{-- صلاحيات: SuperAdmin + CompanyOwner فقط --}}
                @if($u->isSuperAdmin() || $u->isCompanyOwner())
                    <li class="slide__category mt-3">
                        <span class="side-menu__label text-muted text-xs opacity-70">الصلاحيات</span>
                    </li>

                    <li class="slide {{ $isActive('admin.permissions.*') }}">
                        <a href="{{ route('admin.permissions.index') }}" class="side-menu__item">
                            <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M12 2L4 5v6.09c0 5.05 3.41 9.76 8 10.91 4.59-1.15 8-5.86 8-10.91V5l-8-3zm6 9.09c0 4-2.55 7.7-6 8.83-3.45-1.13-6-4.83-6-8.83V6.31l6-2.12 6 2.12v4.78z"/></svg>
                            <span class="side-menu__label">شجرة الصلاحيات</span>
                        </a>
                    </li>
                    
                    @if($u->isCompanyOwner())
                        <li class="slide {{ $isActive('admin.roles.*') }}">
                            <a href="{{ route('admin.roles.index') }}" class="side-menu__item">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M12 2L4 5v6.09c0 5.05 3.41 9.76 8 10.91 4.59-1.15 8-5.86 8-10.91V5l-8-3zm6 9.09c0 4-2.55 7.7-6 8.83-3.45-1.13-6-4.83-6-8.83V6.31l6-2.12 6 2.12v4.78z"/></svg>
                                <span class="side-menu__label">الأدوار المخصصة</span>
                            </a>
                        </li>
                    @endif
                @endif

                {{-- إدارة النظام: SuperAdmin فقط (الثوابت/الأدوار/المستخدمين الكلّي) --}}
                @if($u->isSuperAdmin())
                    <li class="slide__category mt-3">
                        <span class="side-menu__label text-muted text-xs opacity-70">إدارة النظام</span>
                    </li>

                    <li class="slide has-sub {{ $isOpen('admin.roles.*') }}">
                        <a href="javascript:void(0);" class="side-menu__item {{ $isActive('admin.roles.*') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__angle" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M12 2L4 5v6.09c0 5.05 3.41 9.76 8 10.91 4.59-1.15 8-5.86 8-10.91V5l-8-3zm6 9.09c0 4-2.55 7.7-6 8.83-3.45-1.13-6-4.83-6-8.83V6.31l6-2.12 6 2.12v4.78z"/></svg>
                            <span class="side-menu__label">الأدوار</span>
                        </a>
                        <ul class="slide-menu child1" style="{{ $show('admin.roles.*') }}">
                            <li class="slide">
                                <a href="{{ route('admin.roles.index') }}" class="side-menu__item {{ $isActive('admin.roles.index') }}">
                                    قائمة الأدوار
                                </a>
                            </li>
                            <li class="slide">
                                <a href="{{ route('admin.roles.create') }}" class="side-menu__item {{ $isActive('admin.roles.create') }}">
                                    إضافة دور جديد
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="slide {{ $isActive('admin.constants.*') }}">
                        <a href="{{ route('admin.constants.index') }}" class="side-menu__item">
                            <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M4 6h16v2H4zm0 5h16v2H4zm0 5h16v2H4z"/><path d="M6 7h2v2H6zm0 5h2v2H6zm0 5h2v2H6z"/></svg>
                            <span class="side-menu__label">إدارة الثوابت</span>
                        </a>
                    </li>

                    <li class="slide {{ $isActive('admin.settings.*') }}">
                        <a href="{{ route('admin.settings.index') }}" class="side-menu__item">
                            <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l2.03-1.58a.49.49 0 0 0 .12-.61l-1.92-3.32a.488.488 0 0 0-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94L14.4 2.81c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.05.3-.09.63-.09.94s.02.64.07.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"/></svg>
                            <span class="side-menu__label">إعدادات الموقع</span>
                        </a>
                    </li>

                    <li class="slide has-sub {{ $isOpen('admin.users.*') }}">
                        <a href="javascript:void(0);" class="side-menu__item {{ $isActive('admin.users.*') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__angle" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0zM12 14a7 7 0 0 0-7 7h14a7 7 0 0 0-7-7z"/></svg>
                            <span class="side-menu__label">المستخدمون</span>
                        </a>
                        <ul class="slide-menu child1" style="{{ $show('admin.users.*') }}">
                            <li class="slide">
                                <a href="{{ route('admin.users.index') }}" class="side-menu__item {{ $isActive('admin.users.index') }}">
                                    قائمة المستخدمين
                                </a>
                            </li>
                            <li class="slide">
                                <a href="{{ route('admin.users.create') }}" class="side-menu__item {{ $isActive('admin.users.create') }}">
                                    إضافة مستخدم جديد
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                {{-- إدارة العمليات --}}
                <li class="slide__category mt-3">
                    <span class="side-menu__label text-muted text-xs opacity-70">إدارة العمليات</span>
                </li>

                {{-- المشغلون --}}
                @can('viewAny', App\Models\Operator::class)
                    <li class="slide has-sub {{ $isOpen('admin.operators.*') || $isOpen('admin.users.*') || $isOpen('admin.operators.tariff-prices.*') ? 'open' : '' }}">
                        <a href="javascript:void(0);" class="side-menu__item {{ $isActive('admin.operators.*') || $isActive('admin.users.*') || $isActive('admin.operators.tariff-prices.*') ? 'active' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__angle" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M4 21h16v-2H4v2zm0-4h16v-2H4v2zm0-4h16v-2H4v2zm0-4h7V5H4v4zm9 0h7V5h-7v4z"/></svg>
                            <span class="side-menu__label">
                                {{ $u->isCompanyOwner() ? 'المشغل' : 'المشغلون' }}
                            </span>
                        </a>

                        <ul class="slide-menu child1" style="{{ ($show('admin.operators.*') || $show('admin.users.*') || $show('admin.operators.tariff-prices.*')) ? 'display:block' : '' }}">
                            @if($u->isCompanyOwner())
                                <li class="slide">
                                    <a href="{{ route('admin.operators.profile') }}" class="side-menu__item {{ $isActive('admin.operators.profile') }}">
                                        ملف المشغل
                                    </a>
                                </li>
                                <li class="slide">
                                    <a href="{{ route('admin.users.index') }}" class="side-menu__item {{ $isActive('admin.users.index') }}">
                                        الموظفون
                                    </a>
                                </li>
                                @can('create', App\Models\User::class)
                                    <li class="slide">
                                        <a href="{{ route('admin.users.create') }}" class="side-menu__item {{ $isActive('admin.users.create') }}">
                                            إضافة موظف/فني
                                        </a>
                                    </li>
                                @endcan
                                @can('viewAny', App\Models\ElectricityTariffPrice::class)
                                    <li class="slide">
                                        <a href="{{ route('admin.operators.tariff-prices.index', $u->ownedOperators()->first()) }}" class="side-menu__item {{ $isActive('admin.operators.tariff-prices.*') }}">
                                            أسعار التعرفة
                                        </a>
                                    </li>
                                @endcan
                            @else
                                <li class="slide">
                                    <a href="{{ route('admin.operators.index') }}" class="side-menu__item {{ $isActive('admin.operators.index') }}">
                                        قائمة المشغلين
                                    </a>
                                </li>
                                @can('create', App\Models\Operator::class)
                                    <li class="slide">
                                        <a href="{{ route('admin.operators.create') }}" class="side-menu__item {{ $isActive('admin.operators.create') }}">
                                            إضافة مشغل جديد
                                        </a>
                                    </li>
                                @endcan
                            @endif
                        </ul>
                    </li>
                @endcan

                {{-- المولدات + كل ما يتبعها --}}
                @can('viewAny', App\Models\Generator::class)
                    <li class="slide has-sub {{ request()->routeIs('admin.generators.*','admin.operation-logs.*','admin.fuel-efficiencies.*','admin.maintenance-records.*','admin.compliance-safeties.*') ? 'open' : '' }}">
                        <a href="javascript:void(0);" class="side-menu__item {{ request()->routeIs('admin.generators.*','admin.operation-logs.*','admin.fuel-efficiencies.*','admin.maintenance-records.*','admin.compliance-safeties.*') ? 'active' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__angle" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M13 2L3 14h7l-1 8 10-12h-7l1-8z"/></svg>
                            <span class="side-menu__label">المولدات</span>
                        </a>
                        <ul class="slide-menu child1" style="{{ request()->routeIs('admin.generators.*','admin.operation-logs.*','admin.fuel-efficiencies.*','admin.maintenance-records.*','admin.compliance-safeties.*') ? 'display:block' : '' }}">
                            <li class="slide">
                                <a href="{{ route('admin.generators.index') }}" class="side-menu__item {{ $isActive('admin.generators.index') }}">
                                    المولدات
                                </a>
                            </li>
                            <li class="slide">
                                <a href="{{ route('admin.operation-logs.index') }}" class="side-menu__item {{ $isActive('admin.operation-logs.index') }}">
                                    سجلات التشغيل
                                </a>
                            </li>
                            <li class="slide">
                                <a href="{{ route('admin.fuel-efficiencies.index') }}" class="side-menu__item {{ $isActive('admin.fuel-efficiencies.index') }}">
                                    كفاءة الوقود
                                </a>
                            </li>
                            <li class="slide">
                                <a href="{{ route('admin.maintenance-records.index') }}" class="side-menu__item {{ $isActive('admin.maintenance-records.index') }}">
                                    سجلات الصيانة
                                </a>
                            </li>
                            <li class="slide">
                                <a href="{{ route('admin.compliance-safeties.index') }}" class="side-menu__item {{ $isActive('admin.compliance-safeties.index') }}">
                                    الامتثال والسلامة
                                </a>
                            </li>
                        </ul>
                    </li>
                @endcan

                {{-- الشكاوى والمقترحات --}}
                <li class="slide {{ $isActive('admin.complaints-suggestions.*') }}">
                    <a href="{{ route('admin.complaints-suggestions.index') }}" class="side-menu__item">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-2 2V4h16v12z"/></svg>
                        <span class="side-menu__label">الشكاوى والمقترحات</span>
                    </a>
                </li>

            </ul>

            <div class="slide-right" id="slide-right">
                <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
                    <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z"></path>
                </svg>
            </div>

        </nav>
    </div>
</aside>
