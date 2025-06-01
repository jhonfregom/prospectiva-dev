<?php
    /**
    * Shared variables for main blade
    * use in blade include(resourse_path('config/shared-variables/main.php'))
    */

    $texts_home = __('app.home');
    $texts_messages = __('messages');

    $texts = [
        'home' => $texts_home,
        'messages' => $texts_messages
    ];

    //List urls to use global in store from pinia/vue
    $list_urls = [
        'home' => route('home'),
        'tasks' => [
                'index' => route('tasks.index'),
                'create' => route('task.store'),
            ]
    ];
