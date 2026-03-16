<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ArchEng Pro | Admin Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root[data-theme="dark"] {
            --bg:#0f1117;--surface:#161b27;--card:#1c2236;--border:rgba(255,255,255,0.07);
            --text:#e2e8f0;--muted:#64748b;--accent:#6366f1;--accent-glow:rgba(99,102,241,0.25);
            --input-bg:#0f1117;--error-bg:rgba(239,68,68,0.08);--error-border:rgba(239,68,68,0.25);--error-text:#f87171;
        }
        :root[data-theme="light"] {
            --bg:#f1f5f9;--surface:#ffffff;--card:#f8fafc;--border:rgba(0,0,0,0.08);
            --text:#0f172a;--muted:#94a3b8;--accent:#6366f1;--accent-glow:rgba(99,102,241,0.15);
            --input-bg:#f8fafc;--error-bg:rgba(239,68,68,0.06);--error-border:rgba(239,68,68,0.2);--error-text:#ef4444;
        }

        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Outfit',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex;align-items:center;justify-content:center;}

        /* Subtle animated background grid */
        body::before{
            content:'';position:fixed;inset:0;
            background-image:linear-gradient(rgba(99,102,241,0.03) 1px,transparent 1px),linear-gradient(90deg,rgba(99,102,241,0.03) 1px,transparent 1px);
            background-size:48px 48px;z-index:0;pointer-events:none;
        }

        .wrapper{position:relative;z-index:1;width:100%;max-width:420px;padding:24px;}

        .card{background:var(--surface);border:1px solid var(--border);border-radius:20px;padding:40px 36px;box-shadow:0 24px 60px rgba(0,0,0,0.3);}

        .brand{display:flex;flex-direction:column;align-items:center;margin-bottom:36px;}
        .brand-icon{width:60px;height:60px;background:linear-gradient(135deg,#6366f1,#8b5cf6);border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:26px;color:white;margin-bottom:14px;box-shadow:0 8px 24px rgba(99,102,241,0.35);}
        .brand-name{font-size:20px;font-weight:700;letter-spacing:-0.3px;}
        .brand-sub{font-size:12px;color:var(--muted);margin-top:4px;}

        .form-group{margin-bottom:18px;}
        .form-label{display:block;font-size:13px;font-weight:600;color:var(--muted);margin-bottom:7px;letter-spacing:0.2px;}
        .input-wrap{position:relative;}
        .input-icon{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:14px;pointer-events:none;}
        .form-input{width:100%;background:var(--input-bg);border:1px solid var(--border);border-radius:10px;color:var(--text);font-family:'Outfit',sans-serif;font-size:14px;padding:11px 14px 11px 38px;outline:none;transition:border-color 0.2s,box-shadow 0.2s;}
        .form-input:focus{border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-glow);}
        .form-input.error{border-color:var(--error-border);}

        .toggle-pass{position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--muted);cursor:pointer;font-size:14px;padding:2px 4px;transition:color 0.2s;}
        .toggle-pass:hover{color:var(--accent);}

        .error-box{background:var(--error-bg);border:1px solid var(--error-border);color:var(--error-text);border-radius:10px;padding:11px 14px;font-size:13px;display:flex;align-items:center;gap:9px;margin-bottom:18px;}

        .remember-row{display:flex;align-items:center;gap:8px;margin-bottom:22px;}
        .remember-row input[type="checkbox"]{width:16px;height:16px;accent-color:var(--accent);cursor:pointer;}
        .remember-row label{font-size:13px;color:var(--muted);cursor:pointer;}

        .btn-login{width:100%;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:white;border:none;border-radius:10px;padding:13px;font-family:'Outfit',sans-serif;font-size:15px;font-weight:700;cursor:pointer;transition:opacity 0.2s,transform 0.15s;display:flex;align-items:center;justify-content:center;gap:8px;letter-spacing:0.2px;}
        .btn-login:hover{opacity:0.92;transform:translateY(-1px);}
        .btn-login:active{transform:translateY(0);}

        .footer-note{text-align:center;margin-top:24px;font-size:12px;color:var(--muted);}
        .footer-note span{display:block;margin-top:4px;font-size:11px;opacity:0.6;}

        .theme-toggle{position:fixed;top:20px;right:20px;width:38px;height:38px;border-radius:10px;background:var(--surface);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--muted);transition:all 0.2s;font-size:14px;z-index:10;}
        .theme-toggle:hover{color:var(--accent);border-color:var(--accent);}
    </style>
</head>
<body>

<button class="theme-toggle" id="themeToggle" title="Toggle theme"><i class="fas fa-moon" id="themeIcon"></i></button>

<div class="wrapper">
    <div class="card">
        <div class="brand">
            <div class="brand-icon"><i class="fas fa-key"></i></div>
            <div class="brand-name">LicenseHub</div>
            <div class="brand-sub">ArchEng Pro — Admin Panel</div>
        </div>

        @if($errors->any())
        <div class="error-box">
            <i class="fas fa-circle-exclamation"></i>
            {{ $errors->first('email') }}
        </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <div class="input-wrap">
                    <i class="fas fa-envelope input-icon"></i>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-input {{ $errors->has('email') ? 'error' : '' }}"
                        value="{{ old('email') }}"
                        placeholder="admin@admin.com"
                        autocomplete="email"
                        autofocus
                        required
                    >
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <div class="input-wrap">
                    <i class="fas fa-lock input-icon"></i>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-input"
                        placeholder="••••••••"
                        autocomplete="current-password"
                        required
                    >
                    <button type="button" class="toggle-pass" id="togglePass" title="Show/hide password">
                        <i class="fas fa-eye" id="passIcon"></i>
                    </button>
                </div>
            </div>

            <div class="remember-row">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Keep me signed in</label>
            </div>

            <button type="submit" class="btn-login">
                <i class="fas fa-right-to-bracket"></i> Sign In
            </button>
        </form>

        <div class="footer-note">
            LicenseHub Admin Access Only
            <span>Unauthorised access is prohibited</span>
        </div>
    </div>
</div>

<script>
    const html = document.documentElement, themeIcon = document.getElementById('themeIcon');
    const saved = localStorage.getItem('lm-theme') || 'dark';
    html.setAttribute('data-theme', saved);
    themeIcon.className = saved === 'dark' ? 'fas fa-moon' : 'fas fa-sun';

    document.getElementById('themeToggle').addEventListener('click', () => {
        const t = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-theme', t);
        localStorage.setItem('lm-theme', t);
        themeIcon.className = t === 'dark' ? 'fas fa-moon' : 'fas fa-sun';
    });

    document.getElementById('togglePass').addEventListener('click', () => {
        const input = document.getElementById('password');
        const icon  = document.getElementById('passIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'fas fa-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'fas fa-eye';
        }
    });
</script>
</body>
</html>
