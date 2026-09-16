<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Login - {{ config('app.name', 'IT Helpdesk System') }}</title>

    <!-- Google Fonts: Poppins (300, 400, 500, 600, 700) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
        }

        body, html {
            height: 100%;
            width: 100%;
            overflow-x: hidden;
            background-color: #F4F5F7;
        }

        .login-wrapper {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }

        /* Sisi Kiri: Form Login */
        .left-side {
            flex: 1;
            width: 50%;
            background-color: #F4F5F7;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 24px;
            min-height: 100vh;
        }

        .form-box {
            width: 100%;
            max-width: 420px;
            padding: 20px 10px;
        }

        .brand-header {
            margin-bottom: 30px;
        }

        .welcome-title {
            font-size: 2.75rem;
            font-weight: 700;
            color: #0ea5e9; /* Biru Langit (Sky Blue) */
            line-height: 1.2;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .welcome-subtitle {
            font-size: 0.95rem;
            color: #718096;
            font-weight: 400;
        }

        /* Form Controls (Pill Shaped) */
        .form-group {
            margin-bottom: 22px;
            position: relative;
        }

        .input-pill-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 20px;
            color: #A0AEC0;
            font-size: 1rem;
            transition: color 0.3s ease;
            pointer-events: none;
        }

        .input-pill {
            width: 100%;
            padding: 16px 20px 16px 52px;
            border-radius: 50px;
            border: 1px solid rgba(0, 0, 0, 0.03);
            background-color: #FFFFFF;
            font-size: 0.95rem;
            font-weight: 500;
            color: #2D3748;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
            outline: none;
            transition: all 0.25s ease;
        }

        .input-pill:focus {
            box-shadow: 0 6px 20px rgba(14, 165, 233, 0.18);
            border-color: rgba(14, 165, 233, 0.4);
        }

        .input-pill:focus + .input-icon,
        .input-pill-wrapper:focus-within .input-icon {
            color: #0ea5e9;
        }

        .toggle-password {
            position: absolute;
            right: 20px;
            color: #A0AEC0;
            cursor: pointer;
            font-size: 1rem;
            transition: color 0.2s ease;
        }

        .toggle-password:hover {
            color: #0ea5e9;
        }

        /* Submit Button (Pill Shaped & Biru Langit) */
        .btn-submit {
            width: 100%;
            padding: 16px 24px;
            border-radius: 50px;
            border: none;
            background-color: #0ea5e9; /* Biru Langit (Sky Blue) */
            color: #FFFFFF;
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: 1px;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(14, 165, 233, 0.3);
            transition: all 0.25s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 28px;
        }

        .btn-submit:hover {
            background-color: #0284c7;
            box-shadow: 0 10px 24px rgba(14, 165, 233, 0.4);
            transform: translateY(-2px);
        }

        .btn-submit:active {
            transform: translateY(0);
            box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3);
        }

        /* Footer Links */
        .form-footer {
            margin-top: 30px;
            text-align: center;
        }

        .back-portal-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #718096;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 20px;
            transition: all 0.2s ease;
        }

        .back-portal-link:hover {
            color: #0ea5e9;
            background-color: rgba(14, 165, 233, 0.08);
        }

        /* Alert Notification */
        .alert-box {
            padding: 12px 20px;
            border-radius: 30px;
            margin-bottom: 20px;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-danger {
            background-color: #FED7D7;
            color: #C53030;
            border: 1px solid #FEB2B2;
        }

        .alert-success {
            background-color: #C6F6D5;
            color: #22543D;
            border: 1px solid #9AE6B4;
        }

        /* Sisi Kanan: Gambar Penuh di Sebelah Kanan */
        .right-side {
            flex: 1;
            width: 50%;
            background-color: #FCE3B4;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            min-height: 100vh;
        }

        .right-side-container {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .illustration-3d {
            width: 100%;
            height: 100%;
            min-height: 100vh;
            object-fit: cover;
            object-position: center;
            user-select: none;
            display: block;
        }

        /* Responsivitas Layar Mobile & Tablet */
        @media (max-width: 991px) {
            .right-side {
                display: none;
            }

            .left-side {
                width: 100%;
                padding: 30px 20px;
                background-color: #F4F5F7;
            }

            .welcome-title {
                font-size: 2.25rem;
            }

            .form-box {
                max-width: 380px;
            }
        }
    </style>
</head>
<body>

<div class="login-wrapper">

    <!-- Sisi Kiri (Form Login + Background Abu-abu Terang) -->
    <div class="left-side">
        <div class="form-box">
            
            <div class="brand-header">
                <h1 class="welcome-title">Welcome</h1>
                <p class="welcome-subtitle">Silakan login untuk masuk ke dashboard sistem</p>
            </div>

            <!-- Status atau Alert Error -->
            @if (session('status'))
                <div class="alert-box alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert-box alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Input Email / Username -->
                <div class="form-group">
                    <div class="input-pill-wrapper">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="text" 
                               id="username" 
                               name="username" 
                               class="input-pill" 
                               placeholder="Email Address" 
                               value="{{ old('username') }}" 
                               required 
                               autofocus 
                               autocomplete="username">
                    </div>
                </div>

                <!-- Input Password -->
                <div class="form-group">
                    <div class="input-pill-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="input-pill" 
                               placeholder="Password" 
                               required 
                               autocomplete="current-password">
                        <i class="fas fa-eye toggle-password" id="togglePasswordBtn" onclick="togglePasswordVisibility()"></i>
                    </div>
                </div>

                <!-- Tombol Submit (Biru Langit) -->
                <button type="submit" class="btn-submit">
                    <span>LOGIN</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>

            <!-- Tautan Kembali -->
            <div class="form-footer">
                <a href="{{ url('/') }}" class="back-portal-link">
                    <i class="fas fa-arrow-left"></i>
                    <span>Kembali ke Portal Pengaduan</span>
                </a>
            </div>

        </div>
    </div>

    <!-- Sisi Kanan (Gambar Penuh di Sebelah Kanan) -->
    <div class="right-side">
        <div class="right-side-container">
            <img src="{{ asset('image/login-image.png') }}" 
                 alt="IT Helpdesk Illustration" 
                 class="illustration-3d"
                 onerror="this.onerror=null; this.src='{{ asset('images/login-image.png') }}';" />
        </div>
    </div>

</div>

<script>
    function togglePasswordVisibility() {
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('togglePasswordBtn');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        }
    }
</script>

</body>
</html>
