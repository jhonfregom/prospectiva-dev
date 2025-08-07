@extends('layouts.auth')
@section('title', 'Recuperar contraseña')
@section('class-footer','static')

@section('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="password-reset-url" content="{{ route('password.email') }}">
@endsection

@section('content')
<div class="restore-password">
    <section class="hero">
        <div class="hero-body">
            <div class="container has-text-centered">
                <div class="content">
                    <div class="box">
                        <article class="media">
                            <div class="media-left">
                                <figure class="avatar">
                                    <img class="image" src="{{ Vite::asset('resources/img/unad.png') }}" alt="Cloud Back">
                                </figure>
                            </div>
                            <div class="media-content">
                                <h1 class="title">🔐 Recuperar Contraseña</h1>
                            </div>
                        </article>
                        <h2 class="subtitle">Ingresa tu correo electrónico para recibir un enlace de restablecimiento</h2>
                        
                        <form id="restorePasswordForm">
                            <div class="field">
                                <label class="label">Correo Electrónico</label>
                                <div class="control">
                                    <input
                                        id="email"
                                        name="email"
                                        type="email"
                                        placeholder="Ingresa tu correo electrónico"
                                        class="input"
                                        required
                                    />
                                </div>
                            </div>

                            <div class="field">
                                <div class="control">
                                    <button
                                        type="submit"
                                        id="submitBtn"
                                        class="button is-primary is-fullwidth"
                                    >
                                        📧 Enviar Enlace de Restablecimiento
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- Mensaje de éxito -->
                        <div id="successMessage" class="notification is-success" style="display: none;">
                            <button class="delete" onclick="hideMessage('successMessage')"></button>
                            <span id="successText"></span>
                        </div>

                        <!-- Mensaje de error -->
                        <div id="errorMessage" class="notification is-danger" style="display: none;">
                            <button class="delete" onclick="hideMessage('errorMessage')"></button>
                            <span id="errorText"></span>
                        </div>

                        <div class="sub-content columns is-mobile is-centered">
                            <a class="column" href="{{ route('login') }}">← Volver al login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/password-reset.js') }}"></script>
@endsection
