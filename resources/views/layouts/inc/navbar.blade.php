<!-- Navbar -->
<header class="navbar navbar-expand-md d-print-none">
    <div class="container-xl">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu"
            aria-controls="navbar-menu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <h1 class="navbar-brand navbar-brand-autodark pe-0 pe-md-3">
            <a href="{{ route('dashboard') }}">
                <i class="bi bi-gift-fill" style="font-size: 1.5rem; color: #0066cc;"></i>
                <span class="d-none d-md-inline">{{ config('app.name') }}</span>
            </a>
        </h1>
        <div class="navbar-nav flex-row order-md-last">
            <div class="nav-item d-none d-md-flex me-3">
                <div class="btn-list flex-nowrap">
                    <a href="https://github.com/nachad0ng/undian-mall" class="btn btn-icon" target="_blank"
                        rel="noreferrer" title="Source Code">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path
                                d="M9 19c-4.3 1.4 -4.3 -2.5 -6 -3m12 -4v4m0 0v4m0 -11v-4m0 0a1 1 0 0 0 -1 -1h-4a1 1 0 0 0 -1 1m0 0v4a1 1 0 0 0 1 1h4a1 1 0 0 0 1 -1m6 -1a1 1 0 0 0 1 -1v-4a1 1 0 0 0 -1 -1m0 0v-4a1 1 0 0 0 -1 -1h-4a1 1 0 0 0 -1 1m-9 9a1 1 0 0 0 1 1h4a1 1 0 0 0 1 -1m0 0v-4a1 1 0 0 0 -1 -1h-4a1 1 0 0 0 -1 1m0 4v4" />
                        </svg>
                    </a>
                </div>
            </div>
            <div class="nav-item dropdown">
                <a href="#navbar-notifications" class="nav-link px-0" data-bs-toggle="dropdown" aria-expanded="false"
                    role="button" aria-label="Show notifications">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="icon icon-1">
                        <path
                            d="M10 5a2 2 0 1 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6" />
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
                <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown"
                    aria-label="Open user menu">
                    <span class="avatar avatar-sm"
                        style="background-image: url(https://avatars.githubusercontent.com/u/66051499?v=4)"></span>
                    <div class="d-none d-xl-block ps-2">
                        <div>{{ auth()->user()->name }}</div>
                        <div class="mt-1 small text-secondary">{{ auth()->user()->getRoleNames()->first() ?? 'User' }}
                        </div>
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

