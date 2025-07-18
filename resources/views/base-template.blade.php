<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $businessName }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
        }

        .email-container {
            background-color: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }

        .logo {
            max-width:
                {{ $logoWidth }}
                px;
            height: auto;
            margin-bottom: 10px;
        }

        .business-name {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin: 0;
        }

        .content {
            margin-bottom: 30px;
        }

        .content h1 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }

        .content h2 {
            color: #34495e;
            margin-top: 25px;
        }

        .content p {
            margin-bottom: 15px;
        }

        .content ul,
        .content ol {
            margin-bottom: 15px;
            padding-left: 20px;
        }

        .content blockquote {
            border-left: 4px solid #3498db;
            padding-left: 15px;
            margin-left: 0;
            font-style: italic;
            color: #7f8c8d;
        }

        .content table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border: 1px solid #ddd;
        }

        .content th,
        .content td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            border-right: 1px solid #ddd;
        }

        .content th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: center;
        }

        .content td {
            text-align: center;
        }

        /* Button styles for email compatibility */
        .content a[style*="background-color"] {
            display: inline-block !important;
            padding: 12px 24px !important;
            text-decoration: none !important;
            border-radius: 5px !important;
            font-weight: bold !important;
            margin: 10px 0 !important;
            color: white !important;
        }

        .footer {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            font-size: 12px;
            color: #7f8c8d;
        }

        .footer-links {
            margin-bottom: 15px;
        }

        .footer-links a {
            color: #3498db;
            text-decoration: none;
            margin: 0 10px;
        }

        .footer-links a:hover {
            text-decoration: underline;
        }

        /* Mobile responsive */
        @media only screen and (max-width: 600px) {
            body {
                padding: 10px;
            }

            .email-container {
                padding: 20px;
            }

            .logo {
                max-width: 150px;
            }

            .business-name {
                font-size: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="email-container">
        <div class="header">
            @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="{{ $businessName }} Logo" class="logo" width="{{ $logoWidth }}"
                    height="{{ $logoHeight }}">
            @endif
            <h1 class="business-name">{{ $businessName }}</h1>
        </div>

        <div class="content">
            {!! $content !!}
        </div>

        <div class="footer">
            <div class="footer-links">
                @if($links['unsubscribe_url'])
                    <a href="{{ $links['unsubscribe_url'] }}">Unsubscribe</a>
                @endif
                @if($links['privacy_policy_url'])
                    <a href="{{ $links['privacy_policy_url'] }}">Privacy Policy</a>
                @endif
                @if($links['terms_of_service_url'])
                    <a href="{{ $links['terms_of_service_url'] }}">Terms of Service</a>
                @endif
            </div>
            <p>&copy; {{ date('Y') }} {{ $businessName }}. All rights reserved.</p>
        </div>
    </div>
</body>

</html>