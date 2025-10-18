<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>503 - Service Unavailable</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
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
            background: linear-gradient(135deg, #a8edea, #fed6e3);
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
            background: #f0fdf4;
            border-left: 4px solid #10b981;
            padding: 20px;
            border-radius: 10px;
            margin: 30px 0;
            text-align: left;
        }
        .details strong {
            color: #2d3748;
            display: block;
            margin-bottom: 10px;
        }
        .details ul {
            color: #065f46;
            padding-left: 20px;
        }
        .details li {
            margin: 5px 0;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #a8edea, #fed6e3);
            color: #2d3748;
            text-decoration: none;
            border-radius: 30px;
            font-weight: 600;
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(168, 237, 234, 0.3);
        }
        .icon {
            font-size: 80px;
            margin-bottom: 20px;
        }
        .maintenance {
            background: #fff7ed;
            border: 2px dashed #fb923c;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }
        .maintenance p {
            color: #9a3412;
            font-size: 0.95em;
            margin: 0;
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
        <div class="icon">🔧</div>
        <div class="error-code">503</div>
        <h1>Service Unavailable</h1>
        <p>The service is temporarily unavailable. We're working to restore it as quickly as possible.</p>

        <div class="details">
            <strong>What's happening?</strong>
            <ul>
                <li>The server is under maintenance</li>
                <li>We're experiencing high traffic</li>
                <li>Temporary technical difficulties</li>
            </ul>
        </div>

        <div class="maintenance">
            <p><strong>⏱️ Please try again in a few minutes</strong></p>
        </div>

        <div style="margin-top: 30px;">
            <a href="javascript:location.reload()" class="btn">Retry</a>
        </div>
    </div>
</body>
</html>
