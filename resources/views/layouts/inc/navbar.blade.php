<!-- Navbar -->
<header class="navbar navbar-expand-md d-print-none sticky-top">
    <div class="container-xl">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu" aria-controls="navbar-menu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <h1 class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3">
            <a href="{{ route('dashboard') }}">
                <i class="bi bi-gift-fill" style="font-size: 1.5rem; color: #0066cc;"></i>
                <span class="d-none d-md-inline">Mall Lucky Draw</span>
            </a>
        </h1>
        <div class="navbar-nav flex-row order-md-last">
            <div class="nav-item d-none d-md-flex me-3">
                <div class="btn-list flex-nowrap">
                    <a href="https://github.com/nachad0ng/undian-mall" class="btn btn-icon" target="_blank" rel="noreferrer" title="Source Code">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M9 19c-4.3 1.4 -4.3 -2.5 -6 -3m12 -4v4m0 0v4m0 -11v-4m0 0a1 1 0 0 0 -1 -1h-4a1 1 0 0 0 -1 1m0 0v4a1 1 0 0 0 1 1h4a1 1 0 0 0 1 -1m6 -1a1 1 0 0 0 1 -1v-4a1 1 0 0 0 -1 -1m0 0v-4a1 1 0 0 0 -1 -1h-4a1 1 0 0 0 -1 1m-9 9a1 1 0 0 0 1 1h4a1 1 0 0 0 1 -1m0 0v-4a1 1 0 0 0 -1 -1h-4a1 1 0 0 0 -1 1m0 4v4"/>
                        </svg>
                    </a>
                </div>
            </div>
            <div class="nav-item dropdown">
                <a 
                    href="#navbar-notifications" 
                    class="nav-link px-0" 
                    data-bs-toggle="dropdown" 
                    aria-expanded="false" 
                    role="button" 
                    aria-label="Show notifications"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        class="icon icon-1"
                    >
                        <path d="M10 5a2 2 0 1 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6" />
                        <path d="M9 17v1a3 3 0 0 0 6 0v-1" />
                    </svg>
                    <span class="badge bg-red"></span>
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-card">
                    <div class="card">
                        <div class="card-body">
                            No new notifications
                        </div>
                    </div>
                </div>
            </div>
            <div class="nav-item dropdown">
                <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Open user menu">
                    <span class="avatar avatar-sm" style="background-image: url(https://avatars.githubusercontent.com/u/66051499?v=4)"></span>
                    <div class="d-none d-xl-block ps-2">
                        <div>{{ auth()->user()->name }}</div>
                        <div class="mt-1 small text-secondary">{{ auth()->user()->getRoleNames()->first() ?? 'User' }}</div>
                    </div>
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <a href="#" class="dropdown-item">Profile</a>
                    <a href="#" class="dropdown-item">Settings</a>
                    <div class="dropdown-divider"></div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item">Sign out</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>

<header class="navbar-expand-md">
    <div class="collapse navbar-collapse" id="navbar-menu">
        <div class="navbar">
            <div class="container-xl">
                <ul class="navbar-nav">
                    <li class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('dashboard') }}" role="button">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <polyline points="12 3 20 7.5 20 16.5 12 21 4 16.5 4 7.5 12 3"/>
                                    <line x1="12" y1="12" x2="20" y2="7.5"/>
                                    <line x1="12" y1="12" x2="12" y2="21"/>
                                    <line x1="12" y1="12" x2="4" y2="7.5"/>
                                </svg>
                            </span>
                            <span class="nav-link-title">Dashboard</span>
                        </a>
                    </li>
                    @if (auth()->user()->hasPermissionTo('manage-roles') || auth()->user()->hasPermissionTo('manage-permissions'))
                        <li class="nav-item dropdown {{ request()->routeIs('admin.*') ? 'active' : '' }}">
                            <a class="nav-link dropdown-toggle" href="#navbar-admin" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5"/>
                                        <path d="M12 12l8 -4.5"/>
                                        <path d="M12 12l0 9"/>
                                        <path d="M12 12l-8 -4.5"/>
                                    </svg>
                                </span>
                                <span class="nav-link-title">Admin</span>
                            </a>
                            <div class="dropdown-menu" data-bs-popper="none">
                                @if (auth()->user()->hasPermissionTo('manage-roles'))
                                    <a href="{{ route('admin.roles.index') }}" class="dropdown-item {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                <path d="M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9"/>
                                                <path d="M12 9m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/>
                                                <path d="M12 15.5c2 0 4 -1 4 -2.5s-2 -2.5 -4 -2.5s-4 1 -4 2.5s2 2.5 4 2.5"/>
                                            </svg>
                                        </span>
                                        <span>Roles</span>
                                    </a>
                                @endif
                                @if (auth()->user()->hasPermissionTo('manage-permissions'))
                                    <a href="{{ route('admin.permissions.index') }}" class="dropdown-item {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}">
                                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                <path d="M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9"/>
                                                <path d="M12 9m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/>
                                                <path d="M12 15.5c2 0 4 -1 4 -2.5s-2 -2.5 -4 -2.5s-4 1 -4 2.5s2 2.5 4 2.5"/>
                                            </svg>
                                        </span>
                                        <span>Permissions</span>
                                    </a>
                                @endif
                            </div>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</header>