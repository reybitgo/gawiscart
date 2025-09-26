<header class="header header-sticky p-0 mb-4">
    <div class="container-fluid px-4 border-bottom">
        <button class="header-toggler" type="button" onclick='coreui.Sidebar.getInstance(document.querySelector("#sidebar")).toggle()' style="margin-inline-start: -14px">
            <svg class="icon icon-lg">
                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-menu') }}"></use>
            </svg>
        </button>
        <ul class="header-nav d-none d-md-flex">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a>
            </li>
            @if(auth()->user()->hasRole('admin'))
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.users') }}">Users</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.system.settings') }}">Settings</a>
            </li>
            @endif
        </ul>
        <ul class="header-nav ms-auto">
            <!-- Username Display -->
            <li class="nav-item">
                <span class="nav-link px-2 text-body-secondary">
                    {{ auth()->user()->username }}
                </span>
            </li>
            <!-- User Profile -->
            <li class="nav-item dropdown">
                <a class="nav-link py-0 pe-0" data-coreui-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                    <div class="avatar rounded-circle" style="width: 32px; height: 32px;">
                        <div class="avatar-img bg-primary text-white d-flex align-items-center justify-content-center rounded-circle" style="width: 32px; height: 32px;">
                            <svg class="icon" style="width: 18px; height: 18px;">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-user') }}"></use>
                            </svg>
                        </div>
                    </div>
                </a>
                <div class="dropdown-menu dropdown-menu-end pt-0 w-auto">
                    <a class="dropdown-item" href="{{ route('profile.show') }}">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-user') }}"></use>
                        </svg>
                        <span>Profile</span>
                    </a>
                    <div class="dropdown-divider"></div>
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="dropdown-item">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-account-logout') }}"></use>
                            </svg>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </li>
        </ul>
    </div>
    <div class="container-fluid px-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb my-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">Home</a>
                </li>
                @if(isset($breadcrumbs) && count($breadcrumbs) > 0)
                    @foreach($breadcrumbs as $breadcrumb)
                        @if(isset($breadcrumb['url']) && !$loop->last)
                            <li class="breadcrumb-item">
                                <a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['title'] }}</a>
                            </li>
                        @else
                            <li class="breadcrumb-item active">
                                <span>{{ $breadcrumb['title'] }}</span>
                            </li>
                        @endif
                    @endforeach
                @else
                    <li class="breadcrumb-item active">
                        <span>@yield('page-title', 'Dashboard')</span>
                    </li>
                @endif
            </ol>
        </nav>
    </div>
</header>