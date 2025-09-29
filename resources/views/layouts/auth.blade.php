<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ Vite::asset('resources/img/apple-touch-icon.png') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ Vite::asset('resources/img/favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ Vite::asset('resources/img/favicon-16x16.png') }}">
        <link rel="manifest" href="{{ Vite::asset('resources/img/site.webmanifest') }}">
        
        {{-- Google Fonts - Montserrat --}}
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

        <title>{{ config('app.name', 'Laravel') }} - @yield('title')</title>

        {{-- Additional head content --}}
        @yield('head')

        {{-- Styles/Scripts --}}
        @vite(['resources/js/app.js' ])
    </head>
    <body class="@yield('body-class')">
        <div id="app">
            {{-- Content --}}
            @yield('content')
        </div>
        <footer class="footer py-2 px-2 @yield('class-footer')">
            <div class="columns">
                <div class="column is-half">
                    @lang('footer.copyright')
                </div>
                <div class="column columns">
                    <div class="column"><a href="#">@lang('footer.terms')</a></div>
                    <div class="column"><a href="#">@lang('footer.policies')</a></div>
                </div>
            </div>
        </footer>

        {{-- Additional scripts --}}
        @yield('scripts')
    </body>
</html>
