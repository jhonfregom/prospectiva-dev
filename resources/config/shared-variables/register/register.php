<?php

$list_urls = [
    'register' => route('start_register'),
    'login' => route('login'),
    'home' => route('home'),
];

$fields = [
    'registration_type' => [
        'label' => 'Tipo de Registro',
        'placeholder' => 'Seleccione el tipo de registro',
        'error' => false,
        'msg' => '',
    ],
    // Campos para Persona Natural
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
    'document_id' => [
        'label' => __('register.document_id'),
        'placeholder' => 'Cédula (10 dígitos)',
        'error' => false,
        'msg' => 'Falta el documento de identidad',
    ],
    'email' => [
        'label' => 'Correo electrónico',
        'placeholder' => 'Correo electrónico',
        'error' => false,
        'msg' => '',
    ],
    'city' => [
        'label' => 'Ciudad / Región',
        'placeholder' => 'Ciudad / Región',
        'error' => false,
        'msg' => '',
    ],
    // Campos para Empresa/Organización
    'company_name' => [
        'label' => 'Nombre de la empresa',
        'placeholder' => 'Nombre de la empresa',
        'error' => false,
        'msg' => '',
    ],
    'nit' => [
        'label' => 'NIT o número de identificación',
        'placeholder' => 'NIT (9 dígitos)',
        'error' => false,
        'msg' => '',
    ],
    'corporate_email' => [
        'label' => 'Correo electrónico corporativo',
        'placeholder' => 'Correo electrónico corporativo',
        'error' => false,
        'msg' => '',
    ],
    'company_city' => [
        'label' => 'Ciudad / Región',
        'placeholder' => 'Ciudad / Región',
        'error' => false,
        'msg' => '',
    ],
    'economic_sector' => [
        'label' => 'Sector económico',
        'placeholder' => 'Seleccione el sector económico',
        'error' => false,
        'msg' => '',
    ],
    // Campos comunes
    'user' => [
        'label' => 'Correo electrónico para el login',
        'placeholder' => 'Correo electrónico para el login',
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
    'submit' => [
        'label' => __('register.submit'),
    ],
];