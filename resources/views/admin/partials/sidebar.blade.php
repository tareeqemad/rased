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

                {{-- ============================================ --}}
                {{-- 1. لوحة التحكم --}}
                {{-- ============================================ --}}
                <li class="slide {{ $isActive('admin.dashboard') }}">
                    <a href="{{ route('admin.dashboard') }}" class="side-menu__item">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M3 13h1v7c0 1.103.897 2 2 2h12c1.103 0 2-.897 2-2v-7h1a1 1 0 0 0 .707-1.707l-9-9a.999.999 0 0 0-1.414 0l-9 9A1 1 0 0 0 3 13zm7 7v-5h4v5h-4zm2-15.586 6 6V15l.001 5H16v-5c0-1.103-.897-2-2-2h-4c-1.103 0-2 .897-2 2v5H6v-9.586l6-6z"/></svg>
                        <span class="side-menu__label">لوحة التحكم</span>
                    </a>
                </li>

                {{-- الدليل الإرشادي --}}
                <li class="slide {{ $isActive('admin.guide.*') }}">
                    <a href="{{ route('admin.guide.index') }}" class="side-menu__item">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                        <span class="side-menu__label">الدليل الإرشادي</span>
                    </a>
                </li>

                {{-- ============================================ --}}
                {{-- 2. إدارة النظام (SuperAdmin + EnergyAuthority) --}}
                {{-- ============================================ --}}
                @if($u->isSuperAdmin() || $u->isEnergyAuthority())
                    <li class="slide__category mt-3">
                        <span class="side-menu__label text-muted text-xs opacity-70">إدارة النظام</span>
                    </li>

                    {{-- المستخدمون --}}
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
                            @can('create', App\Models\User::class)
                                <li class="slide">
                                    <a href="{{ route('admin.users.create') }}" class="side-menu__item {{ $isActive('admin.users.create') }}">
                                        إضافة مستخدم جديد
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>

                    {{-- الأدوار والصلاحيات --}}
                    @php
                        $canViewRoles = $u->isSuperAdmin();
                        $canViewPermissions = $u->isSuperAdmin() || $u->isCompanyOwner() || $u->can('viewAny', \App\Models\Permission::class);
                        $canViewAuditLogs = $u->isSuperAdmin() || $u->isCompanyOwner() || $u->can('viewAny', \App\Models\PermissionAuditLog::class);
                        $canViewRolesPermissions = $canViewRoles || $canViewPermissions || $canViewAuditLogs;
                        $isRolesPermissionsOpen = $isOpen('admin.roles.*') || $isOpen('admin.permissions.*') || $isOpen('admin.permission-audit-logs.*');
                        $isRolesPermissionsActive = $isActive('admin.roles.*') || $isActive('admin.permissions.*') || $isActive('admin.permission-audit-logs.*');
                    @endphp
                    @if($canViewRolesPermissions)
                        <li class="slide has-sub {{ $isRolesPermissionsOpen ? 'open' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item {{ $isRolesPermissionsActive ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__angle" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M12 2L4 5v6.09c0 5.05 3.41 9.76 8 10.91 4.59-1.15 8-5.86 8-10.91V5l-8-3zm6 9.09c0 4-2.55 7.7-6 8.83-3.45-1.13-6-4.83-6-8.83V6.31l6-2.12 6 2.12v4.78z"/></svg>
                                <span class="side-menu__label">الأدوار والصلاحيات</span>
                            </a>
                            <ul class="slide-menu child1" style="{{ ($show('admin.roles.*') || $show('admin.permissions.*') || $show('admin.permission-audit-logs.*')) ? 'display:block' : '' }}">
                                @if($canViewRoles)
                                    <li class="slide has-sub {{ $isOpen('admin.roles.*') ? 'open' : '' }}">
                                        <a href="javascript:void(0);" class="side-menu__item {{ $isActive('admin.roles.*') }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__angle" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                                            الأدوار
                                        </a>
                                        <ul class="slide-menu child2" style="{{ $show('admin.roles.*') }}">
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
                                @endif
                                @if($canViewPermissions)
                                    <li class="slide">
                                        <a href="{{ route('admin.permissions.index') }}" class="side-menu__item {{ $isActive('admin.permissions.index') }}">
                                            شجرة الصلاحيات
                                        </a>
                                    </li>
                                @endif
                                @if($canViewAuditLogs)
                                    <li class="slide">
                                        <a href="{{ route('admin.permission-audit-logs.index') }}" class="side-menu__item {{ $isActive('admin.permission-audit-logs.*') }}">
                                            سجل التغييرات
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endif

                    {{-- الأرقام المصرح بها --}}
                    @can('viewAny', App\Models\AuthorizedPhone::class)
                        <li class="slide {{ $isActive('admin.authorized-phones.*') }}">
                            <a href="{{ route('admin.authorized-phones.index') }}" class="side-menu__item">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>
                                <span class="side-menu__label">الأرقام المصرح بها</span>
                            </a>
                        </li>
                    @endcan

                    {{-- الإعدادات (SuperAdmin فقط) --}}
                    @php
                        $canViewSettings = $u->isSuperAdmin();
                        $canViewConstants = $u->isSuperAdmin();
                        $canViewLogs = $u->isSuperAdmin() || $u->hasPermission('logs.view');
                        $canViewSystemSettings = $canViewSettings || $canViewConstants || $canViewLogs;
                        $isSystemSettingsOpen = $isOpen('admin.settings.*') || $isOpen('admin.constants.*') || $isOpen('admin.logs.*');
                        $isSystemSettingsActive = $isActive('admin.settings.*') || $isActive('admin.constants.*') || $isActive('admin.logs.*');
                    @endphp
                    @if($canViewSystemSettings)
                        <li class="slide has-sub {{ $isSystemSettingsOpen ? 'open' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item {{ $isSystemSettingsActive ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__angle" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l2.03-1.58a.49.49 0 0 0 .12-.61l-1.92-3.32a.488.488 0 0 0-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94L14.4 2.81c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.05.3-.09.63-.09.94s.02.64.07.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"/></svg>
                                <span class="side-menu__label">إعدادات النظام</span>
                            </a>
                            <ul class="slide-menu child1" style="{{ ($show('admin.settings.*') || $show('admin.constants.*') || $show('admin.logs.*')) ? 'display:block' : '' }}">
                                @if($canViewSettings)
                                    <li class="slide">
                                        <a href="{{ route('admin.settings.index') }}" class="side-menu__item {{ $isActive('admin.settings.*') }}">
                                            إعدادات الموقع
                                        </a>
                                    </li>
                                @endif
                                @if($canViewConstants)
                                    <li class="slide">
                                        <a href="{{ route('admin.constants.index') }}" class="side-menu__item {{ $isActive('admin.constants.*') }}">
                                            إدارة الثوابت
                                        </a>
                                    </li>
                                @endif
                                @if($canViewLogs)
                                    <li class="slide">
                                        <a href="{{ route('admin.logs.index') }}" class="side-menu__item {{ $isActive('admin.logs.*') }}">
                                            سجل الأخطاء
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endif

                    {{-- الرسائل (الترحيبية + قوالب SMS) --}}
                    @php
                        $canViewWelcomeMessages = auth()->user()->can('viewAny', App\Models\WelcomeMessage::class);
                        $canViewSmsTemplates = auth()->user()->can('viewAny', App\Models\SmsTemplate::class);
                        $canViewMessages = $canViewWelcomeMessages || $canViewSmsTemplates;
                        $isMessagesOpen = $isOpen('admin.welcome-messages.*') || $isOpen('admin.sms-templates.*');
                        $isMessagesActive = $isActive('admin.welcome-messages.*') || $isActive('admin.sms-templates.*');
                    @endphp
                    @if($canViewMessages)
                        <li class="slide has-sub {{ $isMessagesOpen ? 'open' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item {{ $isMessagesActive ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__angle" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-2 2V4h16v12z"/></svg>
                                <span class="side-menu__label">الرسائل</span>
                            </a>
                            <ul class="slide-menu child1" style="{{ ($show('admin.welcome-messages.*') || $show('admin.sms-templates.*')) ? 'display:block' : '' }}">
                                @if($canViewWelcomeMessages)
                                    <li class="slide">
                                        <a href="{{ route('admin.welcome-messages.index') }}" class="side-menu__item {{ $isActive('admin.welcome-messages.index') }}">
                                            الرسائل الترحيبية
                                        </a>
                                    </li>
                                @endif
                                @if($canViewSmsTemplates)
                                    <li class="slide">
                                        <a href="{{ route('admin.sms-templates.index') }}" class="side-menu__item {{ $isActive('admin.sms-templates.index') }}">
                                            قوالب SMS
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endif
                @endif

                {{-- ============================================ --}}
                {{-- 3. إدارة العمليات (لجميع المستخدمين المصرح لهم) --}}
                {{-- ============================================ --}}
                <li class="slide__category mt-3">
                    <span class="side-menu__label text-muted text-xs opacity-70">إدارة العمليات</span>
                </li>

                {{-- وحدات التوليد --}}
                @can('viewAny', App\Models\GenerationUnit::class)
                    <li class="slide {{ $isActive('admin.generation-units.*') }}">
                        <a href="{{ route('admin.generation-units.index') }}" class="side-menu__item">
                            <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5zm0 2.18l8 4v8.82c0 4.54-3.07 8.86-8 9.82-4.93-.96-8-5.28-8-9.82V8.18l8-4z"/><path d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm0 6c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"/></svg>
                            <span class="side-menu__label">وحدات التوليد</span>
                        </a>
                    </li>
                @endcan

                {{-- المولدات --}}
                @can('viewAny', App\Models\Generator::class)
                    <li class="slide has-sub {{ request()->routeIs('admin.generators.*') ? 'open' : '' }}">
                        <a href="javascript:void(0);" class="side-menu__item {{ request()->routeIs('admin.generators.*') ? 'active' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__angle" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M13 2L3 14h7l-1 8 10-12h-7l1-8z"/></svg>
                            <span class="side-menu__label">المولدات</span>
                        </a>
                        <ul class="slide-menu child1" style="{{ request()->routeIs('admin.generators.*') ? 'display:block' : '' }}">
                            <li class="slide">
                                <a href="{{ route('admin.generators.index') }}" class="side-menu__item {{ $isActive('admin.generators.index') }}">
                                    قائمة المولدات
                                </a>
                            </li>
                            @can('create', App\Models\Generator::class)
                                <li class="slide">
                                    <a href="{{ route('admin.generators.create') }}" class="side-menu__item {{ $isActive('admin.generators.create') }}">
                                        إضافة مولد جديد
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcan

                {{-- السجلات --}}
                @php
                    $canViewOperationLogs = auth()->user()->can('viewAny', App\Models\OperationLog::class);
                    $canViewFuelEfficiencies = auth()->user()->can('viewAny', App\Models\FuelEfficiency::class);
                    $canViewMaintenanceRecords = auth()->user()->can('viewAny', App\Models\MaintenanceRecord::class);
                    $canViewComplianceSafeties = auth()->user()->can('viewAny', App\Models\ComplianceSafety::class);
                    $canViewRecords = $canViewOperationLogs || $canViewFuelEfficiencies || $canViewMaintenanceRecords || $canViewComplianceSafeties;
                    $isRecordsOpen = $isOpen('admin.operation-logs.*') || $isOpen('admin.fuel-efficiencies.*') || $isOpen('admin.maintenance-records.*') || $isOpen('admin.compliance-safeties.*');
                    $isRecordsActive = $isActive('admin.operation-logs.*') || $isActive('admin.fuel-efficiencies.*') || $isActive('admin.maintenance-records.*') || $isActive('admin.compliance-safeties.*');
                @endphp
                @if($canViewRecords)
                    <li class="slide has-sub {{ $isRecordsOpen ? 'open' : '' }}">
                        <a href="javascript:void(0);" class="side-menu__item {{ $isRecordsActive ? 'active' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__angle" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
                            <span class="side-menu__label">السجلات</span>
                        </a>
                        <ul class="slide-menu child1" style="{{ ($show('admin.operation-logs.*') || $show('admin.fuel-efficiencies.*') || $show('admin.maintenance-records.*') || $show('admin.compliance-safeties.*')) ? 'display:block' : '' }}">
                            @if($canViewOperationLogs)
                                <li class="slide">
                                    <a href="{{ route('admin.operation-logs.index') }}" class="side-menu__item {{ $isActive('admin.operation-logs.*') }}">
                                        سجلات التشغيل
                                    </a>
                                </li>
                            @endif
                            @if($canViewFuelEfficiencies)
                                <li class="slide">
                                    <a href="{{ route('admin.fuel-efficiencies.index') }}" class="side-menu__item {{ $isActive('admin.fuel-efficiencies.*') }}">
                                        كفاءة الوقود
                                    </a>
                                </li>
                            @endif
                            @if($canViewMaintenanceRecords)
                                <li class="slide">
                                    <a href="{{ route('admin.maintenance-records.index') }}" class="side-menu__item {{ $isActive('admin.maintenance-records.*') }}">
                                        سجلات الصيانة
                                    </a>
                                </li>
                            @endif
                            @if($canViewComplianceSafeties)
                                <li class="slide">
                                    <a href="{{ route('admin.compliance-safeties.index') }}" class="side-menu__item {{ $isActive('admin.compliance-safeties.*') }}">
                                        الامتثال والسلامة
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif


                {{-- ============================================ --}}
                {{-- 4. المشغل (للمشغل فقط) --}}
                {{-- ============================================ --}}
                @if($u->isCompanyOwner())
                    <li class="slide__category mt-3">
                        <span class="side-menu__label text-muted text-xs opacity-70">المشغل</span>
                    </li>

                    {{-- ملف المشغل (رابط مباشر) --}}
                    <li class="slide {{ $isActive('admin.operators.profile') }}">
                        <a href="{{ route('admin.operators.profile') }}" class="side-menu__item">
                            <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                            <span class="side-menu__label">ملف المشغل</span>
                        </a>
                    </li>

                    {{-- إدارة المشغل (قائمة فرعية) --}}
                    <li class="slide has-sub {{ $isOpen('admin.generation-units.*') || $isOpen('admin.users.*') || $isOpen('admin.operators.tariff-prices.*') ? 'open' : '' }}">
                        <a href="javascript:void(0);" class="side-menu__item {{ $isActive('admin.generation-units.*') || $isActive('admin.users.*') || $isActive('admin.operators.tariff-prices.*') ? 'active' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__angle" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M4 21h16v-2H4v2zm0-4h16v-2H4v2zm0-4h16v-2H4v2zm0-4h7V5H4v4zm9 0h7V5h-7v4z"/></svg>
                            <span class="side-menu__label">إدارة المشغل</span>
                        </a>
                        <ul class="slide-menu child1" style="{{ ($show('admin.generation-units.*') || $show('admin.users.*') || $show('admin.operators.tariff-prices.*')) ? 'display:block' : '' }}">
                            @can('viewAny', App\Models\GenerationUnit::class)
                                <li class="slide">
                                    <a href="{{ route('admin.generation-units.index') }}" class="side-menu__item {{ $isActive('admin.generation-units.*') }}">
                                        وحدات التوليد
                                    </a>
                                </li>
                            @endcan
                            <li class="slide">
                                <a href="{{ route('admin.users.index') }}" class="side-menu__item {{ $isActive('admin.users.index') }}">
                                    الموظفون والفنيون
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
                        </ul>
                    </li>

                    {{-- الأدوار المخصصة (للمشغل) --}}
                    @if($u->isCompanyOwner())
                        <li class="slide {{ $isActive('admin.roles.*') }}">
                            <a href="{{ route('admin.roles.index') }}" class="side-menu__item">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M12 2L4 5v6.09c0 5.05 3.41 9.76 8 10.91 4.59-1.15 8-5.86 8-10.91V5l-8-3zm6 9.09c0 4-2.55 7.7-6 8.83-3.45-1.13-6-4.83-6-8.83V6.31l6-2.12 6 2.12v4.78z"/></svg>
                                <span class="side-menu__label">الأدوار المخصصة</span>
                            </a>
                        </li>
                    @endif
                @endif

                {{-- ============================================ --}}
                {{-- 5. التواصل والرسائل --}}
                {{-- ============================================ --}}
                @can('viewAny', App\Models\Message::class)
                    <li class="slide__category mt-3">
                        <span class="side-menu__label text-muted text-xs opacity-70">التواصل</span>
                    </li>

                    <li class="slide {{ $isActive('admin.messages.*') }}">
                        <a href="{{ route('admin.messages.index') }}" class="side-menu__item">
                            <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-2 2V4h16v12z"/></svg>
                            <span class="side-menu__label">الرسائل</span>
                        </a>
                    </li>
                @endcan

                {{-- ============================================ --}}
                {{-- 6. الشكاوى والمقترحات --}}
                {{-- ============================================ --}}
                <li class="slide__category mt-3">
                    <span class="side-menu__label text-muted text-xs opacity-70">خدمات</span>
                </li>

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
