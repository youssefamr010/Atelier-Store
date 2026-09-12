<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>Connection Interrupted — ATELIER</title>
    <meta name="theme-color" content="#000000">
    <style>
        :root {
            --bg: #F5F5F0;
            --surface: #FFFFFF;
            --text-main: #000000;
            --text-muted: #555555;
            --border: #000000;
            --shadow: 4px 4px 0px 0px #000000;
            --shadow-lg: 8px 8px 0px 0px #000000;
            --tape-bg: #121212;
            --tape-bg-dark: #050505;
            --tape-bg-light: #2c2c2c;
            --tape-text: #F5F5F0;
            --tape-border: rgba(255, 255, 255, 0.15);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: var(--bg);
            color: var(--text-main);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            text-align: center;
            overflow-x: hidden;
        }

        .offline-card {
            background-color: var(--surface);
            border: 2px solid var(--border);
            box-shadow: var(--shadow-lg);
            width: 100%;
            max-width: 680px;
            padding: 2.5rem 1.75rem;
            position: relative;
            z-index: 10;
        }

        .brand-header {
            font-family: "Cinzel", "Times New Roman", Times, serif;
            font-weight: 800;
            font-size: 1.25rem;
            letter-spacing: 0.25em;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            padding-bottom: 0.75rem;
        }

        .heading-error {
            font-family: "Cinzel", "Times New Roman", Times, serif;
            font-size: clamp(1.4rem, 4vw, 2.1rem);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            line-height: 1.2;
            color: var(--text-main);
            margin: 1.25rem 0 0.75rem;
        }

        .body-desc {
            font-size: 0.95rem;
            line-height: 1.6;
            color: var(--text-muted);
            max-width: 480px;
            margin: 0 auto 1.75rem;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.35rem 0.85rem;
            background: #FEE2E2;
            border: 1px solid #EF4444;
            color: #991B1B;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            margin-bottom: 1rem;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            background-color: #EF4444;
            border-radius: 50%;
            display: inline-block;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(0.85); }
        }

        .btn-group {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            width: 100%;
            max-width: 360px;
            margin: 0 auto;
        }

        @media(min-width: 480px) {
            .btn-group {
                flex-direction: row;
            }
        }

        .btn-primary {
            flex: 1;
            background-color: #000000;
            color: #FFFFFF;
            border: 2px solid #000000;
            box-shadow: var(--shadow);
            padding: 0.85rem 1.5rem;
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            text-decoration: none;
            cursor: pointer;
            transition: transform 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-primary:hover {
            transform: translate(-2px, -2px);
            box-shadow: 6px 6px 0px 0px #000000;
            background-color: #1a1a1a;
        }

        .btn-primary:active {
            transform: translate(2px, 2px);
            box-shadow: 2px 2px 0px 0px #000000;
        }

        .btn-secondary {
            flex: 1;
            background-color: #FFFFFF;
            color: #000000;
            border: 2px solid #000000;
            box-shadow: var(--shadow);
            padding: 0.85rem 1.5rem;
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            text-decoration: none;
            cursor: pointer;
            transition: transform 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-secondary:hover {
            transform: translate(-2px, -2px);
            box-shadow: 6px 6px 0px 0px #000000;
            background-color: #f5f5f0;
        }

        .btn-secondary:active {
            transform: translate(2px, 2px);
            box-shadow: 2px 2px 0px 0px #000000;
        }

        .footer-note {
            margin-top: 1.5rem;
            font-size: 0.75rem;
            color: #777777;
            letter-spacing: 0.05em;
        }
    </style>
</head>
<body>

    <div class="offline-card">
        <div class="brand-header">
            ATELIER STUDIO EGYPT
        </div>

        <div class="status-badge">
            <span class="status-dot"></span>
            <span>Network Disconnected</span>
        </div>

        <!-- Custom Inline SVG Crumpled Warning Tape -->
        <div style="margin: 0.5rem 0;">
            @include('components.crumpled-tape', [
                'text' => 'ATELIER 404 · CONNECTION INTERRUPTED · RETRYING LINK · ATELIER STUDIO EGYPT · STANDBY · '
            ])
        </div>

        <h1 class="heading-error">Connection Lost</h1>

        <p class="body-desc">
            We are having trouble reaching the server. Please check your internet connection or Wi-Fi network and try again.
        </p>

        <div class="btn-group">
            <button type="button" onclick="window.location.reload()" class="btn-primary">
                ↻ Retry Connection
            </button>
            <a href="/" class="btn-secondary">
                ← Homepage
            </a>
        </div>

        <p class="footer-note">
            Secure offline fallback cached by ATELIER PWA.
        </p>
    </div>

    <script>
        // Auto-reload when back online
        window.addEventListener('online', () => {
            window.location.reload();
        });
    </script>
</body>
</html>
