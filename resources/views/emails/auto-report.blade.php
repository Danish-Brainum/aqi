<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AQI Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #2d5016;
        }
        .message {
            margin: 20px 0;
            white-space: pre-line;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 14px;
            color: #666;
            white-space: pre-line;
        }
    </style>
</head>
<body>
    <div class="message">
        {{ $data['message'] ?? '' }}
    </div>
    
    <div class="footer">
       <b>Your breath matters to us.</b>
        Powered by Pulmonol, CCL Pakistan
    </div>
</body>
</html>
