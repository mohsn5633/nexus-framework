<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Internal Server Error</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .error-container {
            background: white;
            border-radius: 20px;
            padding: 60px 40px;
            max-width: 700px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        .error-code {
            font-size: 120px;
            font-weight: 800;
            background: linear-gradient(135deg, #f093fb, #f5576c);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            margin-bottom: 20px;
        }
        h1 {
            font-size: 2em;
            color: #2d3748;
            margin-bottom: 15px;
        }
        p {
            color: #718096;
            font-size: 1.1em;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        .error-details {
            background: #fff5f5;
            border-left: 4px solid #f5576c;
            padding: 20px;
            border-radius: 10px;
            margin: 30px 0;
            text-align: left;
        }
        .error-details h3 {
            color: #c53030;
            margin-bottom: 10px;
            font-size: 1.1em;
        }
        .error-details p {
            color: #742a2a;
            font-size: 0.95em;
            margin: 0;
        }
        .error-details code {
            background: #fed7d7;
            padding: 2px 8px;
            border-radius: 4px;
            font-family: monospace;
            color: #c53030;
            display: block;
            margin-top: 10px;
            white-space: pre-wrap;
            word-break: break-all;
        }
        .stack-trace {
            background: #1e1e2e;
            color: #f8f8f2;
            padding: 20px;
            border-radius: 10px;
            font-family: monospace;
            font-size: 0.85em;
            text-align: left;
            overflow-x: auto;
            margin-top: 20px;
            max-height: 300px;
            overflow-y: auto;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #f093fb, #f5576c);
            color: white;
            text-decoration: none;
            border-radius: 30px;
            font-weight: 600;
            transition: transform 0.2s;
            margin: 5px;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(245, 87, 108, 0.3);
        }
        .btn-secondary {
            background: white;
            color: #f5576c;
            border: 2px solid #f5576c;
        }
        .icon {
            font-size: 80px;
            margin-bottom: 20px;
        }
        @media (max-width: 600px) {
            .error-code { font-size: 80px; }
            h1 { font-size: 1.5em; }
            .error-container { padding: 40px 20px; }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="icon">⚠️</div>
        <div class="error-code">500</div>
        <h1>Internal Server Error</h1>
        <p>Something went wrong on our end. We're working to fix the problem. Please try again later.</p>

        <?php if (isset($debug) && $debug && isset($message)): ?>
        <div class="error-details">
            <h3>Error Details</h3>
            <p><?= htmlspecialchars($message) ?></p>

            <?php if (isset($file) && isset($line)): ?>
            <code><?= htmlspecialchars($file) ?>:<?= $line ?></code>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if (isset($debug) && $debug && isset($trace)): ?>
        <div class="stack-trace">
            <strong>Stack Trace:</strong><br><br>
            <?= htmlspecialchars($trace) ?>
        </div>
        <?php endif; ?>

        <div style="margin-top: 30px;">
            <a href="/" class="btn">Go to Homepage</a>
            <a href="javascript:history.back()" class="btn btn-secondary">Go Back</a>
        </div>
    </div>
</body>
</html>
