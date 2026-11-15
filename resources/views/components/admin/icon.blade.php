@props(['name'])

<svg {{ $attributes->merge(['class' => 'size-5']) }} fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
    @switch($name)
        @case('overview')
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75h6.5v6.5h-6.5zm10 0h6.5v6.5h-6.5zm-10 10h6.5v6.5h-6.5zm10 0h6.5v6.5h-6.5z" />
            @break
        @case('calendar')
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3.75v3m10.5-3v3M4.5 9.25h15M5.75 5.25h12.5a1.25 1.25 0 0 1 1.25 1.25v11.75a1.25 1.25 0 0 1-1.25 1.25H5.75A1.25 1.25 0 0 1 4.5 18.25V6.5a1.25 1.25 0 0 1 1.25-1.25Z" />
            @break
        @case('users')
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.25v-1.5a3.5 3.5 0 0 0-3.5-3.5h-5a3.5 3.5 0 0 0-3.5 3.5v1.5m6-9.5a3.25 3.25 0 1 0 0-6.5 3.25 3.25 0 0 0 0 6.5Zm8.75 9.5v-1.5a3.5 3.5 0 0 0-2.625-3.39m-1.625-10.8a3.25 3.25 0 0 1 0 6.29" />
            @break
        @case('badge')
            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3.75h9A2.25 2.25 0 0 1 18.75 6v12A2.25 2.25 0 0 1 16.5 20.25h-9A2.25 2.25 0 0 1 5.25 18V6A2.25 2.25 0 0 1 7.5 3.75Zm1.25 5.5h6.5m-6.5 3h6.5m-6.5 3h3" />
            @break
        @case('box')
            <path stroke-linecap="round" stroke-linejoin="round" d="m3.75 7.5 8.25-3.75 8.25 3.75M3.75 7.5 12 11.25m-8.25-3.75v9L12 20.25m0-9v9m0-9 8.25-3.75v9L12 20.25" />
            @break
        @case('inbox')
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 4.5h15v12.75a1.5 1.5 0 0 1-1.5 1.5H6a1.5 1.5 0 0 1-1.5-1.5V4.5Zm0 9.75h4.2l1.2 1.5h4.2l1.2-1.5h4.2" />
            @break
        @case('chart')
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5V4.5m0 15h15M8 16v-4m4 4V7.5m4 8.5v-6" />
            @break
        @case('bell')
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 18.75a2.5 2.5 0 0 1-4.5 0m8.25-2.25H6l1.25-1.75v-4.5a4.75 4.75 0 0 1 9.5 0v4.5L18 16.5Z" />
            @break
        @case('settings')
            <path stroke-linecap="round" stroke-linejoin="round" d="m9.6 3.7.45 1.35a7.2 7.2 0 0 1 1.95 0l.45-1.35 1.94.8-.3 1.38c.55.37 1.04.86 1.42 1.42l1.38-.3.8 1.94-1.35.45a7.2 7.2 0 0 1 0 1.95l1.35.45-.8 1.94-1.38-.3a7.03 7.03 0 0 1-1.42 1.42l.3 1.38-1.94.8-.45-1.35a7.2 7.2 0 0 1-1.95 0l-.45 1.35-1.94-.8.3-1.38a7.03 7.03 0 0 1-1.42-1.42l-1.38.3-.8-1.94 1.35-.45a7.2 7.2 0 0 1 0-1.95L4.1 8.94l.8-1.94 1.38.3c.38-.56.87-1.05 1.42-1.42l-.3-1.38 1.94-.8ZM12 13.75a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" />
            @break
        @case('logout')
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 8.25 18 12m0 0-3.75 3.75M18 12H7.5m5.25-7.5h-6A2.25 2.25 0 0 0 4.5 6.75v10.5a2.25 2.25 0 0 0 2.25 2.25h6" />
            @break
        @case('menu')
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 7.5h15m-15 4.5h15m-15 4.5h15" />
            @break
        @default
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" />
    @endswitch
</svg>
