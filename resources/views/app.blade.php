<?php include( resource_path( 'config/shared-variables/main.php' ) ); ?>
@extends('layouts.main')
@section('title', __('app.welcome'))
<meta name="csrf-token" content="{{ csrf_token() }}">
@section('head')
    {{-- Incluye los archivos compilados por Vite --}}
    @vite(['resources/js/app.js', 'resources/css/app.css'])
@endsection

@section('content')
    {{-- Main App --}}
    <main-app
        ref="mainApp"
        v-bind:urls_json="'{{ json_encode($list_urls) }}'"
        v-bind:texts_json="'{{ json_encode($texts) }}'"
        v-bind:locale="'{{ App::currentLocale() }}'"
    />
    {{-- /Main App --}}
@endsection