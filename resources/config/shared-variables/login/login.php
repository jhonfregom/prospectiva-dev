<?php
    /**
    * Shared variables for login blade
    * use in blade include(resourse_path('config/shared-variables/login/login.php'))
    */

    $fields = array(
        'user'  =>  array(
            'placeholder'   => __('login.user_placeholder'),
            'error' => false,
            'msg'   =>  __('validation.required', ['attribute' => ''])
        ),
        'password'          =>  array(
            'placeholder'   => __('validation.attributes.password'),
            'error'         => false,
            'msg'           =>  __('validation.required', ['attribute' => '']),
            'caps_lock'     =>  __('app.caps_lock')
        ),
        'login' => array(
            'placeholder' =>  __('login.login')
        )
    );

    //List urls to use global in store from pinia/vue
    $list_urls = [
      // "home" => route('home'),
      // "login" => route('start_login'),
    ];
?>
