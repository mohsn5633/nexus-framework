<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            max-width: 600px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        .error-code {
            font-size: 120px;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea, #764ba2);
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
        .details {
            background: #f7fafc;
            padding: 20px;
            border-radius: 10px;
            margin: 30px 0;
            text-align: left;
        }
        .details strong {
            color: #2d3748;
        }
        .details code {
            background: #edf2f7;
            padding: 2px 8px;
            border-radius: 4px;
            font-family: monospace;
            color: #667eea;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            text-decoration: none;
            border-radius: 30px;
            font-weight: 600;
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
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
        <div class="icon">🔍</div>
        <div class="error-code">404</div>
        <h1>Page Not Found</h1>
        <p>The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.</p>

        <?php if (isset($path)): ?>
        <div class="details">
            <strong>Requested URL:</strong> <code><?= htmlspecialchars($path) ?></code>
        </div>
        <?php endif; ?>

        <a href="/" class="btn">Go to Homepage</a>
    </div>
</body>
</html>
