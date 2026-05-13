<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="description" content="Jembatan Akuntabilitas Bumdesma">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="keywords"
        content="dbm, sidbm, sidbm.net, demo.sidbm.net, app.sidbm.net, asta brata teknologi, abt, dbm, kepmendesa 136, kepmendesa nomor 136 tahun 2022">
    <meta name="author" content="Enfii">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ $logo }}">
    <link rel="icon" type="image/png" href="{{ $logo }}">
    <title> Aplikasi LKM V.9.10</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet">

    <!-- Icons -->
    <link href="/argon/css/nucleo-icons.css" rel="stylesheet" />
    <link href="/argon/css/nucleo-svg.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- Argon CSS -->
    <link href="/argon/css/argon-dashboard.min.css" rel="stylesheet" />

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body,
        html {
            height: 100%;
            overflow: hidden;
        }

        body {
            position: relative;
            background: #f4f6f9;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 20%;
            background: #344767;
            z-index: 0;
        }

        .login-container {
            display: flex;
            height: 100vh;
            position: relative;
            z-index: 1;
        }

        .login-left {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem;
        }

        .login-right {
            flex: 1;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .login-right img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 1rem;
        }

        .login-card {
            background: transparent;
            backdrop-filter: none;
            border-radius: 0;
            box-shadow: none;
            padding: 4rem 3.5rem;
            width: 100%;
            height: 100%;
            max-width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .login-content {
            width: 100%;
            max-width: 420px;
            padding: 2.25rem 2rem;
            background: rgba(255, 255, 255, 0.35) !important;
            backdrop-filter: blur(12px) !important;
            -webkit-backdrop-filter: blur(12px) !important;
            border-radius: 2.25rem !important;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12) !important;
            border: 1px solid rgba(255, 255, 255, 0.45) !important;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 2.25rem;
        }

        .logo-container img {
            width: 135px;
            height: 135px;
            border-radius: 50%;
            padding: 9px;
            background: white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.35rem;
        }

        .logo-container h4 {
            margin-bottom: 0.5rem;
            font-weight: 700;
            color: #32325d;
            font-size: 1.125rem;
            line-height: 1.3;
        }

        .logo-container h5 {
            margin-bottom: 0;
            color: #8898aa;
            font-weight: 400;
            font-size: 0.9rem;
        }

        .form-group {
            margin-bottom: 1.35rem;
        }

        .input-group-alternative {
            box-shadow: 0 1px 3px rgba(50, 50, 93, .15), 0 1px 0 rgba(0, 0, 0, .02);
            border: 0;
            transition: box-shadow .15s ease;
            border-radius: 0.375rem;
            background-color: #fff;
            display: flex !important;
            align-items: center !important;
        }

        .input-group-alternative:focus-within {
            box-shadow: 0 4px 6px rgba(50, 50, 93, .11), 0 1px 3px rgba(0, 0, 0, .08);
            border: 1px solid #7267E4;
        }

        .input-group-alternative .form-control {
            border: 0;
            background-color: transparent;
            font-size: 0.78rem;
            padding: 1.08rem 0.9rem;
            flex: 1 !important;
        }

        .input-group-alternative .form-control:focus {
            background-color: transparent;
            box-shadow: none;
        }

        .input-group-prepend .input-group-text {
            border: 0;
            background-color: transparent;
            color: #8898aa;
            font-size: 0.78rem;
            padding: 0 1rem !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            height: 100% !important;
        }

        .btn-primary {
            background: linear-gradient(87deg, #7267E4 0, #7267E4 100%);
            border: none;
            width: 100%;
            padding: 1.17rem;
            font-weight: 600;
            border-radius: 0.375rem;
            text-transform: uppercase;
            font-size: 0.78rem;
            letter-spacing: 0.45px;
            transition: all 0.15s ease;
            margin-top: 0.9rem;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 7px 14px rgba(50, 50, 93, .1), 0 3px 6px rgba(0, 0, 0, .08);
        }

        .footer-text {
            text-align: center;
            margin-top: 1.8rem;
            color: #8898aa;
            font-size: 0.78rem;
        }

        @media (max-width: 991px) {
            .login-right {
                display: none;
            }

            .login-left {
                flex: 1;
            }

            .login-content {
                width: 80%;
            }
        }

        @media (max-width: 576px) {
            .login-left {
                padding: 1rem;
            }

            .login-card {
                padding: 1rem 0.25rem;
            }

            .login-content {
                width: 100%;
                max-width: 100%;
                padding: 2rem 1.25rem !important;
            }

            .logo-container img {
                width: 80px;
                height: 80px;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <!-- Left Side - Login Form -->
        <div class="login-left">
            <div class="login-card">
                <div class="login-content">
                    <div class="logo-container">
                        <img src="{{ $logo }}" alt="Logo"
                            onerror="this.onerror=null; this.src='/assets/img/logo.jpeg';" />
                        <h4>{{ $kec->nama_lembaga_sort }}</h4>
                        <h5>{{ $kec->nama_kec }}</h5>
                    </div>

                    <form role="form" method="POST" action="/login">
                        @csrf

                        <div class="form-group">
                            <div class="input-group input-group-alternative">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-user"></i></span>
                                </div>
                                <input class="form-control" placeholder="Username" type="text" name="username"
                                    id="username" required autofocus>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-group input-group-alternative">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                </div>
                                <input class="form-control" placeholder="Password" type="password" name="password"
                                    id="password" required>
                            </div>
                        </div>

                        <button type="submit" name="login" class="btn btn-primary">Login</button>
                    </form>

                    <div class="footer-text">
                        &copy; {{ date('Y') }} PT. Asta Brata Teknologi &mdash;
                        {{ str_pad($kec->id, 4, '0', STR_PAD_LEFT) }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Background Image -->
        <div class="login-right">
            <img src="/argon/img/bg.png" alt="Background">
        </div>
    </div>

    <!-- Core -->
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="/argon/js/core/bootstrap.bundle.min.js"></script>

    <!-- Argon JS -->
    <script src="/argon/js/argon-dashboard.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        if (localStorage.getItem('devops') !== 'true') {
            $(document).bind("contextmenu", function(e) {
                return false;
            });

            $(document).keydown(function(event) {
                if (event.keyCode == 123) { // Prevent F12
                    return false;
                }
                if (event.ctrlKey && event.shiftKey && event.keyCode == 73) { // Prevent Ctrl+Shift+I        
                    return false;
                }
                if (event.ctrlKey && event.shiftKey && event.keyCode == 67) { // Prevent Ctrl+Shift+C  
                    return false;
                }
                if (event.ctrlKey && event.shiftKey && event.keyCode == 74) { // Prevent Ctrl+Shift+J
                    return false;
                }
            });
        }
    </script>

    @if (session('error'))
        <script>
            $(document).ready(function() {
                if (typeof Swal !== 'undefined') {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        didOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer)
                            toast.addEventListener('mouseleave', Swal.resumeTimer)
                        }
                    });

                    var font = "1.2rem Nimrod MT";
                    var canvas = document.createElement("canvas");
                    var context = canvas.getContext("2d");
                    context.font = font;
                    var width = context.measureText("{{ session('error') }}").width;
                    var formattedWidth = Math.ceil(width) + 100;

                    Toast.fire({
                        icon: 'error',
                        title: "{{ session('error') }}",
                        width: formattedWidth
                    });
                }
            });
        </script>
    @endif

    @if (session('pesan'))
        <script>
            $(document).ready(function() {
                if (typeof Swal !== 'undefined') {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        didOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer)
                            toast.addEventListener('mouseleave', Swal.resumeTimer)
                        }
                    });

                    var font = "1.2rem Nimrod MT";
                    var canvas = document.createElement("canvas");
                    var context = canvas.getContext("2d");
                    context.font = font;
                    var width = context.measureText("{{ session('pesan') }}").width;
                    var formattedWidth = Math.ceil(width) + 100;

                    Toast.fire({
                        icon: 'success',
                        title: "{{ session('pesan') }}",
                        width: formattedWidth
                    });
                }
            });
        </script>
    @endif
</body>

</html>
