@props(['name'])

<svg {{ $attributes->merge(['class' => 'size-5']) }} xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('overview')
            <rect x="3" y="3" width="7" height="7" rx="1.5"/>
            <rect x="14" y="3" width="7" height="7" rx="1.5"/>
            <rect x="3" y="14" width="7" height="7" rx="1.5"/>
            <rect x="14" y="14" width="7" height="7" rx="1.5"/>
            @break
        @case('calendar')
            <rect x="3" y="5" width="18" height="16" rx="2"/>
            <path d="M7 3v4M17 3v4M3 10h18"/>
            @break
        @case('map')
            <path d="m3 6 6-3 6 3 6-3v15l-6 3-6-3-6 3V6Z"/>
            <path d="M9 3v15m6-12v15"/>
            @break
        @case('file-pdf')
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/>
            <path d="M14 2v6h6M8 13h2a1.5 1.5 0 0 1 0 3H8v3m0-3h2m3 3v-6h1a3 3 0 0 1 0 6h-1m5-6h3m-3 0v6m0-3h2"/>
            @break
        @case('download')
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
            <path d="m7 10 5 5 5-5M12 15V3"/>
            @break
        @case('upload')
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
            <path d="m17 8-5-5-5 5m5-5v12"/>
            @break
        @case('external-link')
            <path d="M15 3h6v6m-11 5L21 3"/>
            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
            @break
        @case('users')
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
            @break
        @case('storefront')
            <path d="M3 10h18l-2-6H5l-2 6Z"/>
            <path d="M4 10v10h16V10M9 20v-7h6v7M3 10c0 1.7 3 2.5 4.5 0 1.5 2.5 3 2.5 4.5 0 1.5 2.5 3 2.5 4.5 0 1.5 2.5 4.5 1.7 4.5 0"/>
            @break
        @case('badge')
            <rect x="5" y="4" width="14" height="17" rx="2"/>
            <path d="M9 4V2h6v2M9 9h6M9 13h6M9 17h3"/>
            @break
        @case('badge-users')
            <circle cx="9" cy="8" r="2.5"/>
            <circle cx="16" cy="9" r="2"/>
            <path d="M3 19v-2a6 6 0 0 1 12 0v2H3ZM16 15a4.5 4.5 0 0 1 5 4h-4"/>
            @break
        @case('layers')
            <path d="m12 2 9 5-9 5-9-5 9-5ZM3 12l9 5 9-5M3 17l9 5 9-5"/>
            @break
        @case('box')
            <path d="m12 2 9 5-9 5-9-5 9-5ZM3 7v10l9 5 9-5V7M12 12v10"/>
            @break
        @case('inbox')
            <rect x="3" y="3" width="18" height="18" rx="2"/>
            <path d="M3 14h5l2 3h4l2-3h5"/>
            @break
        @case('chart')
            <path d="M3 3v18h18"/>
            <rect x="6" y="12" width="3" height="6" rx=".5"/>
            <rect x="11" y="8" width="3" height="10" rx=".5"/>
            <rect x="16" y="5" width="3" height="13" rx=".5"/>
            @break
        @case('bell')
            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9ZM10 21h4"/>
            @break
        @case('settings')
            <path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z"/>
            <path d="m19.4 15 .1.1 1.4 1.1-1.5 2.6-1.7-.7a8 8 0 0 1-2.1 1.2l-.3 1.8h-3l-.3-1.8a8 8 0 0 1-2.1-1.2l-1.7.7-1.5-2.6 1.4-1.1a8 8 0 0 1 0-2.4l-1.4-1.1L8.2 9l1.7.7A8 8 0 0 1 12 8.5l.3-1.8h3l.3 1.8a8 8 0 0 1 2.1 1.2l1.7-.7 1.5 2.6-1.4 1.1a8 8 0 0 1 0 2.3Z" transform="translate(-1.7 -2)"/>
            @break
        @case('logout')
            <path d="M10 17l5-5-5-5M15 12H3M12 3h7a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-7"/>
            @break
        @case('menu')
            <path d="M4 6h16M4 12h16M4 18h16"/>
            @break
        @case('user-plus')
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <path d="M19 8v6M16 11h6"/>
            @break
        @case('user')
            <circle cx="12" cy="8" r="4"/>
            <path d="M5 21v-1a7 7 0 0 1 14 0v1"/>
            @break
        @case('clipboard-check')
            <rect x="5" y="4" width="14" height="18" rx="2"/>
            <path d="M9 4V2h6v2m-7 11 2.5 2.5L16 12"/>
            @break
        @case('zap')
            <path d="m13 2-9 12h7l-1 8 10-12h-7l0-8Z"/>
            @break

@case('camera')
    <path d="M14 4h-4l-2 3H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-4l-2-3Z"/>
    <circle cx="12" cy="13" r="3.5"/>
    @break
        @case('lock')
            <rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 1 1 8 0v3M12 14v3"/>
            @break
        @case('eye')
            <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>
            @break

        @default
            <circle cx="12" cy="12" r="9"/>
            <path d="M12 8v4l3 2"/>
    @endswitch
</svg>
