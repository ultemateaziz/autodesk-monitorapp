{{--
    License Status Banner
    Included in every page. Shows based on licenseStatus shared from ViewServiceProvider.
    States handled:
      valid + days_left <= 7  → yellow warning banner
      expired                 → red full block overlay
      locked                  → red full block overlay
      not_configured          → orange banner (admin must enter key)
      unreachable             → grey warning banner (grace period)
--}}

@php
    $ls       = $licenseStatus ?? [];
    $lStatus  = $ls['status']   ?? 'not_configured';
    $daysLeft = $ls['days_left'] ?? null;
    $tier     = $ls['tier']      ?? '';
    $expires  = $ls['expires_at'] ?? '';
    $customer = $ls['customer']   ?? '';
@endphp

{{-- ── FULL BLOCK: Expired ─────────────────────────────────── --}}
@if ($lStatus === 'expired')
<div id="license-block-overlay" style="
    position:fixed; inset:0; z-index:99999;
    background:rgba(8,12,20,0.97);
    display:flex; align-items:center; justify-content:center;
    font-family:'Segoe UI',sans-serif;
">
    <div style="text-align:center; max-width:460px; padding:40px 32px;
        background:#131a27; border:1px solid rgba(239,68,68,0.3);
        border-radius:20px; box-shadow:0 20px 60px rgba(0,0,0,0.5);">
        <div style="width:64px;height:64px;border-radius:50%;background:rgba(239,68,68,0.12);
            display:flex;align-items:center;justify-content:center;margin:0 auto 20px;
            border:1px solid rgba(239,68,68,0.25);">
            <svg width="28" height="28" fill="#ef4444" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
            </svg>
        </div>
        <h2 style="color:#f87171;font-size:22px;font-weight:800;margin-bottom:8px;">Subscription Expired</h2>
        <p style="color:#94a3b8;font-size:14px;line-height:1.6;margin-bottom:6px;">
            Your ASCLAM subscription has expired.
        </p>
        @if ($expires)
        <p style="color:#64748b;font-size:13px;margin-bottom:20px;">
            Expired on: <strong style="color:#94a3b8;">{{ \Carbon\Carbon::parse($expires)->format('d M Y') }}</strong>
            &nbsp;·&nbsp; Plan: <strong style="color:#94a3b8;">{{ $tier }}</strong>
        </p>
        @endif
        <div style="background:rgba(239,68,68,0.07);border:1px solid rgba(239,68,68,0.15);
            border-radius:12px;padding:16px;margin-bottom:24px;">
            <p style="color:#fca5a5;font-size:13px;line-height:1.6;">
                Contact your LicenseHub administrator to renew your subscription key.
                Once renewed, enter the new key below.
            </p>
        </div>
        <a href="{{ route('license.activate') }}"
            style="display:inline-block;background:linear-gradient(135deg,#ef4444,#dc2626);
            color:white;padding:12px 28px;border-radius:10px;font-size:14px;
            font-weight:700;text-decoration:none;box-shadow:0 4px 16px rgba(239,68,68,0.35);">
            Enter New License Key
        </a>
    </div>
</div>

{{-- ── FULL BLOCK: Locked ──────────────────────────────────── --}}
@elseif ($lStatus === 'locked')
<div id="license-block-overlay" style="
    position:fixed; inset:0; z-index:99999;
    background:rgba(8,12,20,0.97);
    display:flex; align-items:center; justify-content:center;
    font-family:'Segoe UI',sans-serif;
