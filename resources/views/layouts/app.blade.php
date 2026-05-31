<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Mall Lucky Draw') - Mall Lucky Draw Management System</title>

    <!-- Tabler CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/css/tabler.min.css" crossorigin>
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" crossorigin>
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" crossorigin>
    
    <!-- Select2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" crossorigin>
    
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.min.css" crossorigin>

    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif;
        }

        .sidebar-nav .nav-link {
            border-radius: 6px;
            margin-bottom: 4px;
        }

        .sidebar-nav .nav-link.active {
            background-color: rgba(0, 102, 204, 0.1);
            color: #0066cc;
            font-weight: 600;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 20px;
        }
    </style>

    @stack('styles')
</head>
<body>
    @auth
        <!-- Page wrapper -->
        <div class="page">
            <div class="sticky-top">
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
                                <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow dropdown-menu-card">
                                    <div class="card">
                                        <div class="card-body">
                                            No new notifications
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="nav-item dropdown">
                                <a href="#" class="nav-link d-flex lh-1 p-0 px-2" data-bs-toggle="dropdown" aria-label="Open user menu">
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
                        <div class="navbar navbar-light">
                            <div class="container-xl">
                                <ul class="navbar-nav">
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}" role="button">
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
                                </ul>
                            </div>
                        </div>
                    </div>
                </header>
            </div>

            <!-- Page body -->
            <div class="page-wrapper">
                @yield('content')
            </div>
        </div>
    @endauth

    @guest
        @yield('content')
    @endguest

    <!-- jQuery (Load First) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin></script>
    
    <!-- Tabler Core JS -->
    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/js/tabler.min.js" crossorigin></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js" crossorigin></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js" crossorigin></script>
    
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js" crossorigin></script>
    
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.min.js" crossorigin></script>

    <script>
        // Initialize components after DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            // Fix Tabler navbar toggle for mobile

            // Select2 initialization
            if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
                jQuery('.select2').select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                });
            }

            // DataTables initialization
            if (typeof jQuery !== 'undefined' && jQuery.fn.dataTable) {
                jQuery('.datatable').DataTable({
                    responsive: true,
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.8/i18n/id.json',
                    },
                });
            }
        });

        // Utility function for SweetAlert2 confirmation
        window.confirmDelete = function (url) {
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: 'Apakah Anda yakin ingin menghapus data ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        };

        // Utility function for showing alerts
        window.showAlert = function (title, message, type = 'info') {
            Swal.fire({
                title: title,
                text: message,
                icon: type,
            });
        };
    </script>

    @stack('scripts')
</body>
</html>
