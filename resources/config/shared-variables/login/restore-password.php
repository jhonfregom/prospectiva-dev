<?php
    /**
    * Shared variables for login restore password blade
    * use in blade include(resourse_path('config/shared-variables/login/restore-password.php'))
    */

    $textsRestore = __('app.restore_password');
    $textsRestore['accept'] = __('validation.attributes.accept');
    $fields = array(
        'email'     =>  array(
            'placeholder'   => __('validation.attributes.email'),
            'error' => false,
            'msg'   =>  __('validation.required', ['attribute' => '']),
            'msg_validate'  =>  __('validation.email', ['attribute' => '']),
        ),
        'button'    => array(
            'placeholder' =>  __('app.restore_password.send_link')
        )
    );

    //List urls to use global in store from pinia/vue
    $list_urls =[
        "login" => route('login'),
        // "participant_search_email_main_account" => route('participants_search_email_main_account'),
        // "back_send_email_restore_password" => route('backs_send_email_restore_password_main_account'),
    ];
?>
