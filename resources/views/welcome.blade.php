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

    <style>
        .hero {
            background: none; /* quita el fondo aquí */
            min-height: auto; /* deja que crezca según el contenido */
        }

        .hero-body {
            /* background image set inline in HTML for Blade asset resolution */
            background-size: contain;
            background-color: white;
            height: 50%; /* ocupa todo el contenedor */
            min-height: 10vh; /* pantalla completa */
        }

        .hero-content {
            background: rgba(0, 0, 0, 0.4); /* opcional, da contraste */
            padding: 2rem;
            border-radius: 12px;
        }

        .hero h1 {
            font-size: 2rem;
            font-weight: bold;
        }

        .hero .button {
            margin-top: 3rem;
        }
    </style>
</head>
<body>
    <section class="hero is-large">
  <div class="hero-body" style="
      background: url('{{ Vite::asset('resources/img/unad2.png') }}') no-repeat center top;
      background-size: cover;">
    <div class="container">
      <h1 class="title has-text-white">Bienvenidos a Foresight Tool</h1>
      <a class="button is-primary is-medium" href="{{ route('login') }}">{{ __('login.login') }}</a>
    </div>
  </div>
</section>
</body>
</html>