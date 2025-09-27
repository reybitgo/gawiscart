<div class="sidebar sidebar-fixed border-end" id="sidebar">
    <div class="sidebar-header border-bottom">
        <div class="sidebar-brand">
            <!-- Dark theme logos (default) -->
            <img class="sidebar-brand-full logo-dark" src="{{ asset('coreui-template/assets/brand/gawis_logo.png') }}" width="110" height="39" alt="{{ config('app.name', 'Gawis iHerbal') }} Logo" />
            <img class="sidebar-brand-narrow logo-dark" src="{{ asset('coreui-template/assets/brand/gawis.png') }}" width="32" height="32" alt="{{ config('app.name', 'Gawis iHerbal') }} Logo" />
            <!-- Light theme logos -->
            <img class="sidebar-brand-full logo-light" src="{{ asset('coreui-template/assets/brand/gawis_logo_light.png') }}" width="110" height="39" alt="{{ config('app.name', 'Gawis iHerbal') }} Logo" />
            <img class="sidebar-brand-narrow logo-light" src="{{ asset('coreui-template/assets/brand/gawis_light.png') }}" width="32" height="32" alt="{{ config('app.name', 'Gawis iHerbal') }} Logo" />
        </div>
        <button class="btn-close d-lg-none" type="button" aria-label="Close" onclick='coreui.Sidebar.getInstance(document.querySelector("#sidebar")).toggle()'></button>
    </div>
    <ul class="sidebar-nav" data-coreui="navigation" data-simplebar="">
        <!-- Dashboard -->
        <li class="nav-item">
            <a class="nav-link{{ Request::routeIs('dashboard') ? ' active' : '' }}" href="{{ route('dashboard') }}">
                <svg class="nav-icon">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-speedometer') }}"></use>
                </svg>
                <span>Dashboard</span>
            </a>
        </li>

        @if(auth()->user()->hasRole('admin'))
            <!-- Admin Section -->
            <li class="nav-title">Administration</li>
            <li class="nav-item">
                <a class="nav-link{{ Request::routeIs('admin.dashboard') ? ' active' : '' }}" href="{{ route('admin.dashboard') }}">
                    <svg class="nav-icon">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-chart-pie') }}"></use>
                    </svg>
                    <span>Admin Dashboard</span>
                </a>
            </li>

            <!-- Admin Management Group -->
            <li class="nav-group">
                <a class="nav-link nav-group-toggle{{ Request::routeIs('admin.*') && !Request::routeIs('admin.dashboard') ? ' active' : '' }}" href="#">
                    <svg class="nav-icon">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-puzzle') }}"></use>
                    </svg>
                    <span>Management</span>
                </a>
                <ul class="nav-group-items compact">
                    @can('wallet_management')
                    <li class="nav-item">
                        <a class="nav-link{{ Request::routeIs('admin.wallet.*') ? ' active' : '' }}" href="{{ route('admin.wallet.management') }}">
                            <span class="nav-icon">
                                <span class="nav-icon-bullet"></span>
                            </span>
                            Wallet Management
                        </a>
                    </li>
                    @endcan
                    @can('transaction_approval')
                    <li class="nav-item">
                        <a class="nav-link{{ Request::routeIs('admin.transaction.*') ? ' active' : '' }}" href="{{ route('admin.transaction.approval') }}">
                            <span class="nav-icon">
                                <span class="nav-icon-bullet"></span>
                            </span>
                            Transaction Approval
                        </a>
                    </li>
                    @endcan
                    <li class="nav-item">
                        <a class="nav-link{{ Request::routeIs('admin.users') ? ' active' : '' }}" href="{{ route('admin.users') }}">
                            <span class="nav-icon">
                                <span class="nav-icon-bullet"></span>
                            </span>
                            User Management
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link{{ Request::routeIs('admin.packages.*') ? ' active' : '' }}" href="{{ route('admin.packages.index') }}">
                            <span class="nav-icon">
                                <span class="nav-icon-bullet"></span>
                            </span>
                            Package Management
                        </a>
                    </li>
                    @can('system_settings')
                    <li class="nav-item">
                        <a class="nav-link{{ Request::routeIs('admin.reports*') ? ' active' : '' }}" href="{{ route('admin.reports') }}">
                            <span class="nav-icon">
                                <span class="nav-icon-bullet"></span>
                            </span>
                            Reports & Analytics
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link{{ Request::routeIs('admin.logs') ? ' active' : '' }}" href="{{ route('admin.logs') }}">
                            <span class="nav-icon">
                                <span class="nav-icon-bullet"></span>
                            </span>
                            System Logs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link{{ Request::routeIs('admin.system.*') ? ' active' : '' }}" href="{{ route('admin.system.settings') }}">
                            <span class="nav-icon">
                                <span class="nav-icon-bullet"></span>
                            </span>
                            System Settings
                        </a>
                    </li>
                    @endcan
                </ul>
            </li>
        @endif

        <!-- E-Wallet Section -->
        <li class="nav-title">E-Wallet</li>
        <li class="nav-group">
            <a class="nav-link nav-group-toggle{{ Request::routeIs('wallet.*') ? ' active' : '' }}" href="#">
                <svg class="nav-icon">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-wallet') }}"></use>
                </svg>
                <span>Wallet Operations</span>
            </a>
            <ul class="nav-group-items compact">
                @can('deposit_funds')
                <li class="nav-item">
                    <a class="nav-link{{ Request::routeIs('wallet.deposit*') ? ' active' : '' }}" href="{{ route('wallet.deposit') }}">
                        <span class="nav-icon">
                            <span class="nav-icon-bullet"></span>
                        </span>
                        Deposit Funds
                    </a>
                </li>
                @endcan
                @can('transfer_funds')
                <li class="nav-item">
                    <a class="nav-link{{ Request::routeIs('wallet.transfer*') ? ' active' : '' }}" href="{{ route('wallet.transfer') }}">
                        <span class="nav-icon">
                            <span class="nav-icon-bullet"></span>
                        </span>
                        Transfer Funds
                    </a>
                </li>
                @endcan
                @can('withdraw_funds')
                <li class="nav-item">
                    <a class="nav-link{{ Request::routeIs('wallet.withdraw*') ? ' active' : '' }}" href="{{ route('wallet.withdraw') }}">
                        <span class="nav-icon">
                            <span class="nav-icon-bullet"></span>
                        </span>
                        Withdraw Funds
                    </a>
                </li>
                @endcan
                @can('view_transactions')
                <li class="nav-item">
                    <a class="nav-link{{ Request::routeIs('wallet.transactions*') ? ' active' : '' }}" href="{{ route('wallet.transactions') }}">
                        <span class="nav-icon">
                            <span class="nav-icon-bullet"></span>
                        </span>
                        Transaction History
                    </a>
                </li>
                @endcan
            </ul>
        </li>

        <!-- E-commerce Section -->
        <li class="nav-title">E-commerce</li>
        <li class="nav-group">
            <a class="nav-link nav-group-toggle{{ Request::routeIs('packages.*') ? ' active' : '' }}" href="#">
                <svg class="nav-icon">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-cart') }}"></use>
                </svg>
                <span>Shopping</span>
            </a>
            <ul class="nav-group-items compact">
                <li class="nav-item">
                    <a class="nav-link{{ Request::routeIs('packages.index') ? ' active' : '' }}" href="{{ route('packages.index') }}">
                        <span class="nav-icon">
                            <span class="nav-icon-bullet"></span>
                        </span>
                        Browse Packages
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" onclick="return false;">
                        <span class="nav-icon">
                            <span class="nav-icon-bullet"></span>
                        </span>
                        Shopping Cart
                        <span class="badge bg-warning ms-auto">Phase 2</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" onclick="return false;">
                        <span class="nav-icon">
                            <span class="nav-icon-bullet"></span>
                        </span>
                        Order History
                        <span class="badge bg-warning ms-auto">Phase 2</span>
                    </a>
                </li>
            </ul>
        </li>
    </ul>
    <div class="sidebar-footer border-top d-none d-lg-flex">
        <button class="sidebar-toggler" type="button" data-coreui-toggle="unfoldable"></button>
    </div>
</div>