<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Sistem Audit PTPN 1 Regional 7</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Font -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Custom Style -->
    <style>
        body {
            font-family: 'Nunito', sans-serif;
        }

        .navbar-custom {
            background-color: #198754;
        }

        .navbar-custom .nav-link {
            color: rgba(255, 255, 255, 0.85);
            font-size: 0.95rem;
            margin-right: 10px;
        }

        .navbar-custom .nav-link:hover,
        .navbar-custom .nav-link.active {
            color: #ffffff;
            font-weight: bold;
        }

        .navbar-brand {
            font-weight: 800;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
        }

        .navbar-brand img {
            height: 40px;
            margin-right: 12px;
        }

        @media print {
            .navbar>.container>*:not(.navbar-brand) {
                display: none !important;
            }

            .navbar {
                background: transparent !important;
                box-shadow: none !important;
            }
        }
    </style>
</head>

<body>
    <div id="app">

        <!-- NAVBAR -->
        <nav class="navbar navbar-expand-md navbar-dark navbar-custom shadow-sm">
            <div class="container">

                <!-- LOGO -->
                <a class="navbar-brand" href="{{ url('/home') }}">
                    <img src="/image/ptpn7.png" alt="Logo PTPN">
                    PTPN 1 Regional 7
                </a>

                <!-- TOGGLER -->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">

                    <!-- MENU KIRI -->
                    <ul class="navbar-nav me-auto ms-3">
                        @auth
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}"
                                    href="{{ route('home') }}">
                                    Beranda
                                </a>
                            </li>

                            @if(Auth::user()->role == 'auditor')
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('audit.create') ? 'active' : '' }}"
                                        href="{{ route('audit.create') }}">
                                        Buat Jadwal
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('audit.standards.*') ? 'active' : '' }}"
                                        href="{{ route('audit.standards.index') }}">
                                        Kelola Standar
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('riwayat.*') ? 'active' : '' }}"
                                        href="{{ route('riwayat.index') }}">
                                        Riwayat Unit
                                    </a>
                                </li>
                            @endif

                            @if(Auth::user()->role == 'auditee')
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('audit.request.form') ? 'active' : '' }}"
                                        href="{{ route('audit.request.form') }}">
                                        Ajuan Audit Baru
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('riwayat.show') ? 'active' : '' }}"
                                        href="{{ route('riwayat.show', Auth::id()) }}">
                                        Riwayat Unit
                                    </a>
                                </li>
                            @endif
                        @endauth
                    </ul>

                    <!-- MENU KANAN -->
                    <ul class="navbar-nav ms-auto">
                        @guest
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">Login</a>
                            </li>
                        @else
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle bg-white text-success fw-bold rounded px-3 py-1" href="#"
                                    role="button" data-bs-toggle="dropdown">
                                    {{ Auth::user()->name }}
                                    <span class="badge bg-warning text-dark ms-1">
                                        {{ ucfirst(Auth::user()->role) }}
                                    </span>
                                </a>

                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>

                </div>
            </div>
        </nav>

        <!-- CONTENT -->
        <main class="py-4">
            @yield('content')
        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>