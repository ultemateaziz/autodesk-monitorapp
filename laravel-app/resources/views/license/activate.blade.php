<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>License Activation — ACLM</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: #0f172a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 16px;
            padding: 48px;
            width: 100%;
            max-width: 520px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
        }

        .logo {
            text-align: center;
            margin-bottom: 32px;
        }

        .logo-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 16px;
        }

        .logo h1 {
            color: #f1f5f9;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .logo p {
            color: #64748b;
            font-size: 14px;
            margin-top: 4px;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #1e3a5f;
            border: 1px solid #2563eb44;
            color: #60a5fa;
            font-size: 12px;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 20px;
            margin-bottom: 24px;
        }

        h2 {
            color: #f1f5f9;
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #94a3b8;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 28px;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 14px;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .alert-error {
            background: #2d1515;
            border: 1px solid #ef444444;
            color: #fca5a5;
        }

        .alert-success {
            background: #14291a;
            border: 1px solid #22c55e44;
            color: #86efac;
        }

        .alert-info {
            background: #1a1f2e;
            border: 1px solid #3b82f644;
            color: #93c5fd;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            color: #94a3b8;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 8px;
            letter-spacing: 0.3px;
        }

        .input-wrap {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #475569;
            font-size: 16px;
            pointer-events: none;
        }

        input[type="text"],
        input[type="url"] {
            width: 100%;
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: 10px;
            padding: 12px 14px 12px 42px;
            color: #f1f5f9;
            font-size: 14px;
            font-family: 'Courier New', monospace;
            letter-spacing: 1px;
            transition: border-color 0.2s;
            outline: none;
        }

        input[type="text"]:focus,
        input[type="url"]:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px #3b82f620;
        }

        input::placeholder { color: #475569; letter-spacing: 0; }

        .hint {
            color: #475569;
            font-size: 12px;
            margin-top: 6px;
        }

        .error-text {
            color: #f87171;
            font-size: 12px;
            margin-top: 6px;
        }

        .btn {
            width: 100%;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 14px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s, transform 0.1s;
            margin-top: 8px;
        }

        .btn:hover { opacity: 0.9; transform: translateY(-1px); }
        .btn:active { transform: translateY(0); }

        .divider {
            border: none;
            border-top: 1px solid #334155;
            margin: 28px 0;
        }

        .info-box {
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: 10px;
            padding: 16px;
        }

        .info-box p {
            color: #64748b;
            font-size: 13px;
            line-height: 1.7;
        }

        .info-box strong {
            color: #94a3b8;
        }

        .format-example {
            display: inline-block;
            background: #1e293b;
            border: 1px solid #334155;
            color: #60a5fa;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            padding: 4px 10px;
            border-radius: 6px;
            margin-top: 6px;
            letter-spacing: 1px;
        }

        @media (max-width: 480px) {
            .card { padding: 28px 20px; }
        }
    </style>
</head>
<body>

<div class="card">

    <div class="logo">
        <div class="logo-icon">🏗</div>
        <h1>ACLM</h1>
        <p>Autodesk Software Monitor</p>
    </div>

    <div class="badge">🔐 License Activation Required</div>

    <h2>Activate Your Subscription</h2>
    <p class="subtitle">
        Enter the license key provided by your administrator to activate this installation.
        The key will be verified with the LicenseHub server.
    </p>

    {{-- Alerts --}}
    @if(session('error'))
        <div class="alert alert-error">
            <span>⚠</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">
            <span>✅</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('info'))
        <div class="alert alert-info">
            <span>ℹ</span>
            <span>{{ session('info') }}</span>
        </div>
    @endif

    {{-- Cached status banner --}}
    @if(isset($cached) && in_array($cached['status'] ?? '', ['expired', 'locked']))
        <div class="alert alert-error">
            <span>🔒</span>
            <span>
                @if($cached['status'] === 'expired')
                    Your previous subscription expired. Please enter a new license key.
                @else
                    Your license has been locked by the administrator.
                @endif
            </span>
        </div>
    @endif

    <form action="{{ route('license.activate.post') }}" method="POST">
        @csrf

        {{-- License Key --}}
        <div class="form-group">
            <label>License Key</label>
            <div class="input-wrap">
                <span class="input-icon">🔑</span>
                <input
                    type="text"
                    name="license_key"
                    placeholder="AEPRO-XXXX-XXXX-XXXX"
                    value="{{ old('license_key') }}"
                    autocomplete="off"
                    spellcheck="false"
                    oninput="this.value = this.value.toUpperCase()"
                >
            </div>
            @error('license_key')
                <p class="error-text">{{ $message }}</p>
            @enderror
            <p class="hint">Format: <span class="format-example">AEPRO-XXXX-XXXX-XXXX</span></p>
        </div>

        {{-- LicenseHub Server URL --}}
        <div class="form-group">
            <label>LicenseHub Server URL</label>
            <div class="input-wrap">
                <span class="input-icon">🌐</span>
                <input
                    type="url"
                    name="license_server_url"
                    placeholder="http://192.168.0.201:8001"
                    value="{{ old('license_server_url', config('services.license_manager.url')) }}"
                    autocomplete="off"
                >
            </div>
            @error('license_server_url')
                <p class="error-text">{{ $message }}</p>
            @enderror
            <p class="hint">IP address of the LicenseHub server on your network</p>
        </div>

        <button type="submit" class="btn">
            🚀 Activate License
        </button>
    </form>

    <hr class="divider">

    <div class="info-box">
        <p>
            <strong>Need help?</strong><br>
            Contact your system administrator to obtain a license key from the LicenseHub portal.
            Each key is tied to one machine and cannot be shared.
        </p>
    </div>

</div>

</body>
</html>