">
    <div style="text-align:center; max-width:460px; padding:40px 32px;
        background:#131a27; border:1px solid rgba(245,158,11,0.3);
        border-radius:20px; box-shadow:0 20px 60px rgba(0,0,0,0.5);">
        <div style="width:64px;height:64px;border-radius:50%;background:rgba(245,158,11,0.1);
            display:flex;align-items:center;justify-content:center;margin:0 auto 20px;
            border:1px solid rgba(245,158,11,0.25);">
            <svg width="28" height="28" fill="#f59e0b" viewBox="0 0 24 24">
                <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1s3.1 1.39 3.1 3.1v2z"/>
            </svg>
        </div>
        <h2 style="color:#fbbf24;font-size:22px;font-weight:800;margin-bottom:8px;">Subscription Locked</h2>
        <p style="color:#94a3b8;font-size:14px;line-height:1.6;margin-bottom:20px;">
            This subscription has been locked by the LicenseHub administrator.<br>
            The system is currently inaccessible.
        </p>
        <div style="background:rgba(245,158,11,0.07);border:1px solid rgba(245,158,11,0.15);
            border-radius:12px;padding:16px;margin-bottom:24px;">
            <p style="color:#fde68a;font-size:13px;line-height:1.6;">
                Please contact your LicenseHub administrator to unlock this subscription.
            </p>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.1);
                color:#94a3b8;padding:10px 24px;border-radius:10px;font-size:13px;
                font-weight:600;cursor:pointer;">
                Sign Out
            </button>
        </form>
    </div>
</div>

{{-- ── BANNER: Expiring Soon (≤7 days) ──────────────────────── --}}
@elseif ($lStatus === 'valid' && $daysLeft !== null && $daysLeft <= 7)
<div id="license-expiry-banner" style="
    background:linear-gradient(90deg,rgba(245,158,11,0.12),rgba(245,158,11,0.06));
    border-bottom:1px solid rgba(245,158,11,0.25);
    padding:10px 24px; display:flex; align-items:center; gap:12px;
    font-family:'Segoe UI',sans-serif; position:relative; z-index:200;
">
    <span style="font-size:16px;">⚠️</span>
    <span style="font-size:13px;color:#fbbf24;font-weight:600;">
        Your subscription expires in
        <strong style="color:#fcd34d;">{{ $daysLeft }} day{{ $daysLeft != 1 ? 's' : '' }}</strong>
        @if ($expires)
            ({{ \Carbon\Carbon::parse($expires)->format('d M Y') }})
        @endif
        — Plan: <strong>{{ $tier }}</strong>
    </span>
    <span style="margin-left:auto;display:flex;gap:10px;align-items:center;">
        <a href="{{ route('license.activate') }}"
            style="font-size:12px;font-weight:700;color:#fbbf24;
            background:rgba(245,158,11,0.15);border:1px solid rgba(245,158,11,0.3);
            padding:5px 14px;border-radius:8px;text-decoration:none;">
            Renew Key
        </a>
        <button onclick="document.getElementById('license-expiry-banner').remove()"
            style="background:none;border:none;color:#64748b;font-size:16px;cursor:pointer;padding:0;">✕</button>
    </span>
</div>

{{-- ── BANNER: Not Configured ────────────────────────────────── --}}
@elseif ($lStatus === 'not_configured')
<div style="
    background:linear-gradient(90deg,rgba(99,102,241,0.1),rgba(99,102,241,0.05));
    border-bottom:1px solid rgba(99,102,241,0.2);
    padding:10px 24px; display:flex; align-items:center; gap:12px;
    font-family:'Segoe UI',sans-serif; z-index:200;
">
    <span style="font-size:16px;">🔑</span>
    <span style="font-size:13px;color:#818cf8;font-weight:600;">
        No subscription key configured. Activate your license to enable all features.
    </span>
    <a href="{{ route('license.activate') }}"
        style="margin-left:auto;font-size:12px;font-weight:700;color:#818cf8;
        background:rgba(99,102,241,0.12);border:1px solid rgba(99,102,241,0.25);
        padding:5px 14px;border-radius:8px;text-decoration:none;">
        Activate Now
    </a>
</div>

{{-- ── BANNER: LicenseHub Unreachable ───────────────────────── --}}
@elseif ($lStatus === 'unreachable')
<div style="
    background:rgba(100,116,139,0.08);
    border-bottom:1px solid rgba(100,116,139,0.15);
    padding:8px 24px; display:flex; align-items:center; gap:12px;
    font-family:'Segoe UI',sans-serif; z-index:200;
">
    <span style="font-size:14px;">📡</span>
    <span style="font-size:12px;color:#64748b;font-weight:500;">
        LicenseHub server is unreachable. Running in offline grace period. Last checked: {{ $ls['checked'] ?? 'unknown' }}
    </span>
</div>
@endif
