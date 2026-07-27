<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Your Password</title>
    <style>
        body {
            font-family: monospace;
            background: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 10px;
            overflow: hidden;
        }

        .header {
            background: oklch(0.77 0.2 131);
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 24px;
        }

        .body {
            padding: 30px;
        }

        .body p {
            color: #333333;
            line-height: 1.6;
        }

        .button {
            display: inline-block;
            margin: 20px 0;
            padding: 14px 32px;
            background: oklch(0.77 0.2 131);
            color: #ffffff;
            text-decoration: none;
            border-radius: 50px;
            font-weight: bold;
        }

        .footer {
            padding: 20px 30px;
            text-align: center;
            color: #999999;
            font-size: 12px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>The Fibonacci Gallery</h1>
        </div>
        <div class="body">
            <p>Hello {{ $fullName }},</p>
            <p>We received a request to reset your password. Click the button below to set a new password. This link
                will expire in <strong>60 minutes</strong>.</p>
            <a href="{{ $resetUrl }}" class="button">Reset Password</a>
            <p>If you did not request a password reset, you can safely ignore this email.</p>
            <p>For security, this link can only be used once.</p>
        </div>
        <div class="footer">
            <p>© {{ date('Y') }} The Fibonacci Gallery. All rights reserved.</p>
            <p>If the button above does not work, copy and paste this link into your browser:</p>
            <p>{{ $resetUrl }}</p>
        </div>
    </div>
</body>

</html>
