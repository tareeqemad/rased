<!DOCTYPE html>
<html lang="ar" dir="rtl" data-nav-layout="vertical" data-theme-mode="light" data-header-styles="color" data-menu-styles="color" data-toggled="close">
<head>
    <!-- Meta Data -->
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'لوحة التحكم') - راصد</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('assets/admin/images/brand-logos/favicon.ico') }}" type="image/x-icon">

    <!-- Choices JS -->
    <script src="{{ asset('assets/admin/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>

    <!-- Main Theme Js -->
    <script src="{{ asset('assets/admin/js/main.js') }}"></script>

    <!-- Bootstrap Css -->
    <link id="bootstrap-style" href="{{ asset('assets/admin/libs/bootstrap/css/bootstrap.rtl.min.css') }}" rel="stylesheet">

    <!-- Style Css -->
    <link href="{{ asset('assets/admin/css/styles.min.css') }}" rel="stylesheet">

    <!-- Icons Css -->
    <link href="{{ asset('assets/admin/css/icons.css') }}" rel="stylesheet">
    <!-- Feather Icons Css (Direct Load) -->
    <link href="{{ asset('assets/admin/icon-fonts/feather/feather.css') }}" rel="stylesheet">

    <!-- Node Waves Css -->
    <link href="{{ asset('assets/admin/libs/node-waves/waves.min.css') }}" rel="stylesheet">

    <!-- Simplebar Css -->
    <link href="{{ asset('assets/admin/libs/simplebar/simplebar.min.css') }}" rel="stylesheet">

    <!-- Color Picker Css -->
    <link rel="stylesheet" href="{{ asset('assets/admin/libs/flatpickr/flatpickr.min.css') }}">

    <!-- Choices Css -->
    <link rel="stylesheet" href="{{ asset('assets/admin/libs/choices.js/public/assets/styles/choices.min.css') }}">

    <!-- Custom Css -->
    <link rel="stylesheet" href="{{ asset('assets/admin/css/custom.css') }}">

    @stack('styles')
</head>
<body>
    <!-- Loader -->
    <div id="loader">
        <img src="{{ asset('assets/admin/images/media/loader.svg') }}" alt="">
    </div>
    <!-- Loader -->

    <div class="page">
        <!-- app-header -->
        @include('admin.partials.header')
        <!-- /app-header -->

        <!-- Start::app-sidebar -->
        @include('admin.partials.sidebar')
        <!-- End::app-sidebar -->

        <!-- main-content -->
        <div class="main-content app-content">
            <!-- container -->
            <div class="main-container container-fluid">
                <!-- breadcrumb -->
                @include('admin.partials.breadcrumb', [
                    'title' => $breadcrumbTitle ?? '',
                    'parent' => $breadcrumbParent ?? null,
                    'parent_url' => $breadcrumbParentUrl ?? null
                ])
                <!-- /breadcrumb -->

                <!-- Flash Messages -->
                @include('admin.partials.toast')

                @yield('content')
            </div>
            <!-- Container closed -->
        </div>
        <!-- main-content closed -->

        <!-- Footer Start -->
        @include('admin.partials.footer')
        <!-- Footer End -->
    </div>

    <!-- Scroll To Top -->
    <div class="scrollToTop">
        <span class="arrow"><i class="bi bi-arrow-up fs-20"></i></span>
    </div>
    <div id="responsive-overlay"></div>
    <!-- Scroll To Top -->

    <!-- Popper JS -->
    <script src="{{ asset('assets/admin/libs/@popperjs/core/umd/popper.min.js') }}"></script>

    <!-- Bootstrap JS -->
    <script src="{{ asset('assets/admin/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <!-- Defaultmenu JS -->
    <script src="{{ asset('assets/admin/js/defaultmenu.min.js') }}"></script>

    <!-- Node Waves JS-->
    <script src="{{ asset('assets/admin/libs/node-waves/waves.min.js') }}"></script>

    <!-- Simplebar JS -->
    <script src="{{ asset('assets/admin/libs/simplebar/simplebar.min.js') }}"></script>

    <!-- Sticky JS -->
    <script src="{{ asset('assets/admin/js/sticky.js') }}"></script>

    <!-- Flatpickr JS -->
    <script src="{{ asset('assets/admin/libs/flatpickr/flatpickr.min.js') }}"></script>
    @if(file_exists(public_path('assets/admin/libs/flatpickr/l10n/ar.js')))
        <script src="{{ asset('assets/admin/libs/flatpickr/l10n/ar.js') }}"></script>
    @endif

    <!-- Admin Date Picker -->
    <script src="{{ asset('assets/admin/js/admin-datepicker.js') }}"></script>

    <!-- Custom JS -->
    <script src="{{ asset('assets/admin/js/custom.js') }}"></script>
    <script src="{{ asset('assets/admin/js/notifications.js') }}"></script>

    <!-- Flash Messages as Bootstrap Toasts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // عرض رسائل Flash كـ Bootstrap Toasts
            @if(session('success'))
                if (window.adminNotifications) {
                    window.adminNotifications.success('{{ session('success') }}');
                }
            @endif

            @if(session('error'))
                if (window.adminNotifications) {
                    window.adminNotifications.error('{{ session('error') }}');
                }
            @endif

            @if(session('warning'))
                if (window.adminNotifications) {
                    window.adminNotifications.warning('{{ session('warning') }}');
                }
            @endif

            @if(session('info'))
                if (window.adminNotifications) {
                    window.adminNotifications.info('{{ session('info') }}');
                }
            @endif

            @if($errors->any())
                @foreach($errors->all() as $error)
                    if (window.adminNotifications) {
                        window.adminNotifications.error('{{ $error }}');
                    }
                @endforeach
            @endif
        });
    </script>

    <!-- General Helpers JS -->
    <script src="{{ asset('assets/admin/js/general-helpers.js') }}"></script>

    @stack('scripts')
</body>
</html>

