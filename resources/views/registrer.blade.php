<?php include(resource_path('config/shared-variables/login/login.php')) ?>
@extends('layouts.main')
@section('title', 'registrer')
@section('class-footer', 'static')
@section('content')

<dic class="register">
    <section class="hero">
        <div class="hero-body">
            <div class="container has-text-centered">
                <div class="content">
                    <div class="box">
                        <article class="media">
                            <div class="media-left">
                                <figure class="avatar">
                                    <img class="image" src="{{ Vite::asset('resources/img/logo-1280.png') }}" alt="Cloud Back">
                                </figure>
                            </div>
                            <div class="media-content">
                                    <h1 class="title">Crear cuenta</h1>
                            </div>
                            /article>
                        <h2 class="subtitle">Únete a nuestra plataforma</h2>
                        <register-form
                            v-bind:csrf_token="'{{ csrf_token() }}'"
                            v-bind:urls_json="'{{ json_encode($list_urls) }}'">
                        </register-form>
                        <div class="sub-content columns is-mobile is-centered">
                            <a class="column" href="{{ route('login') }}">{{ __('login.login') }}</a>
                            <a class="column" href="../">{{ __('login.need_help') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
                                    
                                
                               