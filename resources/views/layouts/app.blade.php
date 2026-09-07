<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Mall Lucky Draw') - Mall Lucky Draw Management System</title>

    <!-- Tabler CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Select2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.min.css">

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
                @include('layouts.inc.navbar')
            </div>

            @include('layouts.inc.alert')

            @yield('content')
        </div>
    @endauth

    @guest
        @yield('content')
    @endguest

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Tabler Core JS -->
    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/js/tabler.min.js"></script>
    
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.min.js"></script>

    <script>
        // Initialize components after DOM is ready
        document.addEventListener('DOMContentLoaded', function() {

            // Select2 initialization
            if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
                jQuery('.select2').select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                });
            }

            // DataTables initialization
            // if (typeof jQuery !== 'undefined' && jQuery.fn.dataTable) {
            //     jQuery('.datatable').DataTable({
            //         responsive: true,
            //         language: {
            //             url: '//cdn.datatables.net/plug-ins/1.13.8/i18n/id.json',
            //         },
            //     });
            // }
        });

        // Utility function for SweetAlert2 confirmation
        window.confirmDelete = function (e) {
            e.preventDefault();
            const form = e.target.closest('form');

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
                    form.submit();
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
