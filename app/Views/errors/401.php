<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>401 - Unauthorized</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
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
            background: linear-gradient(135deg, #4facfe, #00f2fe);
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
            background: #e6fffa;
            border-left: 4px solid #00f2fe;
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
        .details p {
            color: #234e52;
            font-size: 0.95em;
            margin: 5px 0;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #4facfe, #00f2fe);
            color: white;
            text-decoration: none;
            border-radius: 30px;
            font-weight: 600;
            transition: transform 0.2s;
            margin: 5px;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(79, 172, 254, 0.3);
        }
        .btn-secondary {
            background: white;
            color: #4facfe;
            border: 2px solid #4facfe;
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
        <div class="icon">🔐</div>
        <div class="error-code">401</div>
        <h1>Unauthorized Access</h1>
        <p>You need to authenticate to access this resource. Please log in to continue.</p>

        <div class="details">
            <strong>Authentication Required</strong>
            <p>This page requires valid credentials. If you have an account, please log in. Otherwise, contact the administrator.</p>
        </div>

        <div>
            <a href="/login" class="btn">Go to Login</a>
            <a href="/" class="btn btn-secondary">Go to Homepage</a>
        </div>
    </div>
</body>
</html>
