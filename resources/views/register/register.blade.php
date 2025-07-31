<?php include( resource_path( 'config/shared-variables/register/register.php' ) ) ?>
@extends('layouts.main')
@section('title', __('register.register') )
@section('class-footer','static')
@section('content')
<div class="register">
    <section class="hero">
        <div class="hero-body">
            <div class="container has-text-centered">
                <div class="content">
                    <div class="box">
                        <article class="media">
                            <div class="media-left">
                                <figure class="avatar">
                                    <img class="image" src ="{{ Vite::asset('resources/img/unad.png') }}" alt="Cloud Back">
                                </figure>
                            </div>
                            <div class="media-content">
                                <h1 class="title">{!! __('register.title') !!}</h1> 
                            </div>
                        </article>
                        <h2 class="subtitle">{!! __('register.subtitle') !!}</h2>
                        <register-form
                            v-bind:csrf_token="'{{ csrf_token() }}'"
                            v-bind:urls_json="'{{ json_encode($list_urls) }}'"
                            v-bind:texts_json="'{{ json_encode($texts, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}'"
                            v-bind:fields_json="'{{ json_encode($fields) }}'">
                        </register-form>
                        <div class="sub-content columns is-mobile is-centered">
                            <a class="column" href="{{ route('login') }}">{{ ucfirst(__('register.login_link')) }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section> 
</div>
@endsection   
