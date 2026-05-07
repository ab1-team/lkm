<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Simpanan &mdash; SI LKM Online</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .step-header {
            background-color: #5DC4BF;
            color: white;
            padding: 15px 0;
            width: 100%;
        }
        .step-container {
            display: flex;
            justify-content: center;
            max-width: 600px;
            margin: 0 auto;
        }
        .step {
            flex: 1;
            text-align: center;
            padding: 10px;
            font-weight: bold;
        }
        .step.active {
            background-color: #47B3AE;
        }
        .content {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .form-container, .result-container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        .result-container {
            width: 80%;
            max-width: 600px;
        }
        h2 {
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }
        .form-group {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        label {
            flex: 0 0 80px;
            margin-bottom: 0;
            margin-right: 10px;
            color: #666;
        }
        select, input[type="text"] {
            flex: 1;
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        input[type="submit"], button {
            background-color: #5DC4BF;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        input[type="submit"]:hover, button:hover {
            background-color: #47B3AE;
        }
        input[type="submit"]:disabled, button:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }
        .result-text {
            margin-bottom: 20px;
            color: #5DC4BF;
            font-weight: bold;
            font-size: 18px;
        }
        .result-text2 {
            margin-bottom: 20px;
            color: #333;
        }
        .result-text3 {
            margin-bottom: 20px;
            text-align: right;
            margin-top: -15px;
            color: #333;
            font-size: 11px;
        }
        .process-form {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .process-form input[type="text"] {
            width: 70px;
            margin-right: 10px;
        }
        .back-button {
            background-color: #5DC4BF;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
            transition: background-color 0.3s;
        }
        .back-button:hover {
            background-color: #47B3AE;
        }
    </style>
</head>
<body>
    <header class="step-header">
        <div class="step-container">
            <div class="step active">STEP 1</div>
            <div class="step">STEP 2</div>
            <div class="step">STEP 3</div>
        </div>
    </header>

    <div class="content">
        @if ($step === 'form')
            <div class="form-container">
                <h2>Proses Generate Simpanan</h2>
                <form action="{{ url('/generate_simpanan') }}" method="get">
                    <div class="form-group">
                        <label for="id">CIF</label>
                        <input type="text" id="id" name="id" placeholder="semua CIF">
                    </div>
                    <div class="result-text3">jika hanya sebagian, ketiklah : <strong>CIF, CIF </strong>dst</div>
                    <input type="submit" value="Kirim">
                </form>
            </div>
        @elseif ($step === 'process')
            <div class="result-container">
                <div class="result-text2">Total Simpanan yang akan di proses adalah {{ $total }} data.</div>

                <form action="{{ url('/generate_simpanan') }}" method="get" class="process-form" id="runForm">
                    <input type="hidden" name="id" value="{{ $id }}">
                    <input type="text" name="start" id="start" value="{{ $start }}" readonly>
                    <input type="hidden" name="limit" id="limit" value="{{ $limit }}" readonly>
                    <button type="submit" name="migrate" id="runButton" {{ $isFinished ? 'disabled' : '' }}>
                        Loading<span id="loadingDots">.</span>
                    </button>
                </form>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        var button = document.getElementById('runButton');
                        var loadingDots = document.getElementById('loadingDots');
                        var dotCount = 0;

                        function animateDots() {
                            dotCount = (dotCount % 4) + 1;
                            loadingDots.textContent = '.'.repeat(dotCount);
                        }

                        if (!button.disabled) {
                            var interval = setInterval(animateDots, 500);

                            button.addEventListener('click', function() {
                                clearInterval(interval);
                                button.disabled = true;
                            });
                        }
                    });
                </script>

                @if ($isFinished)
                    <div class="result-text">Proses generate simpanan telah selesai</div>
                    <button onclick="window.location.href='{{ url('/generate_bunga.php') }}'" class="back-button">Next Step</button>
                @endif
            </div>

            @if (!$isFinished)
                <script>
                    window.onload = function() {
                        setTimeout(function() {
                            document.getElementById('runForm').submit();
                        }, 1000);
                    }
                </script>
            @endif
        @endif
    </div>
</body>
</html>
