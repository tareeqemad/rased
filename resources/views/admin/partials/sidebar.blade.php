<aside class="app-sidebar sticky" id="sidebar">
    <div class="main-sidebar-header">
        <a href="{{ route('admin.dashboard') }}" class="header-logo">
            <img src="{{ asset('assets/admin/images/brand-logos/logo_white.webp') }}" alt="logo" class="desktop-logo">
            <img src="{{ asset('assets/admin/images/brand-logos/toggle-logo.png') }}" alt="logo" class="toggle-logo">
            <img src="{{ asset('assets/admin/images/brand-logos/logo_dark.webp') }}" alt="logo" class="desktop-dark">
            <img src="{{ asset('assets/admin/images/brand-logos/toggle-dark.png') }}" alt="logo" class="toggle-dark">
            <img src="{{ asset('assets/admin/images/brand-logos/logo_white.webp') }}" alt="logo" class="desktop-white">
            <img src="{{ asset('assets/admin/images/brand-logos/toggle-white.png') }}" alt="logo" class="toggle-white">
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
                        <i class="bi bi-house-door side-menu__icon"></i>
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
                            <i class="bi bi-diagram-3 side-menu__icon"></i>
                            <span class="side-menu__label">شجرة الصلاحيات</span>
                        </a>
                    </li>
                @endif

                {{-- إدارة النظام: SuperAdmin فقط (الثوابت/الأدوار/المستخدمين الكلّي) --}}
                @if($u->isSuperAdmin())
                    <li class="slide__category mt-3">
                        <span class="side-menu__label text-muted text-xs opacity-70">إدارة النظام</span>
                    </li>

                    <li class="slide has-sub {{ $isOpen('admin.roles.*') }}">
                        <a href="javascript:void(0);" class="side-menu__item {{ $isActive('admin.roles.*') }}">
                            <i class="bi bi-shield-check side-menu__icon"></i>
                            <span class="side-menu__label">الأدوار</span>
                            <i class="bi bi-chevron-left side-menu__angle"></i>
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
                            <i class="bi bi-database side-menu__icon"></i>
                            <span class="side-menu__label">إدارة الثوابت</span>
                        </a>
                    </li>

                    <li class="slide has-sub {{ $isOpen('admin.users.*') }}">
                        <a href="javascript:void(0);" class="side-menu__item {{ $isActive('admin.users.*') }}">
                            <i class="bi bi-people side-menu__icon"></i>
                            <span class="side-menu__label">المستخدمون</span>
                            <i class="bi bi-chevron-left side-menu__angle"></i>
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
                    <li class="slide has-sub {{ $isOpen('admin.operators.*') || $isOpen('admin.users.*') ? 'open' : '' }}">
                        <a href="javascript:void(0);" class="side-menu__item {{ $isActive('admin.operators.*') || $isActive('admin.users.*') ? 'active' : '' }}">
                            <i class="bi bi-building side-menu__icon"></i>
                            <span class="side-menu__label">
                                {{ $u->isCompanyOwner() ? 'المشغل' : 'المشغلون' }}
                            </span>
                            <i class="bi bi-chevron-left side-menu__angle"></i>
                        </a>

                        <ul class="slide-menu child1" style="{{ ($show('admin.operators.*') || $show('admin.users.*')) ? 'display:block' : '' }}">
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
                            <i class="bi bi-lightning-charge side-menu__icon"></i>
                            <span class="side-menu__label">المولدات</span>
                            <i class="bi bi-chevron-left side-menu__angle"></i>
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

            </ul>

            <div class="slide-right" id="slide-right">
                <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
                    <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z"></path>
                </svg>
            </div>

        </nav>
    </div>
</aside>
