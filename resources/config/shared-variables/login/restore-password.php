<?php
    /**
    * Shared variables for login restore password blade
    * use in blade include(resourse_path('config/shared-variables/login/restore-password.php'))
    */

    $textsRestore = __('app.restore_password');
    $textsRestore['accept'] = __('validation.attributes.accept');
    $fields = array(
        'email'     =>  array(
            'label' => 'Correo electrónico',
            'placeholder'   => 'Ingresa tu correo electrónico',
            'error' => false,
            'msg'   =>  'El campo correo electrónico es obligatorio.',
            'msg_validate'  =>  'El formato del correo electrónico no es válido.',
        ),
        'button'    => array(
            'placeholder' =>  'Enviar enlace de restablecimiento'
        )
    );

    //List urls to use global in store from pinia/vue
    $list_urls =[
        "login" => route('login'),
        "send_reset_link" => route('password.email'),
        // "participant_search_email_main_account" => route('participants_search_email_main_account'),
        // "back_send_email_restore_password" => route('backs_send_email_restore_password_main_account'),
    ];
?>
