<!DOCTYPE html>
<html lang="en" translate="no">

<head>
    <meta charset="utf-8" />
    <meta name="description" content="Jembatan Akuntabilitas Bumdesma">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="keywords"
        content="dbm, sidbm, sidbm.net, demo.sidbm.net, app.sidbm.net, asta brata teknologi, abt, dbm, kepmendesa 136, kepmendesa nomor 136 tahun 2022">
    <meta name="author" content="Enfii">
    <title>Daftar Akun - {{ $kec->nama_kec }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet" />

    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #0f172a;
            min-height: 100vh;
            margin: 0;
            color: #e2e8f0;
            background-image:
                radial-gradient(at 20% 0%, rgba(99, 102, 241, 0.15) 0px, transparent 50%),
                radial-gradient(at 80% 100%, rgba(168, 85, 247, 0.15) 0px, transparent 50%),
                radial-gradient(at 50% 50%, rgba(56, 189, 248, 0.05) 0px, transparent 50%);
            background-attachment: fixed;
        }

        .page {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 24px 60px;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 32px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: linear-gradient(135deg, #6366f1, #a855f7);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 22px;
            box-shadow: 0 10px 30px -10px rgba(99, 102, 241, 0.5);
        }

        .brand-text h1 {
            font-size: 20px;
            font-weight: 700;
            margin: 0;
            color: #f8fafc;
            letter-spacing: -0.01em;
        }

        .brand-text p {
            font-size: 13px;
            color: #94a3b8;
            margin: 2px 0 0;
        }

        .location-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            background: rgba(99, 102, 241, 0.1);
            border: 1px solid rgba(99, 102, 241, 0.25);
            color: #c7d2fe;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 500;
        }

        .location-chip i {
            color: #818cf8;
        }

        .web-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            background: linear-gradient(135deg, #06b6d4, #0891b2);
            color: #fff;
            text-decoration: none;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            transition: transform .2s, box-shadow .2s;
            box-shadow: 0 6px 20px -8px rgba(6, 182, 212, 0.6);
        }

        .web-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px -8px rgba(6, 182, 212, 0.7);
            color: #fff;
        }

        .card {
            background: rgba(30, 41, 59, 0.6);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(148, 163, 184, 0.1);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 22px 28px;
            border-bottom: 1px solid rgba(148, 163, 184, 0.1);
        }

        .card-header h2 {
            font-size: 16px;
            font-weight: 600;
            margin: 0;
            color: #f1f5f9;
        }

        .search {
            position: relative;
        }

        .search input {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(148, 163, 184, 0.15);
            color: #e2e8f0;
            padding: 9px 14px 9px 38px;
            border-radius: 10px;
            font-size: 13px;
            font-family: inherit;
            width: 240px;
            outline: none;
            transition: border-color .2s, background .2s;
        }

        .search input::placeholder {
            color: #64748b;
        }

        .search input:focus {
            border-color: #6366f1;
            background: rgba(15, 23, 42, 0.9);
        }

        .search i {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
            font-size: 13px;
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            text-align: left;
            padding: 14px 24px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #94a3b8;
            background: rgba(15, 23, 42, 0.4);
            border-bottom: 1px solid rgba(148, 163, 184, 0.1);
        }

        tbody tr {
            border-bottom: 1px solid rgba(148, 163, 184, 0.06);
            transition: background .2s;
        }

        tbody tr:last-child {
            border-bottom: none;
        }

        tbody tr:hover {
            background: rgba(99, 102, 241, 0.05);
        }

        tbody td {
            padding: 16px 24px;
            font-size: 14px;
            color: #cbd5e1;
            vertical-align: middle;
        }

        .name-cell {
            font-weight: 500;
            color: #f1f5f9;
        }

        .level-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            background: rgba(99, 102, 241, 0.15);
            color: #a5b4fc;
            border: 1px solid rgba(99, 102, 241, 0.25);
        }

        .cred {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(148, 163, 184, 0.12);
            border-radius: 8px;
            font-family: 'SF Mono', Monaco, 'Cascadia Code', 'Roboto Mono', monospace;
            font-size: 13px;
            color: #e2e8f0;
            cursor: pointer;
            user-select: none;
            transition: all .2s;
        }

        .cred:hover {
            background: rgba(99, 102, 241, 0.12);
            border-color: rgba(99, 102, 241, 0.4);
            color: #fff;
        }

        .cred i {
            font-size: 12px;
            color: #64748b;
            transition: color .2s;
        }

        .cred:hover i {
            color: #4857da;
        }

        .row-hint {
            font-size: 11px;
            color: #64748b;
            text-align: center;
            padding: 14px;
            border-top: 1px solid rgba(148, 163, 184, 0.08);
            background: rgba(15, 23, 42, 0.3);
        }

        .row-hint kbd {
            background: rgba(148, 163, 184, 0.1);
            border: 1px solid rgba(148, 163, 184, 0.2);
            color: #cbd5e1;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-family: inherit;
        }

        .toast {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            background: rgba(15, 23, 42, 0.95);
            border: 1px solid rgba(99, 102, 241, 0.4);
            color: #f1f5f9;
            padding: 12px 20px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            backdrop-filter: blur(20px);
            box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.5);
            opacity: 0;
            transition: all .3s cubic-bezier(.4, 0, .2, 1);
            z-index: 9999;
            pointer-events: none;
        }

        .toast.show {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }

        .toast i {
            color: #34d399;
        }

        .empty {
            padding: 60px 20px;
            text-align: center;
            color: #64748b;
        }

        .empty i {
            font-size: 32px;
            margin-bottom: 12px;
            display: block;
        }

        @media (max-width: 768px) {
            .page {
                padding: 24px 16px 40px;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .search input {
                width: 100%;
            }

            .card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            thead th,
            tbody td {
                padding: 12px 14px;
            }
        }
    </style>
</head>

<body>
    <div class="page">
        <div class="header">
            <div class="brand">
                <div class="brand-icon">
                    <i class="fas fa-users-cog"></i>
                </div>
                <div class="brand-text">
                    <h1>Daftar Akun</h1>
                    <p>Jembatan Akuntabilitas Bumdesma</p>
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">Lokasi :
                <span class="cred" data-copy="{{ $kec->id }}" title="Klik untuk copy">
                    <i class="fas fa-user"></i> {{ $kec->id }} <i class="fas fa-copy" style="margin-left:4px;"></i>
                </span>
                <span class="location-chip">
                    <i class="fas fa-map-marker-alt"></i>
                    Kec. {{ $kec->nama_kec }} - {{ $kec->kabupaten->nama_kab }}
                </span>
                <a href="/" target="_blank" class="web-btn">
                    <i class="fas fa-external-link-alt"></i> {{ $kec->web_kec }}
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-list-ul" style="margin-right:8px; color:#818cf8;"></i>Pengguna Terdaftar</h2>
                <div class="search">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Cari nama, username..." autocomplete="off">
                </div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Level</th>
                            <th>Jabatan</th>
                            <th>Username</th>
                            <th>Password</th>
                        </tr>
                    </thead>
                    <tbody id="userTable">
                        @forelse ($users as $u)
                            <tr class="login-row" data-uname="{{ $u->uname }}" data-pass="{{ $u->pass }}"
                                data-search="{{ strtolower($u->namadepan . ' ' . $u->namabelakang . ' ' . $u->uname) }}">
                                <td class="name-cell">{{ $u->namadepan . ' ' . $u->namabelakang }}</td>
                                <td><span class="level-badge">{{ $u->l->nama_level }}</span></td>
                                <td>{{ $u->j->nama_jabatan }}</td>
                                <td>
                                    <span class="cred" data-copy="{{ $u->uname }}" title="Klik untuk copy">
                                        <i class="fas fa-user"></i> {{ $u->uname }} <i class="fas fa-copy"
                                            style="margin-left:4px;"></i>
                                    </span>
                                </td>
                                <td>
                                    <span class="cred" data-copy="{{ $u->pass }}" title="Klik untuk copy">
                                        <i class="fas fa-key"></i> {{ $u->pass }} <i class="fas fa-copy"
                                            style="margin-left:4px;"></i>
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="empty">
                                    <i class="fas fa-inbox"></i>
                                    Tidak ada pengguna terdaftar
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="row-hint">
                <kbd>Klik</kbd> username / password untuk menyalin &nbsp;•&nbsp; <kbd>Double click</kbd> baris untuk login
            </div>
        </div>
    </div>

    <div class="toast" id="toast">
        <i class="fas fa-check-circle"></i>
        <span id="toastMsg">Tersalin!</span>
    </div>

    <form style="display: none;" role="form" method="POST" action="/login" id="formLogin">
        @csrf
        <input type="hidden" name="username" id="username">
        <input type="hidden" name="password" id="password">
    </form>

    <script>
        function showToast(msg) {
            var t = document.getElementById('toast');
            document.getElementById('toastMsg').textContent = msg;
            t.classList.add('show');
            clearTimeout(window._toastTimer);
            window._toastTimer = setTimeout(function() {
                t.classList.remove('show');
            }, 1800);
        }

        function copyText(text) {
            if (navigator.clipboard && window.isSecureContext) {
                return navigator.clipboard.writeText(text);
            }
            return new Promise(function(resolve, reject) {
                var ta = document.createElement('textarea');
                ta.value = text;
                ta.style.position = 'fixed';
                ta.style.opacity = '0';
                document.body.appendChild(ta);
                ta.select();
                try {
                    document.execCommand('copy');
                    resolve();
                } catch (e) {
                    reject(e);
                }
                document.body.removeChild(ta);
            });
        }

        document.addEventListener('click', function(e) {
            var cred = e.target.closest('.cred');
            if (!cred) return;
            var text = cred.getAttribute('data-copy') || '';
            if (!text) return;
            e.stopPropagation();
            copyText(text).then(function() {
                showToast('Berhasil menyalin: ' + text);
            }).catch(function() {
                showToast('Gagal menyalin');
            });
        });

        document.addEventListener('dblclick', function(e) {
            var row = e.target.closest('.login-row');
            if (!row) return;
            e.preventDefault();
            var uname = row.getAttribute('data-uname');
            var pass = row.getAttribute('data-pass');
            document.getElementById('username').value = uname;
            document.getElementById('password').value = pass;
            showToast('Login sebagai ' + uname);
            setTimeout(function() {
                document.getElementById('formLogin').submit();
            }, 400);
        });

        document.getElementById('searchInput').addEventListener('input', function(e) {
            var q = e.target.value.toLowerCase().trim();
            document.querySelectorAll('.login-row').forEach(function(row) {
                var s = row.getAttribute('data-search') || '';
                row.style.display = (q === '' || s.indexOf(q) !== -1) ? '' : 'none';
            });
        });

        function openUserPage() {
            history.replaceState({}, '', '{{ $http }}://{{ $host }}');
        }
        openUserPage();
    </script>
</body>

</html>
