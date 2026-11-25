<!DOCTYPE html>
@props(['title' => null, 'toastMessages' => null])
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" type="image/png" href="{{ asset('assets/images/icons/favicon_io.png') }}">
        <title>{{ $title ?? config('app.name') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-100 text-slate-900">
        <main class="mx-auto flex min-h-screen w-full max-w-md items-center px-6 py-12">
            <section class="w-full rounded-xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
                <a href="{{ url('/') }}" class="mx-auto flex w-full justify-center" aria-label="Refrigair Expo 2027">
                    <img src="{{ asset('assets/images/Refrigairexpo-February-2-4-2027-OFEC-Casablanca-Morocco-Sans-titre-4-removebg-preview.png') }}" alt="Refrigair Expo 2027" class="h-auto max-h-20 w-full max-w-[260px] object-contain sm:max-h-24" />
                </a>

                {{ $slot }}
            </section>
        </main>
        <x-ui.toast :messages="$toastMessages" />
    </body>
</html>
