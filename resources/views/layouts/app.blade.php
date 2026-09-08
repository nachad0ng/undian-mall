<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Mall Lucky Draw') - Mall Lucky Draw Management System</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

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

            @yield('content')
        </div>
    @endauth

    @guest
        @yield('content')
    @endguest

    @stack('scripts')
</body>

</html>
