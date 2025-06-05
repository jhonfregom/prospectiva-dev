<?php include( resource_path( 'config/shared-variables/main.php' ) ) ?>
@extends('layouts.main')
@section('title','App')
@section('class-body','has-aside-left-menu')
@section('class-footer','is-hidden')
@section('content')
    {{-- Main App --}}
    <main-app
        ref="mainApp"
        v-bind:urls_json="'{{ json_encode($list_urls) }}'"
        v-bind:texts_json="'{{ json_encode($texts) }}'"
        v-bind:locale="'{{ App::currentLocale() }}'"
        v-bind:fields_json="'{{ json_encode($fields) }}'"
        />
    {{-- /Main App --}}
@endsection
