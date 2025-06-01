<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ Vite::asset('resources/img/apple-touch-icon.png') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ Vite::asset('resources/img/favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ Vite::asset('resources/img/favicon-16x16.png') }}">
        <link rel="manifest" href="{{ Vite::asset('resources/img/site.webmanifest') }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        {{-- Styles/Scripts --}}
        @vite(['resources/js/app.js' ])
    </head>
    <body>
        <section class="hero is-large is-info">
            <div class="hero-body">
                <h1>Bienvenidos al Proyecto Prospectiva</h1>
                <p class="subtitle">
                    <span class="icon"><i class="fas fa-home"></i></span>
                </p>
                <div class="columns">
                    <figure class="image is-128x128 column is-1">
                        <img src="{{ Vite::asset('resources/img/unad.png') }}" />
                    </figure>
                    <div class="column is-11">
                        <a class="button is-primary is-medium" href="{{ route('login') }}">{{ __('login.login') }}</a>
                    </div>
                </div>
            </div>
        </section>
    </body>
</html>