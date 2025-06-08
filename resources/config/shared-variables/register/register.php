<?php

$list_urls = [
    'register' => route('start_register'),
    'login' => route('login'),
    'home' => route('home'),
];

$fields = [
    'first_name' => [
        'label' => __('register.first_name'),
        'placeholder' => __('register.first_name'),
        'error' => false,
        'msg' => '',
    ],
    'last_name' => [
        'label' => __('register.last_name'),
        'placeholder' => __('register.last_name'),
        'error' => false,
        'msg' => '',
    ],
    'user' => [
        'label' => __('register.user'),
        'placeholder' => __('register.user'),
        'error' => false,
        'msg' => '',
    ],
    'password' => [
        'label' => __('register.password'),
        'placeholder' => __('register.password'),
        'error' => false,
        'msg' => '',
    ],
    'confirm_password' => [
        'label' => __('register.confirm_password'),
        'placeholder' => __('register.confirm_password'),
        'error' => false,
        'msg' => '',
    ],
    'document_id' => [
        'label' => __('register.document_id'),
        'placeholder' => __('register.document_id'),
        'error' => false,
        'msg' => '',
    ],
    'submit' => [
        'label' => __('register.submit'),
    ],
];