<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SIMONITA - Login</title>

    <link href="https://fonts.bunny.net/css?family=Nunito:400,600,700,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <style>
        body, html {
            height: 100%;
            font-family: 'Nunito', sans-serif;
            background-color: #f8f9fa;
        }

        .full-height { min-height: 100vh; }

        /* Bagian Kiri: Branding */
        .left-section {
            background-color: #ffffff;
            padding: 4rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .brand-text {
            font-size: 1.25rem;
            font-weight: 800;
            color: #198754;
            letter-spacing: 0.5px;
        }

        .feature-list i {
            color: #198754;
            font-size: 1.2rem;
            margin-right: 10px;
        }

        .feature-list div {
            margin-bottom: 12px;
            font-size: 1.05rem;
            color: #555;
        }

        /* Bagian Kanan: Background & Card */
        .right-section {
            background-image: url('/image/audit-bg.jpg'); /* Pastikan file ada di public/image/ */
            background-size: cover;
            background-position: center;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .right-section::before {
            content: "";
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(135deg, rgba(25, 135, 84, 0.7), rgba(15, 81, 50, 0.8));
        }

        .login-card {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 400px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(5px);
        }

        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            background-color: #f8f9fa;
        }

        .form-control:focus {
            border-color: #198754;
            box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
        }

        .btn-login {
            background-color: #198754;
            border: none;
            padding: 12px;
            font-weight: 700;
            border-radius: 10px;
            transition: all 0.3s;
        }

        .btn-login:hover {
            background-color: #146c43;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .left-section { display: none; } /* Sembunyikan kiri di HP agar fokus ke login */
            .right-section { padding: 2rem; }
        }
    </style>
</head>

<body>
    <div class="container-fluid p-0">
        <div class="row g-0 full-height">
            
            <div class="col-lg-7 left-section">
                <div class="d-flex align-items-center mb-5">
                    <img src="/image/ptpn.png" alt="Logo PTPN" style="height: 50px;">
                    <span class="brand-text ms-2">PTPN 1 Regional 7</span>
                </div>

                <div class="pe-lg-5">
                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill fw-bold mb-3">
                        INTERNAL AUDIT SYSTEM
                    </span>

                    <h1 class="fw-bolder display-5 text-dark mb-3">
                        Sistem Monitoring <br>
                        <span class="text-success">Audit Terintegrasi</span>
                    </h1>

                    <p class="text-secondary lead mb-5">
                        Platform digital (SIMONITA) untuk memantau kepatuhan standar audit secara 
                        real-time dan efisien di lingkungan PTPN 1 Regional 7.
                    </p>

                    <div class="feature-list">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Monitoring Temuan (Findings) Real-time</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-cloud-arrow-up-fill"></i>
                            <span>Pelaporan Digital & Upload Bukti</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5 right-section">
                <div class="login-card">
                    <div class="text-center mb-4">
                        <h3 class="fw-bold text-dark">Selamat Datang</h3>
                        <p class="text-muted">Masukkan kredensial Anda</p>
                    </div>

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-envelope text-muted"></i>
                                </span>
                                <input type="email" name="email" class="form-control border-start-0 @error('email') is-invalid @enderror" 
                                       value="{{ old('email') }}" required autofocus placeholder="nama@ptpn7.com">
                            </div>
                            @error('email')
                                <span class="text-danger small mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-key text-muted"></i>
                                </span>
                                <input type="password" name="password" class="form-control border-start-0 @error('password') is-invalid @enderror" 
                                       required placeholder="••••••••">
                            </div>
                            @error('password')
                                <span class="text-danger small mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-4 d-flex justify-content-between align-items-center">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                                <label class="form-check-label small text-muted" for="remember">Ingat Saya</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-login text-white w-100 shadow-sm">
                            MASUK <i class="bi bi-box-arrow-in-right ms-2"></i>
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</body>
</html>