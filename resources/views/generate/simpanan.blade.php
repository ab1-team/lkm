<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Simpanan &mdash; Developer Tools</title>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg-primary: #0d1117;
            --bg-secondary: #161b22;
            --bg-tertiary: #1c2333;
            --bg-card: #1a1f2e;
            --border: #30363d;
            --text-primary: #e6edf3;
            --text-secondary: #8b949e;
            --text-muted: #484f58;
            --accent: #58a6ff;
            --accent-glow: rgba(88, 166, 255, 0.15);
            --green: #3fb950;
            --green-glow: rgba(63, 185, 80, 0.15);
            --purple: #bc8cff;
            --orange: #f0883e;
            --red: #f85149;
            --cyan: #39d2c0;
            --cyan-glow: rgba(57, 210, 192, 0.2);
            --gradient-accent: linear-gradient(135deg, #58a6ff, #bc8cff);
            --gradient-card: linear-gradient(145deg, #1a1f2e, #151a27);
            --font-mono: 'JetBrains Mono', 'Fira Code', monospace;
            --font-sans: 'Inter', -apple-system, sans-serif;
            --radius: 12px;
        }

        body {
            font-family: var(--font-sans);
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* Animated background grid */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                radial-gradient(circle at 25% 25%, rgba(88,166,255,0.03) 0%, transparent 50%),
                radial-gradient(circle at 75% 75%, rgba(188,140,255,0.03) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }

        /* === TOP BAR === */
        .topbar {
            background: var(--bg-secondary);
            border-bottom: 1px solid var(--border);
            padding: 0 24px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(12px);
        }
        .topbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .topbar-icon {
            width: 20px; height: 20px;
            background: var(--gradient-accent);
            border-radius: 4px;
        }
        .topbar-title {
            font-family: var(--font-mono);
            font-size: 13px;
            color: var(--text-secondary);
        }
        .topbar-title span { color: var(--accent); }
        .topbar-badge {
            font-family: var(--font-mono);
            font-size: 10px;
            padding: 2px 8px;
            border-radius: 10px;
            background: var(--accent-glow);
            color: var(--accent);
            border: 1px solid rgba(88,166,255,0.2);
        }

        /* === STEP INDICATOR === */
        .steps {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            padding: 32px 24px 8px;
            position: relative;
            z-index: 1;
        }
        .step-item {
            display: flex;
            align-items: center;
            gap: 0;
        }
        .step-node {
            width: 36px; height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: var(--font-mono);
            font-size: 13px;
            font-weight: 600;
            border: 2px solid var(--border);
            background: var(--bg-secondary);
            color: var(--text-muted);
            transition: all 0.4s ease;
            position: relative;
        }
        .step-node.active {
            border-color: var(--cyan);
            color: var(--cyan);
            background: var(--cyan-glow);
            box-shadow: 0 0 20px rgba(57,210,192,0.15);
        }
        .step-node.done {
            border-color: var(--green);
            color: var(--green);
            background: var(--green-glow);
        }
        .step-line {
            width: 80px;
            height: 2px;
            background: var(--border);
            position: relative;
            overflow: hidden;
        }
        .step-line.active::after {
            content: '';
            position: absolute;
            inset: 0;
            background: var(--cyan);
            animation: lineGrow 0.6s ease forwards;
        }
        @keyframes lineGrow {
            from { width: 0; }
            to { width: 100%; }
        }

        /* === MAIN CONTENT === */
        .main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            position: relative;
            z-index: 1;
        }

        /* === CARD === */
        .card {
            background: var(--gradient-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            width: 100%;
            max-width: 440px;
            overflow: hidden;
            animation: cardIn 0.5s ease;
        }
        .card.wide { max-width: 600px; }

        @keyframes cardIn {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .card-header {
            padding: 20px 24px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .card-header-dot {
            width: 10px; height: 10px;
            border-radius: 50%;
        }
        .dot-red { background: #f85149; }
        .dot-yellow { background: #d29922; }
        .dot-green { background: #3fb950; }
        .card-header-title {
            font-family: var(--font-mono);
            font-size: 12px;
            color: var(--text-muted);
            margin-left: 8px;
        }

        .card-body { padding: 24px; }

        .card-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 4px;
            background: var(--gradient-accent);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .card-subtitle {
            font-size: 13px;
            color: var(--text-secondary);
            margin-bottom: 24px;
            font-family: var(--font-mono);
        }

        /* === FORM === */
        .form-group { margin-bottom: 20px; }
        .form-label {
            display: block;
            font-family: var(--font-mono);
            font-size: 12px;
            color: var(--text-secondary);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .form-input {
            width: 100%;
            padding: 12px 16px;
            background: var(--bg-primary);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text-primary);
            font-family: var(--font-mono);
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-glow);
        }
        .form-input::placeholder { color: var(--text-muted); }
        .form-hint {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 6px;
            font-family: var(--font-mono);
        }
        .form-hint code {
            background: var(--bg-primary);
            padding: 1px 6px;
            border-radius: 4px;
            color: var(--orange);
            font-size: 11px;
        }

        /* === BUTTONS === */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-family: var(--font-sans);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--cyan), #2ea89e);
            color: #fff;
        }
        .btn-primary:hover {
            box-shadow: 0 4px 20px rgba(57,210,192,0.3);
            transform: translateY(-1px);
        }
        .btn-primary:disabled {
            background: var(--bg-tertiary);
            color: var(--text-muted);
            cursor: not-allowed;
            box-shadow: none;
            transform: none;
        }
        .btn-success {
            background: linear-gradient(135deg, var(--green), #2ea043);
            color: #fff;
        }
        .btn-success:hover {
            box-shadow: 0 4px 20px rgba(63,185,80,0.3);
            transform: translateY(-1px);
        }

        /* === PROCESS VIEW === */
        .process-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 20px;
        }
        .stat-box {
            background: var(--bg-primary);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 14px;
        }
        .stat-label {
            font-family: var(--font-mono);
            font-size: 10px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }
        .stat-value {
            font-family: var(--font-mono);
            font-size: 22px;
            font-weight: 700;
            color: var(--cyan);
        }
        .stat-value.total { color: var(--purple); }

        /* Progress bar */
        .progress-wrap { margin-bottom: 20px; }
        .progress-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
            font-family: var(--font-mono);
            font-size: 11px;
            color: var(--text-secondary);
        }
        .progress-bar {
            width: 100%;
            height: 6px;
            background: var(--bg-primary);
            border-radius: 3px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--cyan), var(--accent));
            border-radius: 3px;
            transition: width 0.4s ease;
            position: relative;
        }
        .progress-fill::after {
            content: '';
            position: absolute;
            right: 0; top: 0; bottom: 0;
            width: 20px;
            background: rgba(255,255,255,0.3);
            animation: shimmer 1.5s infinite;
        }
        @keyframes shimmer {
            0% { opacity: 0; }
            50% { opacity: 1; }
            100% { opacity: 0; }
        }

        /* Terminal log */
        .terminal {
            background: #0a0e14;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 14px;
            font-family: var(--font-mono);
            font-size: 12px;
            line-height: 1.8;
            margin-bottom: 20px;
            max-height: 100px;
            overflow-y: auto;
        }
        .terminal .line-prefix { color: var(--green); }
        .terminal .line-info { color: var(--text-secondary); }
        .terminal .line-accent { color: var(--cyan); }
        .terminal .cursor-blink {
            display: inline-block;
            width: 7px;
            height: 14px;
            background: var(--cyan);
            animation: blink 1s step-end infinite;
            vertical-align: text-bottom;
            margin-left: 2px;
        }
        @keyframes blink {
            50% { opacity: 0; }
        }

        /* Finished state */
        .finished-icon {
            width: 56px; height: 56px;
            border-radius: 50%;
            background: var(--green-glow);
            border: 2px solid var(--green);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            font-size: 24px;
            animation: popIn 0.4s ease;
        }
        @keyframes popIn {
            0% { transform: scale(0); }
            70% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        .finished-text {
            text-align: center;
            font-size: 16px;
            font-weight: 600;
            color: var(--green);
            margin-bottom: 20px;
        }

        /* Hidden form for auto-submit */
        .hidden-form { display: none; }
    </style>
</head>
<body>

    <!-- Top Bar -->
    <div class="topbar">
        <div class="topbar-left">
            <div class="topbar-icon"></div>
            <div class="topbar-title"><span>~/</span>developer-tools</div>
        </div>
        <div class="topbar-badge">DEV MODE</div>
    </div>

    <!-- Steps -->
    <div class="steps">
        <div class="step-item">
            <div class="step-node {{ $step === 'form' ? 'active' : 'done' }}">1</div>
        </div>
        <div class="step-line {{ $step === 'process' ? 'active' : '' }}"></div>
        <div class="step-item">
            <div class="step-node {{ $step === 'process' && !($isFinished ?? false) ? 'active' : '' }} {{ ($isFinished ?? false) ? 'done' : '' }}">2</div>
        </div>
        <div class="step-line {{ ($isFinished ?? false) ? 'active' : '' }}"></div>
        <div class="step-item">
            <div class="step-node {{ ($isFinished ?? false) ? 'active' : '' }}">3</div>
        </div>
    </div>

    <!-- Main -->
    <div class="main">
        @if ($step === 'form')
        <div class="card">
            <div class="card-header">
                <div class="card-header-dot dot-red"></div>
                <div class="card-header-dot dot-yellow"></div>
                <div class="card-header-dot dot-green"></div>
                <div class="card-header-title">generate_simpanan.sh</div>
            </div>
            <div class="card-body">
                <div class="card-title">Generate Simpanan</div>
                <div class="card-subtitle">// rebuild real_simpanan table</div>

                <form action="{{ url('/generate_simpanan') }}" method="get">
                    <div class="form-group">
                        <label class="form-label" for="id">CIF Target</label>
                        <input class="form-input" type="text" id="id" name="id" placeholder="kosongkan untuk semua CIF" autocomplete="off">
                        <div class="form-hint">Untuk sebagian CIF, gunakan format: <code>12, 45, 78</code></div>
                    </div>
                    <button class="btn btn-primary" type="submit">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                        Mulai Generate
                    </button>
                </form>
            </div>
        </div>

        @elseif ($step === 'process')
        @php
            $processed = min($start, $total);
            $pct = $total > 0 ? round(($processed / $total) * 100) : 100;
        @endphp

        <div class="card wide">
            <div class="card-header">
                <div class="card-header-dot dot-red"></div>
                <div class="card-header-dot dot-yellow"></div>
                <div class="card-header-dot dot-green"></div>
                <div class="card-header-title">{{ $isFinished ? 'process complete' : 'processing...' }}</div>
            </div>
            <div class="card-body">

                <div class="process-stats">
                    <div class="stat-box">
                        <div class="stat-label">Diproses</div>
                        <div class="stat-value">{{ $processed }}</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-label">Total</div>
                        <div class="stat-value total">{{ $total }}</div>
                    </div>
                </div>

                <div class="progress-wrap">
                    <div class="progress-info">
                        <span>Progress</span>
                        <span>{{ $pct }}%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: {{ $pct }}%"></div>
                    </div>
                </div>

                <div class="terminal">
                    <div><span class="line-prefix">$</span> <span class="line-info">generate_simpanan</span> <span class="line-accent">--batch={{ $limit }}</span></div>
                    <div><span class="line-prefix">></span> <span class="line-info">processed {{ $processed }}/{{ $total }} records</span></div>
                    @if ($isFinished)
                    <div><span class="line-prefix">✓</span> <span style="color:var(--green)">done.</span></div>
                    @else
                    <div><span class="line-prefix">></span> <span class="line-info">running</span><span class="cursor-blink"></span></div>
                    @endif
                </div>

                @if ($isFinished)
                    <div class="finished-icon">✓</div>
                    <div class="finished-text">Generate simpanan selesai!</div>
                    <a href="{{ url('/generate_bunga.php') }}" class="btn btn-success">
                        Next Step →
                    </a>
                @endif

                {{-- Hidden auto-submit form --}}
                @if (!$isFinished)
                <form action="{{ url('/generate_simpanan') }}" method="get" id="runForm" class="hidden-form">
                    <input type="hidden" name="id" value="{{ $id }}">
                    <input type="hidden" name="start" value="{{ $start }}">
                    <input type="hidden" name="limit" value="{{ $limit }}">
                    <input type="hidden" name="migrate" value="1">
                </form>
                <script>
                    window.addEventListener('load', function() {
                        setTimeout(function() {
                            document.getElementById('runForm').submit();
                        }, 800);
                    });
                </script>
                @endif
            </div>
        </div>
        @endif
    </div>
</body>
</html>
