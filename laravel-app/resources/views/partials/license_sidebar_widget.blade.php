@php
    $_ls     = $licenseStatus ?? [];
    $_st     = $_ls['status']    ?? '';
    $_dl     = $_ls['days_left'] ?? null;
    $_warn   = in_array($_st, ['expired', 'locked'])
               || ($_st === 'valid' && $_dl !== null && $_dl <= 30);
    $_red    = in_array($_st, ['expired', 'locked']);
    $_bg     = $_red ? 'rgba(239,68,68,0.08)'   : 'rgba(245,158,11,0.08)';
    $_border = $_red ? 'rgba(239,68,68,0.2)'     : 'rgba(245,158,11,0.18)';
    $_color  = $_red ? '#f87171'                 : '#fbbf24';
    $_icon   = $_st === 'expired' ? '⛔' : ($_st === 'locked' ? '🔒' : '⚠️');
    $_sub    = $_red ? 'Click to renew' : 'Expiring soon';

    if ($_st === 'expired')       { $_label = 'License Expired'; }
    elseif ($_st === 'locked')    { $_label = 'License Locked'; }
    elseif ($_dl === 0)           { $_label = 'Expires Today'; }
    else                          { $_label = $_dl . ' day' . ($_dl != 1 ? 's' : '') . ' left'; }
@endphp

@if($_warn)
<a href="{{ route('license.activate') }}"
   style="display:flex;align-items:center;gap:8px;margin-top:12px;padding:8px 10px;
          border-radius:8px;text-decoration:none;
          background:{{ $_bg }};border:1px solid {{ $_border }};">
    <span style="font-size:14px;flex-shrink:0;">{{ $_icon }}</span>
    <div class="user-info-text">
        <span class="user-name" style="font-size:11px;color:{{ $_color }};">{{ $_label }}</span>
        <span class="user-role">{{ $_sub }}</span>
    </div>
</a>
@endif
