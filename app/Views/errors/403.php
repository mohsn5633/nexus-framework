<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Forbidden</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
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
            background: linear-gradient(135deg, #fa709a, #fee140);
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
            background: #fffaf0;
            border-left: 4px solid #ed8936;
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
            color: #744210;
            padding-left: 20px;
        }
        .details li {
            margin: 5px 0;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #fa709a, #fee140);
            color: white;
            text-decoration: none;
            border-radius: 30px;
            font-weight: 600;
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(250, 112, 154, 0.3);
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
        <div class="icon">🚫</div>
        <div class="error-code">403</div>
        <h1>Access Forbidden</h1>
        <p>You don't have permission to access this resource. Please contact the administrator if you believe this is an error.</p>

        <div class="details">
            <strong>Possible reasons:</strong>
            <ul>
                <li>You don't have the necessary permissions</li>
                <li>Your access has been restricted</li>
                <li>This resource requires authentication</li>
            </ul>
        </div>

        <a href="/" class="btn">Go to Homepage</a>
    </div>
</body>
</html>
