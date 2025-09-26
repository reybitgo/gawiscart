<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'Gawis iHerbal') }}</title>
    <meta name="description" content="@yield('description', 'Gawis iHerbal E-Wallet Admin Dashboard')">
    <meta name="author" content="{{ config('app.name', 'Gawis iHerbal') }}">
    <meta name="keyword" content="E-Wallet,Digital Wallet,Financial Management,Transaction,Payment">

    <!-- Favicon and PWA Icons -->
    <link rel="apple-touch-icon" sizes="57x57" href="{{ asset('coreui-template/assets/favicon/apple-icon-57x57.png') }}">
    <link rel="apple-touch-icon" sizes="60x60" href="{{ asset('coreui-template/assets/favicon/apple-icon-60x60.png') }}">
    <link rel="apple-touch-icon" sizes="72x72" href="{{ asset('coreui-template/assets/favicon/apple-icon-72x72.png') }}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('coreui-template/assets/favicon/apple-icon-76x76.png') }}">
    <link rel="apple-touch-icon" sizes="114x114" href="{{ asset('coreui-template/assets/favicon/apple-icon-114x114.png') }}">
    <link rel="apple-touch-icon" sizes="120x120" href="{{ asset('coreui-template/assets/favicon/apple-icon-120x120.png') }}">
    <link rel="apple-touch-icon" sizes="144x144" href="{{ asset('coreui-template/assets/favicon/apple-icon-144x144.png') }}">
    <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('coreui-template/assets/favicon/apple-icon-152x152.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('coreui-template/assets/favicon/apple-icon-180x180.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('coreui-template/assets/favicon/android-icon-192x192.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('coreui-template/assets/favicon/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('coreui-template/assets/favicon/favicon-96x96.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('coreui-template/assets/favicon/favicon-16x16.png') }}">
    <link rel="manifest" href="{{ asset('coreui-template/assets/favicon/manifest.json') }}">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="{{ asset('coreui-template/assets/favicon/apple-icon-144x144.png') }}">
    <meta name="theme-color" content="#ffffff">

    <!-- CoreUI CSS -->
    <link href="{{ asset('coreui-template/vendors/simplebar/css/simplebar.css') }}" rel="stylesheet">
    <link href="{{ asset('coreui-template/vendors/@coreui/chartjs/css/coreui-chartjs.css') }}" rel="stylesheet">
    <link href="{{ asset('coreui-template/css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('coreui-template/vendors/@coreui/icons/css/free.min.css') }}" rel="stylesheet">
    <link href="{{ asset('coreui-template/vendors/@coreui/icons/css/flag.min.css') }}" rel="stylesheet">
    <link href="{{ asset('coreui-template/vendors/@coreui/icons/css/brand.min.css') }}" rel="stylesheet">

    <!-- Additional CSS -->
    @stack('styles')
</head>
<body>
    @include('partials.sidebar')

    <div class="wrapper d-flex flex-column min-vh-100">
        @include('partials.header')

        <div class="body flex-grow-1">
            <div class="container-lg h-auto px-4">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-coreui-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-coreui-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-x') }}"></use>
                        </svg>
                        @if($errors->count() === 1)
                            {{ $errors->first() }}
                        @else
                            <div class="fw-bold mb-2">Please correct the following issues:</div>
                            @foreach($errors->all() as $error)
                                <div class="mb-1">• {{ $error }}</div>
                            @endforeach
                        @endif
                        <button type="button" class="btn-close" data-coreui-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>

        <footer class="footer p-0">
            <div class="container-lg text-center text-body-secondary py-3">
                © {{ date('Y') }} {{ config('app.name', 'Gawis iHerbal') }}. All rights reserved.
            </div>
        </footer>
    </div>

    <!-- CoreUI and Vendors JS -->
    <script src="{{ asset('coreui-template/vendors/@coreui/coreui-pro/js/coreui.bundle.min.js') }}"></script>
    <script src="{{ asset('coreui-template/vendors/simplebar/js/simplebar.min.js') }}"></script>
    <script src="{{ asset('coreui-template/vendors/chart.js/js/chart.umd.js') }}"></script>
    <script src="{{ asset('coreui-template/vendors/@coreui/chartjs/js/coreui-chartjs.js') }}"></script>
    <script src="{{ asset('coreui-template/vendors/@coreui/utils/js/index.js') }}"></script>
    <script src="{{ asset('coreui-template/js/main.js') }}"></script>

    <!-- CoreUI Initialization -->
    <script>
        const header = document.querySelector("header.header");

        document.addEventListener("scroll", () => {
            if (header) {
                header.classList.toggle(
                    "shadow-sm",
                    document.documentElement.scrollTop > 0
                );
            }
        });

        // Initialize CoreUI theme system
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Initializing CoreUI theme system...');

            // Force light mode only
            const storedTheme = 'light';
            console.log('Forced theme to light mode');

            // Set initial theme to light
            document.documentElement.setAttribute('data-coreui-theme', 'light');

            // Initialize theme switcher buttons
            const themeButtons = document.querySelectorAll('[data-coreui-theme-value]');
            console.log('Found theme buttons:', themeButtons.length);

            themeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const theme = this.getAttribute('data-coreui-theme-value');
                    console.log('Theme button clicked:', theme);

                    // Set theme attribute
                    document.documentElement.setAttribute('data-coreui-theme', theme);

                    // Store in localStorage
                    localStorage.setItem('coreui-theme', theme);

                    // Dispatch custom event for theme change
                    const event = new CustomEvent('ColorSchemeChange', {
                        detail: { theme: theme }
                    });
                    document.documentElement.dispatchEvent(event);

                    console.log('Theme changed to:', theme);
                });
            });

            // Update active button state
            function updateActiveThemeButton(theme) {
                themeButtons.forEach(btn => {
                    btn.classList.remove('active');
                    if (btn.getAttribute('data-coreui-theme-value') === theme) {
                        btn.classList.add('active');
                    }
                });
            }

            // Set initial active state
            updateActiveThemeButton(storedTheme);

            // Listen for theme changes to update button states
            document.documentElement.addEventListener('ColorSchemeChange', (e) => {
                updateActiveThemeButton(e.detail.theme);
                updateLogosForTheme(e.detail.theme);
            });

            // Function to ensure logos are properly displayed for the current theme
            function updateLogosForTheme(theme) {
                const darkLogos = document.querySelectorAll('.logo-dark');
                const lightLogos = document.querySelectorAll('.logo-light');

                // Check if we should use dark theme (either explicitly dark or auto with dark system preference)
                const shouldUseDarkTheme = theme === 'dark' ||
                    (theme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches);

                if (shouldUseDarkTheme) {
                    darkLogos.forEach(logo => logo.style.display = 'none');
                    lightLogos.forEach(logo => logo.style.display = '');
                } else {
                    darkLogos.forEach(logo => logo.style.display = '');
                    lightLogos.forEach(logo => logo.style.display = 'none');
                }
            }

            // Initialize logos for light theme only
            updateLogosForTheme('light');

            // Listen for system theme changes when in auto mode
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
                const currentTheme = localStorage.getItem('coreui-theme') || 'light';
                if (currentTheme === 'auto') {
                    updateLogosForTheme('auto');
                }
            });

            console.log('CoreUI theme system initialized');

            // Initialize sidebar to ensure it starts in full width mode
            // CoreUI's unfoldable toggle will handle the narrow mode with hover expand
            const sidebar = document.getElementById('sidebar');
            if (sidebar) {
                // Remove any narrow classes that might be present
                sidebar.classList.remove('sidebar-narrow');
                sidebar.classList.remove('sidebar-narrow-unfoldable');
                console.log('Sidebar initialized in full width mode for CoreUI unfoldable toggle');
            }
        });
    </script>

    <!-- Additional JavaScript -->
    @stack('scripts')
</body>
</html>