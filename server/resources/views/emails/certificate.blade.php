<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Certificate of Completion - The Fibonacci Gallery</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: #0f1311;
            margin: 0;
            padding: 24px 12px;
            color: #d1d5db;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #141a17;
            border-radius: 16px;
            border: 1px solid #233229;
            overflow: hidden;
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.6);
        }

        .header {
            background: linear-gradient(135deg, #0e241b 0%, #17382b 100%);
            padding: 36px 24px;
            text-align: center;
            border-bottom: 2px solid #b38728;
        }

        .header h1 {
            color: #fbf9f4;
            margin: 0;
            font-size: 22px;
            letter-spacing: 0.15em;
            text-transform: uppercase;
        }

        .header p {
            color: #cbb479;
            margin: 6px 0 0 0;
            font-size: 11px;
            letter-spacing: 0.25em;
            text-transform: uppercase;
        }

        .body {
            padding: 32px 28px;
        }

        .badge-pill {
            display: inline-block;
            background: rgba(179, 135, 40, 0.15);
            border: 1px solid rgba(179, 135, 40, 0.4);
            color: #e5c378;
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            padding: 4px 12px;
            border-radius: 9999px;
            margin-bottom: 16px;
        }

        h2 {
            color: #ffffff;
            font-size: 22px;
            margin: 0 0 12px 0;
        }

        p {
            color: #9ca3af;
            line-height: 1.65;
            font-size: 14px;
            margin: 0 0 16px 0;
        }

        .cert-card {
            background: #0b0e0d;
            border: 1px solid #283a30;
            border-left: 4px solid #b38728;
            border-radius: 10px;
            padding: 18px 20px;
            margin: 22px 0;
        }

        .cert-card .course-name {
            color: #4ade80;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .cert-card .cert-id {
            font-family: 'Courier New', monospace;
            color: #e5c378;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.05em;
        }

        .cert-card .cert-date {
            color: #6b7280;
            font-size: 12px;
            margin-top: 4px;
        }

        .button {
            display: inline-block;
            margin: 8px 0 20px 0;
            padding: 14px 28px;
            background: #22c55e;
            color: #000000 !important;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            font-size: 14px;
            letter-spacing: 0.03em;
        }

        .attachment-note {
            background: rgba(34, 197, 94, 0.08);
            border: 1px dashed rgba(34, 197, 94, 0.3);
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 12.5px;
            color: #86efac;
            margin-top: 20px;
        }

        .footer {
            padding: 20px 24px;
            text-align: center;
            border-top: 1px solid #1f2a24;
            color: #6b7280;
            font-size: 12px;
            background: #0c100e;
        }

        .footer a {
            color: #22c55e;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>The Fibonacci Gallery</h1>
            <p>Academy of Institutional Market Dynamics</p>
        </div>

        <div class="body">
            <div class="badge-pill">★ Certificate of Completion</div>
            <h2>Congratulations, {{ $recipientName }}!</h2>
            <p>
                You have successfully completed all lessons and fulfilled the rigorous academic requirements for:
            </p>

            <div class="cert-card">
                <div class="course-name">{{ $courseTitle }}</div>
                <div class="cert-id">Certificate ID: {{ $certificateNumber }}</div>
                <div class="cert-date">Issued on {{ $issuedAt->format('F j, Y') }}</div>
            </div>

            <p>
                This credential recognizes your commitment to mastering institutional order flow, risk preservation, and high-probability market execution.
            </p>

            <div style="text-align: center; margin: 24px 0 16px 0;">
                <a href="{{ $verifyUrl }}" class="button" target="_blank">
                    Verify Certificate Online &rarr;
                </a>
            </div>

            <div class="attachment-note">
                📎 <strong>Official PDF Attached:</strong> Your high-resolution certificate is attached to this email ready to print, frame, or add to your professional portfolio.
            </div>
        </div>

        <div class="footer">
            <p>© {{ date('Y') }} The Fibonacci Gallery. All rights reserved.</p>
            <p>Verify this credential anytime at <a href="{{ $verifyUrl }}">{{ $verifyUrl }}</a></p>
        </div>
    </div>
</body>
</html>
