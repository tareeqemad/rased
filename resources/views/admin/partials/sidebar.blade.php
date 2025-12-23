<aside class="app-sidebar sticky" id="sidebar">
    <!-- Start::main-sidebar-header -->
    <div class="main-sidebar-header">
        <a href="{{ route('admin.dashboard') }}" class="header-logo">
            <span class="logo-text">راصد</span>
        </a>
    </div>
    <!-- End::main-sidebar-header -->

    <!-- Start::main-sidebar -->
    <div class="main-sidebar" id="sidebar-scroll">
        <nav class="main-menu-container nav nav-pills flex-column sub-open">
            <div class="slide-left" id="slide-left">
                <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
                    <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z"></path>
                </svg>
            </div>

            @php
                $isActive = fn($routes) => request()->routeIs($routes) ? 'active' : '';
                $isOpen   = fn($routes) => request()->routeIs($routes) ? 'open'   : '';
                $show     = fn($routes) => request()->routeIs($routes) ? 'display:block' : '';
                $hasActiveSubmenu = request()->routeIs('admin.generators.*') || 
                                    request()->routeIs('admin.operation-logs.*') || 
                                    request()->routeIs('admin.fuel-efficiencies.*') || 
                                    request()->routeIs('admin.maintenance-records.*') || 
                                    request()->routeIs('admin.compliance-safeties.*');
            @endphp

            <ul class="main-menu">
                <!-- Dashboard -->
                <li class="slide {{ $isActive('admin.dashboard') }}">
                    <a href="{{ route('admin.dashboard') }}" class="side-menu__item">
                        <i class="bi bi-house-door side-menu__icon"></i>
                        <span class="side-menu__label">لوحة التحكم</span>
                    </a>
                </li>

                <!-- Permissions Tree -->
                <li class="slide {{ $isActive('admin.permissions.*') }}">
                    <a href="{{ route('admin.permissions.index') }}" class="side-menu__item">
                        <i class="bi bi-diagram-3 side-menu__icon"></i>
                        <span class="side-menu__label">شجرة الصلاحيات</span>
                    </a>
                </li>

                <!-- Users (Super Admin Only) -->
                @can('viewAny', App\Models\User::class)
                    <li class="slide {{ $isActive('admin.users.*') }}">
                        <a href="{{ route('admin.users.index') }}" class="side-menu__item">
                            <i class="bi bi-people side-menu__icon"></i>
                            <span class="side-menu__label">المستخدمون</span>
                        </a>
                    </li>
                @endcan

                <!-- Operators -->
                @can('viewAny', App\Models\Operator::class)
                    <li class="slide {{ $isActive('admin.operators.*') }}">
                        <a href="{{ route('admin.operators.index') }}" class="side-menu__item">
                            <i class="bi bi-building side-menu__icon"></i>
                            <span class="side-menu__label">المشغلون</span>
                        </a>
                    </li>
                @endcan

                <!-- Generators with Submenu -->
                @can('viewAny', App\Models\Generator::class)
                    <li class="slide has-sub {{ $hasActiveSubmenu ? 'open' : '' }}">
                        <a href="javascript:void(0);" class="side-menu__item {{ $hasActiveSubmenu ? 'active' : '' }}">
                            <i class="bi bi-lightning-charge side-menu__icon"></i>
                            <span class="side-menu__label">المولدات</span>
                            <i class="angle bi bi-chevron-left"></i>
                        </a>
                        <ul class="slide-menu child1" style="{{ $hasActiveSubmenu ? 'display:block' : '' }}">
                            <li class="slide {{ $isActive('admin.generators.*') }}">
                                <a href="{{ route('admin.generators.index') }}" class="side-menu__item">
                                    <span class="side-menu__label">المولدات</span>
                                </a>
                            </li>
                            <li class="slide {{ $isActive('admin.operation-logs.*') }}">
                                <a href="{{ route('admin.operation-logs.index') }}" class="side-menu__item">
                                    <span class="side-menu__label">سجلات التشغيل</span>
                                </a>
                            </li>
                            <li class="slide {{ $isActive('admin.fuel-efficiencies.*') }}">
                                <a href="{{ route('admin.fuel-efficiencies.index') }}" class="side-menu__item">
                                    <span class="side-menu__label">كفاءة الوقود</span>
                                </a>
                            </li>
                            <li class="slide {{ $isActive('admin.maintenance-records.*') }}">
                                <a href="{{ route('admin.maintenance-records.index') }}" class="side-menu__item">
                                    <span class="side-menu__label">سجلات الصيانة</span>
                                </a>
                            </li>
                            <li class="slide {{ $isActive('admin.compliance-safeties.*') }}">
                                <a href="{{ route('admin.compliance-safeties.index') }}" class="side-menu__item">
                                    <span class="side-menu__label">الامتثال والسلامة</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endcan
            </ul>

            <div class="slide-right" id="slide-right">
                <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
                    <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z"></path>
                </svg>
            </div>
        </nav>
    </div>
</aside>

