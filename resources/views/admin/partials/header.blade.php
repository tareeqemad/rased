<header class="app-header">
    <div class="main-header-container container-fluid">
        <!-- Start::header-content-left -->
        <div class="header-content-left align-items-center">
            <div class="header-element">
                <div class="horizontal-logo">
                    <a href="{{ route('admin.dashboard') }}" class="header-logo">
                        <span class="logo-text">راصد</span>
                    </a>
                </div>
            </div>
            <div class="header-element">
                <a aria-label="Hide Sidebar" class="sidemenu-toggle header-link animated-arrow hor-toggle horizontal-navtoggle" data-bs-toggle="sidebar" href="javascript:void(0);">
                    <span></span>
                </a>
            </div>
        </div>
        <!-- End::header-content-left -->

        <!-- Start::header-content-right -->
        <div class="header-content-right">
            <div class="header-element">
                <a href="#" class="header-link dropdown-toggle" id="mainHeaderProfile" data-bs-toggle="dropdown">
                    <div class="d-flex align-items-center">
                        <div class="me-sm-2 me-0">
                            <div class="avatar-circle">{{ substr(Auth::user()->name, 0, 1) }}</div>
                        </div>
                        <div class="d-xl-block d-none">
                            <p class="fw-semibold mb-0 lh-1">{{ Auth::user()->name }}</p>
                            <span class="op-7 fw-normal d-block fs-11">
                                @if(Auth::user()->isSuperAdmin())
                                    مدير النظام
                                @elseif(Auth::user()->isCompanyOwner())
                                    صاحب مشغل
                                @else
                                    موظف
                                @endif
                            </span>
                        </div>
                    </div>
                </a>
                <div class="main-header-dropdown dropdown-menu dropdown-menu-end">
                    <div class="p-3 border-bottom">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <div class="avatar-circle-lg">{{ substr(Auth::user()->name, 0, 1) }}</div>
                            </div>
                            <div class="flex-grow-1">
                                <p class="fw-semibold mb-0">{{ Auth::user()->name }}</p>
                                <small class="text-muted">{{ Auth::user()->email }}</small>
                            </div>
                        </div>
                    </div>
                    <ul class="list-unstyled mb-0">
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i>
                                    تسجيل الخروج
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- End::header-content-right -->
    </div>
</header>

