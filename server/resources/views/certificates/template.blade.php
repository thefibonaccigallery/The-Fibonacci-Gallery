<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Certificate of Completion - {{ $recipient_name }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
        }

        body {
            margin: 0;
            padding: 0;
            width: 297mm;
            height: 210mm;
            font-family: 'Georgia', 'Times New Roman', serif;
            background-color: #fbf9f4;
            color: #1a2421;
            position: relative;
            overflow: hidden;
        }

        /* ─── LUXURY PARCHMENT BACKGROUND & SUBTLE BORDER ─── */
        .certificate-wrapper {
            position: absolute;
            top: 6mm;
            left: 6mm;
            width: 285mm;
            height: 198mm;
            background-color: #ffffff;
            border: 4px solid #b38728;
        }

        .inner-frame {
            position: absolute;
            top: 3.5mm;
            left: 3.5mm;
            width: 276mm;
            height: 189mm;
            border: 1.5px solid #0f281e;
        }

        .thin-gold-line {
            position: absolute;
            top: 2.5mm;
            left: 2.5mm;
            width: 269mm;
            height: 182mm;
            border: 1px dashed rgba(179, 135, 40, 0.45);
            pointer-events: none;
        }

        /* ─── CORNER ORNAMENTS ─── */
        .corner {
            position: absolute;
            width: 26px;
            height: 26px;
            color: #b38728;
        }

        .corner-tl {
            top: 4px;
            left: 4px;
        }

        .corner-tr {
            top: 4px;
            right: 4px;
        }

        .corner-bl {
            bottom: 4px;
            left: 4px;
        }

        .corner-br {
            bottom: 4px;
            right: 4px;
        }

        /* ─── WATERMARK ─── */
        .watermark-container {
            position: absolute;
            top: 42mm;
            left: 88mm;
            width: 100mm;
            height: 100mm;
            opacity: 0.045;
            pointer-events: none;
            z-index: 1;
        }

        .content-area {
            position: absolute;
            top: 5mm;
            left: 10mm;
            width: 256mm;
            text-align: center;
            z-index: 2;
        }

        /* ─── HEADER BRANDING & LOGO ─── */
        .logo-wrapper {
            margin: 0 auto 1.5mm auto;
            text-align: center;
        }

        .academy-logo {
            width: 24mm;
            height: 24mm;
            display: inline-block;
        }

        .academy-name {
            font-size: 14.5pt;
            letter-spacing: 0.28em;
            text-transform: uppercase;
            color: #0f281e;
            font-weight: bold;
            margin: 0;
            padding-top: 0;
        }

        .academy-subtitle {
            font-family: 'Arial', sans-serif;
            font-size: 7.2pt;
            letter-spacing: 0.35em;
            text-transform: uppercase;
            color: #8c6d23;
            margin-top: 2px;
            margin-bottom: 3.5mm;
            font-weight: 600;
        }

        .golden-rule {
            width: 120px;
            height: 1.5px;
            background: #b38728;
            margin: 0 auto 3.5mm auto;
        }

        /* ─── CERTIFICATE TITLE ─── */
        .certificate-title {
            font-size: 24pt;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #0f281e;
            font-weight: bold;
            margin: 0 0 1.5mm 0;
        }

        .certify-text {
            font-family: 'Georgia', serif;
            font-style: italic;
            font-size: 10.5pt;
            color: #555555;
            margin: 0 0 1.5mm 0;
        }

        /* ─── RECIPIENT NAME ─── */
        .recipient-name {
            font-size: 26pt;
            color: #0b1e16;
            font-weight: bold;
            letter-spacing: 0.04em;
            margin: 0.5mm 0 1.5mm 0;
            padding-bottom: 1.5mm;
            border-bottom: 2px solid #b38728;
            display: inline-block;
            min-width: 420px;
        }

        /* ─── COURSE FULFILLMENT ─── */
        .fulfillment-text {
            font-family: 'Arial', sans-serif;
            font-size: 8.2pt;
            color: #4a5550;
            letter-spacing: 0.05em;
            margin: 2.5mm 0 1.5mm 0;
            line-height: 1.4;
        }

        .course-title {
            font-size: 14pt;
            color: #0f281e;
            font-weight: bold;
            letter-spacing: 0.07em;
            text-transform: uppercase;
            margin: 1mm 0 4mm 0;
        }

        /* ─── FOOTER METADATA & SEAL ─── */
        .footer-table {
            width: 100%;
            margin-top: 18mm;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .footer-table td {
            vertical-align: middle;
            padding: 0 3mm;
        }

        .col-left {
            width: 32%;
            text-align: left !important;
        }

        .col-center {
            width: 36%;
            text-align: center !important;
        }

        .col-right {
            width: 32%;
            text-align: right !important;
        }

        .meta-label {
            font-family: 'Arial', sans-serif;
            font-size: 7.2pt;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: #7d8b84;
            margin-bottom: 3px;
        }

        .meta-value {
            font-size: 10.5pt;
            font-weight: bold;
            color: #0f281e;
            font-family: 'Georgia', serif;
        }

        .meta-sub {
            font-family: 'Arial', sans-serif;
            font-size: 7pt;
            color: #7d8b84;
            margin-top: 4px;
        }

        .cert-code {
            font-family: 'Courier New', monospace;
            font-size: 10pt;
            font-weight: bold;
            color: #8c6d23;
            letter-spacing: 0.08em;
        }

        .verify-url {
            font-family: 'Arial', sans-serif;
            font-size: 6.2pt;
            color: #7d8b84;
            margin-top: 4px;
            line-height: 1.4;
            word-break: break-all;
        }

        .seal-img {
            display: block;
            margin: 0 auto;
            width: 110px;
            height: 110px;
        }
    </style>
</head>

<body>
    @php
        $starL =
            '<polygon points="17,31 18.5,34.2 22,34.5 19.3,37 20.1,40.4 17,38.5 13.9,40.4 14.7,37 12,34.5 15.5,34.2" fill="#8c6d23"/>';
        $starR =
            '<polygon points="83,31 84.5,34.2 88,34.5 85.3,37 86.1,40.4 83,38.5 79.9,40.4 80.7,37 78,34.5 81.5,34.2" fill="#8c6d23"/>';
        $starB =
            '<polygon points="50,73.5 51.3,76.2 54.3,76.4 52,78.4 52.7,81.4 50,79.8 47.3,81.4 48,78.4 45.7,76.4 48.7,76.2" fill="#b38728"/>';
        $sealSvg =
            '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100">' .
            '<circle cx="50" cy="50" r="47.5" fill="#faf7ee" stroke="#b38728" stroke-width="2.6"/>' .
            '<circle cx="50" cy="50" r="44" fill="none" stroke="#d4af37" stroke-width="1"/>' .
            '<circle cx="50" cy="50" r="39.5" fill="none" stroke="#8c6d23" stroke-width="1.6" stroke-dasharray="3.5, 2"/>' .
            '<circle cx="50" cy="50" r="35.5" fill="none" stroke="#b38728" stroke-width="0.6"/>' .
            $starL .
            '<text x="50" y="39.5" text-anchor="middle" font-family="Helvetica, Arial, sans-serif" font-size="11.5" font-weight="bold" fill="#8c6d23" letter-spacing="1.2">OFFICIAL</text>' .
            $starR .
            '<text x="50" y="56" text-anchor="middle" font-family="Times-Roman, Georgia, serif" font-size="18.5" font-weight="bold" fill="#0f281e" letter-spacing="1.5">VERIFIED</text>' .
            '<line x1="18" y1="61" x2="82" y2="61" stroke="#b38728" stroke-width="1.5"/>' .
            '<text x="50" y="71" text-anchor="middle" font-family="Helvetica, Arial, sans-serif" font-size="9.5" font-weight="bold" fill="#8c6d23" letter-spacing="2">EXCELLENCE</text>' .
            $starB .
            '</svg>';
        $sealBase64 = 'data:image/svg+xml;base64,' . base64_encode($sealSvg);

        $parsedUrl = parse_url($verify_url);
        $displayHost = $parsedUrl['host'] ?? 'the-fibonacci-gallery.com';
        $displayPath = $parsedUrl['path'] ?? '/certificates/verify/' . $certificate_number;

        $logoPath = public_path('images/logo.png');
        $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;
    @endphp

    <div class="certificate-wrapper">
        <div class="inner-frame">
            <div class="thin-gold-line"></div>

            <!-- Corner Flourishes -->
            <svg class="corner corner-tl" viewBox="0 0 30 30" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M 2 28 L 2 2 L 28 2" />
                <path d="M 6 24 L 6 6 L 24 6" stroke-width="1" />
                <circle cx="6" cy="6" r="1.8" fill="currentColor" />
            </svg>
            <svg class="corner corner-tr" viewBox="0 0 30 30" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M 2 2 L 28 2 L 28 28" />
                <path d="M 6 6 L 24 6 L 24 24" stroke-width="1" />
                <circle cx="24" cy="6" r="1.8" fill="currentColor" />
            </svg>
            <svg class="corner corner-bl" viewBox="0 0 30 30" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M 2 2 L 28 2 L 28 28" />
                <path d="M 6 6 L 6 24 L 24 24" stroke-width="1" />
                <circle cx="6" cy="24" r="1.8" fill="currentColor" />
            </svg>
            <svg class="corner corner-br" viewBox="0 0 30 30" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M 28 2 L 28 28 L 2 28" />
                <path d="M 24 6 L 24 24 L 6 24" stroke-width="1" />
                <circle cx="24" cy="24" r="1.8" fill="currentColor" />
            </svg>

            <!-- Fibonacci Watermark Emblem -->
            <div class="watermark-container">
                <svg viewBox="0 0 200 200" fill="none" stroke="#b38728" stroke-width="2.5"
                    style="width: 100%; height: 100%;">
                    <path
                        d="M 20 180 A 160 160 0 0 1 180 20 A 100 100 0 0 1 80 120 A 60 60 0 0 1 140 180 A 38 38 0 0 1 102 142" />
                    <line x1="20" y1="120" x2="180" y2="120" stroke-dasharray="3 3"
                        opacity="0.6" />
                    <line x1="120" y1="20" x2="120" y2="180" stroke-dasharray="3 3"
                        opacity="0.6" />
                </svg>
            </div>

            <div class="content-area">
                <!-- Academy Crest / Logo -->
                @if ($logoBase64)
                    <div class="logo-wrapper">
                        <img src="{{ $logoBase64 }}" class="academy-logo" alt="The Fibonacci Gallery Logo">
                    </div>
                @endif

                <!-- Academy Brand -->
                <h1 class="academy-name">The Fibonacci Gallery</h1>
                <div class="academy-subtitle">Academy of Institutional Market Dynamics</div>
                <div class="golden-rule"></div>

                <!-- Certificate Title -->
                <h2 class="certificate-title">Certificate of Completion</h2>
                <p class="certify-text">This is proudly presented to</p>

                <!-- Student Full Name -->
                <div class="recipient-name">
                    {{ $recipient_name }}
                </div>

                <!-- Fulfillment Statement -->
                <p class="fulfillment-text">
                    for successfully demonstrating mastery and fulfilling all academic and strategic requirements for
                </p>

                <!-- Course Title -->
                <div class="course-title">
                    {{ $course_title }}
                </div>

                <!-- Footer Table -->
                <table class="footer-table">
                    <tr>
                        <!-- Left: Date of Issuance -->
                        <td class="col-left">
                            <div class="meta-label">Date of Issuance</div>
                            <div class="meta-value">{{ $issued_at->format('F j, Y') }}</div>
                            <div class="meta-sub">The Fibonacci Gallery Academic Board</div>
                        </td>

                        <!-- Center: Official Seal -->
                        <td class="col-center">
                            <img src="{{ $sealBase64 }}" width="110" height="110" alt="Official Seal"
                                class="seal-img">
                        </td>

                        <!-- Right: Certificate Verification ID -->
                        <td class="col-right">
                            <div class="meta-label">Certificate Verification ID</div>
                            <div class="cert-code">{{ $certificate_number }}</div>
                            <div class="verify-url">
                                <a href="{{ $verify_url }}" class="verify-link">
                                    https://{{ $displayHost }}<br>{{ $displayPath }}
                                </a>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</body>

</html>
