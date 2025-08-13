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

        {{-- Styles/Scripts --}}
        {{-- No cargar Vue.js para páginas de activación --}}
        <script src="https:
        
        {{-- Estilos CSS para la página de activación --}}
        <style>
            
            * {
                box-sizing: border-box;
            }
            
            body {
                margin: 0;
                padding: 0;
                font-family: 'Montserrat', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
                background: linear-gradient(135deg, 
                min-height: 100vh;
            }

            .activation-container {
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }

            .activation-box {
                background: white;
                border-radius: 15px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.2);
                padding: 40px;
                max-width: 500px;
                width: 100%;
            }

            .activation-header {
                text-align: center;
                margin-bottom: 30px;
                border-bottom: 2px solid 
                padding-bottom: 20px;
            }
            
            .activation-header h1 {
                color: 
                margin: 0 0 10px 0;
                font-size: 28px;
            }
            
            .activation-header p {
                color: 
                margin: 0;
                font-size: 16px;
            }

            .user-info {
                background-color: 
                padding: 20px;
                border-radius: 10px;
                margin: 20px 0;
                border-left: 4px solid 
            }
            
            .user-info h3 {
                margin-top: 0;
                color: 
                font-size: 18px;
            }
            
            .info-row {
                display: flex;
                justify-content: space-between;
                margin: 12px 0;
                padding: 8px 0;
                border-bottom: 1px solid 
            }
            
            .info-label {
                font-weight: bold;
                color: 
            }
            
            .info-value {
                color: 
            }
            
            .status-pending {
                color: 
                font-weight: bold;
            }

            .activation-question {
                text-align: center;
                margin: 30px 0;
                padding: 20px;
                background-color: 
                border-radius: 10px;
            }
            
            .activation-question h3 {
                color: 
                margin: 0 0 10px 0;
                font-size: 20px;
            }
            
            .activation-question p {
                color: 
                margin: 0;
            }

            .activation-buttons {
                display: flex;
                gap: 15px;
                justify-content: center;
                margin: 30px 0;
            }
            
            .btn-activate, .btn-cancel {
                padding: 12px 24px;
                border: none;
                border-radius: 8px;
                font-size: 16px;
                font-weight: bold;
                cursor: pointer;
                transition: all 0.3s ease;
                min-width: 150px;
                text-decoration: none;
                display: inline-block;
            }
            
            .btn-activate {
                background-color: 
                color: white;
            }
            
            .btn-activate:hover:not(:disabled) {
                background-color: 
                transform: translateY(-2px);
            }
            
            .btn-cancel {
                background-color: 
                color: white;
            }
            
            .btn-cancel:hover:not(:disabled) {
                background-color: 
                transform: translateY(-2px);
            }
            
            .btn-activate:disabled, .btn-cancel:disabled {
                opacity: 0.6;
                cursor: not-allowed;
                transform: none;
            }

            .message {
                padding: 15px;
                border-radius: 8px;
                margin: 20px 0;
                text-align: center;
                font-weight: bold;
            }
            
            .message.success {
                background-color: 
                color: 
                border: 1px solid 
            }
            
            .message.error {
                background-color: 
                color: 
                border: 1px solid 
            }
            
            .message.info {
                background-color: 
                color: 
                border: 1px solid 
            }

            .footer {
                position: relative;
                margin-top: 0;
                background-color: 
                padding: 1rem;
                text-align: center;
            }
            
            .footer .columns {
                display: flex;
                justify-content: space-between;
                align-items: center;
                max-width: 1200px;
                margin: 0 auto;
            }
            
            .footer .column {
                flex: 1;
            }
            
            .footer a {
                color: 
                text-decoration: none;
            }
            
            .footer a:hover {
                text-decoration: underline;
            }

            @media (max-width: 600px) {
                .activation-box {
                    padding: 20px;
                    margin: 10px;
                }
                
                .activation-buttons {
                    flex-direction: column;
                }
                
                .info-row {
                    flex-direction: column;
                    gap: 5px;
                }
                
                .activation-header h1 {
                    font-size: 24px;
                }
                
                .user-info {
                    padding: 15px;
                }
            }
        </style>
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
                    <div class="column"><a href="
                    <div class="column"><a href="
                </div>
            </div>
        </footer>
    </body>
</html>