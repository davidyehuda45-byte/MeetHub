<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Undangan Rapat - MeetHub</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f1f5f9;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #0f172a;
            -webkit-text-size-adjust: 100%;
        }
        .wrapper {
            width: 100%;
            background-color: #f1f5f9;
            padding: 32px 16px;
        }
        .main-card {
            max-width: 580px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .header-bar {
            background-color: #042f2e;
            background-image: linear-gradient(135deg, #042f2e 0%, #115e59 100%);
            padding: 24px 32px;
            text-align: left;
        }
        .logo-text {
            color: #ffffff;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.5px;
            margin: 0;
        }
        .logo-highlight {
            color: #2dd4bf;
        }
        .body-content {
            padding: 32px;
        }
        .greeting {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            margin-top: 0;
            margin-bottom: 8px;
        }
        .sub-greeting {
            font-size: 13px;
            color: #475569;
            margin-bottom: 24px;
            line-height: 1.5;
        }
        .meeting-box {
            background-color: #f8fafc;
            border: 1px solid #ccfbf1;
            border-left: 4px solid #0f766e;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
        }
        .meeting-title {
            font-size: 16px;
            font-weight: 800;
            color: #0f766e;
            margin-top: 0;
            margin-bottom: 12px;
        }
        .detail-row {
            margin-bottom: 8px;
            font-size: 13px;
            color: #334155;
            display: table;
            width: 100%;
        }
        .detail-label {
            font-weight: 600;
            color: #64748b;
            width: 120px;
            display: table-cell;
            vertical-align: top;
        }
        .detail-value {
            color: #0f172a;
            font-weight: 600;
            display: table-cell;
            vertical-align: top;
        }
        .btn-container {
            text-align: center;
            margin: 28px 0;
        }
        .btn-join {
            background-color: #0f766e;
            color: #ffffff !important;
            padding: 13px 28px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-google {
            display: inline-block;
            margin-top: 10px;
            font-size: 12px;
            color: #0f766e;
            text-decoration: none;
            font-weight: 600;
        }
        .divider {
            border-top: 1px solid #e2e8f0;
            margin: 24px 0;
        }
        .footer-content {
            font-size: 11px;
            color: #94a3b8;
            text-align: center;
            line-height: 1.5;
        }
        .code-badge {
            font-family: monospace;
            background-color: #e2e8f0;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 12px;
            color: #0f172a;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="main-card">
            <!-- Header -->
            <div class="header-bar">
                <div class="logo-text">Meet<span class="logo-highlight">Hub</span></div>
            </div>

            <!-- Body -->
            <div class="body-content">
                <h1 class="greeting">Undangan Rapat Online</h1>
                <p class="sub-greeting">
                    Halo, <strong>{{ $inviterName }}</strong> mengundang Anda untuk bergabung ke ruang konferensi video MeetHub.
                </p>

                <!-- Meeting Details Box -->
                <div class="meeting-box">
                    <h2 class="meeting-title">{{ $meeting->title }}</h2>
                    
                    <div class="detail-row">
                        <span class="detail-label">Waktu:</span>
                        <span class="detail-value">
                            {{ $meeting->scheduled_at ? $meeting->scheduled_at->translatedFormat('l, d F Y • H:i') . ' WIB' : 'Langsung Dimulai' }}
                        </span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Perkiraan Durasi:</span>
                        <span class="detail-value">{{ $meeting->duration_minutes }} Menit</span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Kode Rapat:</span>
                        <span class="detail-value"><span class="code-badge">{{ $meeting->room_code }}</span></span>
                    </div>

                    @if ($meeting->description)
                        <div class="detail-row">
                            <span class="detail-label">Agenda:</span>
                            <span class="detail-value" style="font-weight: normal; color: #475569;">{{ $meeting->description }}</span>
                        </div>
                    @endif
                </div>

                <!-- Call to Action -->
                <div class="btn-container">
                    <a href="{{ $joinUrl }}" class="btn-join" target="_blank">
                        Gabung ke Ruang Rapat
                    </a>
                    <br>
                    <a href="{{ $googleCalendarUrl }}" class="btn-google" target="_blank">
                        + Tambahkan ke Google Calendar
                    </a>
                </div>

                <div class="divider"></div>

                <div class="footer-content">
                    <p style="margin: 0 0 6px 0;">
                        Lampiran kalender resmi (<code>.ics</code>) disertakan dalam email ini untuk kalender Outlook, Apple, atau Thunderbird.
                    </p>
                    <p style="margin: 0;">
                        Tautan alternatif: <a href="{{ $joinUrl }}" style="color: #0f766e;">{{ $joinUrl }}</a>
                    </p>
                </div>
            </div>
        </div>

        <div style="text-align: center; margin-top: 16px; font-size: 11px; color: #94a3b8;">
            &copy; {{ date('Y') }} MeetHub. Semua hak dilindungi undang-undang.
        </div>
    </div>
</body>
</html>

