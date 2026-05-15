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

    
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet">

    
    <link href="/assets/argon/css/nucleo-icons.css" rel="stylesheet" />
    <link href="/assets/argon/css/nucleo-svg.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    
    <link href="/assets/argon/css/argon-dashboard.min.css" rel="stylesheet" />

    <link rel="stylesheet" href="/assets/css/lkm-auth.css">
</head>

<body>
    <div class="login-container">
        
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

                        <button type="submit" name="login" class="btn btn-primary">Masuk ke Sistem</button>
                    </form>

                    <div class="footer-text">
                        &copy; {{ date('Y') }} PT. Asta Brata Teknologi &mdash;
                        {{ str_pad($kec->id, 4, '0', STR_PAD_LEFT) }}
                    </div>
                </div>
            </div>
        </div>

        
        <div class="login-right">
            <img src="/assets/argon/img/bg.png" alt="Background">
        </div>
    </div>

    
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="/assets/argon/js/core/bootstrap.bundle.min.js"></script>

    
    <script src="/assets/argon/js/argon-dashboard.min.js"></script>

    
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
