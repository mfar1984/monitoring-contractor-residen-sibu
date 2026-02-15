<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Under Maintenance</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .maintenance-container {
            background: white;
            border-radius: 16px;
            padding: 60px 40px;
            max-width: 600px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        
        .maintenance-icon {
            font-size: 120px;
            color: #667eea;
            margin-bottom: 30px;
        }
        
        h1 {
            font-size: 32px;
            color: #333333;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        p {
            font-size: 16px;
            color: #666666;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        
        .status-badge {
            display: inline-block;
            background: #ffc107;
            color: #333333;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin-top: 20px;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 30px;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="maintenance-container">
        <span class="material-symbols-outlined maintenance-icon">construction</span>
        <h1>System Under Maintenance</h1>
        <p>We're currently performing scheduled maintenance to improve your experience.</p>
        <p>The system will be back online shortly. Thank you for your patience.</p>
        <div class="status-badge">Maintenance in Progress</div>
        <br>
        <a href="{{ route('login') }}" class="back-link">← Back to Login</a>
    </div>
</body>
</html>