<div class="navbar-expand-md">
    <div class="collapse navbar-collapse" id="navbar-menu">
        <div class="navbar">
            <div class="container-xl">
                <ul class="navbar-nav">
                    <li class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('dashboard') }}" role="button">
                            <span class="nav-link-icon">
                                <i class="bi bi-house"></i>
                            </span>
                            <span class="nav-link-title">Dashboard</span>
                        </a>
                    </li>
                    {{-- Master Data: Periode, Tenant, Hadiah, Tipe Pembayaran --}}
                    @if (auth()->user()->hasPermissionTo('manage-periods') ||
                            auth()->user()->hasPermissionTo('manage-tenants') ||
                            auth()->user()->hasPermissionTo('manage-customers') ||
                            auth()->user()->hasPermissionTo('manage-prizes') ||
                            auth()->user()->hasPermissionTo('manage-payment-types'))
                        <li
                            class="nav-item dropdown {{ request()->routeIs('admin.raffle-periods.*') || request()->routeIs('admin.tenants.*') || request()->routeIs('admin.customers.*') || request()->routeIs('admin.prizes.*') || request()->routeIs('admin.payment-types.*') || request()->routeIs('admin.bonus-point-rules.*') ? 'active' : '' }}">
                            <a class="nav-link dropdown-toggle" href="#navbar-master" data-bs-toggle="dropdown"
                                data-bs-auto-close="outside" role="button" aria-expanded="false">
                                <span class="nav-link-icon">
                                    <i class="bi bi-tags"></i>
                                </span>
                                <span class="nav-link-title">Master Data</span>
                            </a>
                            <div class="dropdown-menu">
                                @if (auth()->user()->hasPermissionTo('manage-periods'))
                                    <a href="{{ route('admin.raffle-periods.index') }}"
                                        class="dropdown-item {{ request()->routeIs('admin.raffle-periods.*') ? 'active' : '' }}">
                                        Periode Undian
                                    </a>
                                @endif
                                @if (auth()->user()->hasPermissionTo('manage-tenants'))
                                    <a href="{{ route('admin.tenants.index') }}"
                                        class="dropdown-item {{ request()->routeIs('admin.tenants.*') ? 'active' : '' }}">
                                        Tenant
                                    </a>
                                @endif
                                @if (auth()->user()->hasPermissionTo('manage-customers'))
                                    <a href="{{ route('admin.customers.index') }}"
                                        class="dropdown-item {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
                                        Pelanggan
                                    </a>
                                @endif
                                @if (auth()->user()->hasPermissionTo('manage-prizes'))
                                    <a href="{{ route('admin.prizes.index') }}"
                                        class="dropdown-item {{ request()->routeIs('admin.prizes.*') ? 'active' : '' }}">
                                        Hadiah
                                    </a>
                                @endif
                                @if (auth()->user()->hasPermissionTo('manage-payment-types'))
                                    <a href="{{ route('admin.payment-types.index') }}"
                                        class="dropdown-item {{ request()->routeIs('admin.payment-types.*') ? 'active' : '' }}">
                                        Tipe Pembayaran
                                    </a>
                                @endif
                                @if (auth()->user()->hasPermissionTo('manage-prizes'))
                                    <a href="{{ route('admin.bonus-point-rules.index') }}"
                                        class="dropdown-item {{ request()->routeIs('admin.bonus-point-rules.*') ? 'active' : '' }}">
                                        Bonus Poin Pembayaran
                                    </a>
                                @endif
                            </div>
                        </li>
                    @endif
                    {{-- Transaction: Point Redemption --}}
                    @if (auth()->user()->hasPermissionTo('manage-prizes'))
                        <li
                            class="nav-item dropdown {{ request()->routeIs('admin.point-exchange.*') ? 'active' : '' }}">
                            <a class="nav-link dropdown-toggle" href="#navbar-transaction" data-bs-toggle="dropdown"
                                data-bs-auto-close="outside" role="button" aria-expanded="false">
                                <span class="nav-link-icon">
                                    <i class="bi bi-arrow-left-right"></i>
                                </span>
                                <span class="nav-link-title">Transaction</span>
                            </a>
                            <div class="dropdown-menu" data-bs-popper="none">
                                <a href="{{ route('admin.point-exchange.history') }}"
                                    class="dropdown-item {{ request()->routeIs('admin.point-exchange.*') ? 'active' : '' }}">
                                    Tukar Struk & Riwayat
                                </a>
                            </div>
                        </li>
                    @endif
                    @if (auth()->user()->hasPermissionTo('view-audit-logs'))
                        <li class="nav-item {{ request()->routeIs('admin.audit-logs.*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('admin.audit-logs.index') }}">
                                <span class="nav-link-icon"><i class="bi bi-clock-history"></i></span>
                                <span class="nav-link-title">Audit Log</span>
                            </a>
                        </li>
                    @endif
                    {{-- Admin: Users, Roles, Permissions --}}
                    @if (auth()->user()->hasPermissionTo('manage-users') ||
                            auth()->user()->hasPermissionTo('manage-roles') ||
                            auth()->user()->hasPermissionTo('manage-permissions'))
                        <li
                            class="nav-item dropdown {{ request()->routeIs('admin.users.*') || request()->routeIs('admin.roles.*') || request()->routeIs('admin.permissions.*') ? 'active' : '' }}">
                            <a class="nav-link dropdown-toggle" href="#navbar-admin" data-bs-toggle="dropdown"
                                data-bs-auto-close="outside" role="button" aria-expanded="false">
                                <span class="nav-link-icon">
                                    <i class="bi bi-gear-wide-connected"></i>
                                </span>
                                <span class="nav-link-title">Admin</span>
                            </a>
                            <div class="dropdown-menu" data-bs-popper="none">
                                @if (auth()->user()->hasPermissionTo('manage-users'))
                                    <a href="{{ route('admin.users.index') }}"
                                        class="dropdown-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                                        Users
                                    </a>
                                @endif
                                @if (auth()->user()->hasPermissionTo('manage-roles'))
                                    <a href="{{ route('admin.roles.index') }}"
                                        class="dropdown-item {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                                        Roles
                                    </a>
                                @endif
                                @if (auth()->user()->hasPermissionTo('manage-permissions'))
                                    <a href="{{ route('admin.permissions.index') }}"
                                        class="dropdown-item {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}">
                                        Permissions
                                    </a>
                                @endif
                            </div>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</div>
