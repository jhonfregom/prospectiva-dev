<?php include( resource_path( 'config/shared-variables/login/login.php' ) ) ?>
@extends('layouts.main')
@section('title', __('login.login') )
@section('class-footer','static')
@section('content')
<div class="login">
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
                                <h1 class="title">{!! __('login.title') !!}</h1>
                            </div>
                        </article>
                        <h2 class="subtitle">{!! __('login.subtitle') !!}</h2>
                        <login-form
                            v-bind:csrf_token="'{{ csrf_token() }}'"
                            v-bind:urls_json="'{{ json_encode($list_urls) }}'"
                            v-bind:fields_json="'{{ json_encode($fields) }}'"
                            v-bind:success_message="{{ json_encode(session('success')) }}">
                        </login-form>
                        <div class="sub-content columns is-mobile is-centered">
                            <a class="column" href="{{ route('register') }}">{{ ucfirst( __('login.sign_up') ) }}</a>
                            <a class="column" href="{{ route('login_restore_password') }}">
                                {{ ucfirst( __('login.forgot_password') ) }}</a>
                            <a class="column" href="../">{{ ucfirst( __('login.need_help') ) }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
