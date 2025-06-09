<?php
    /**
    * Shared variables for main blade
    * use in blade include(resourse_path('config/shared-variables/main.php'))
    */

    //Text messages for global use
    $texts_messages =   [
        'apply' => __('validation.attributes.apply'),
        'accept' => __('validation.attributes.accept'),
        'cancel' => __('validation.attributes.cancel'),
        'continue'=> __('validation.attributes.continue'),
        'consult' => __('validation.attributes.consult'),
        'close' => __('validation.attributes.close'),
        'delete' => __('validation.attributes.delete'),
        'edit' => __('validation.attributes.edit'),
        'go_back' => __('validation.attributes.go_back'),
        'new' => __('validation.attributes.new'),
        'not'           => __('validation.attributes.not'),
        'no_options'    => __('messages.no_options'),
        'renew'         => __('validation.attributes.renew'),
        'return'        => __('validation.attributes.return'),
        'save'          => __('validation.attributes.save'),
        'success'  => __('messages.success'),
    ];

    //Text main section
    $text_main_section = __('app.main');

    //Texts invetory section
    $text_variables = array_merge(
        __('app.variables'), [
        'item' => __('validation.attributes.item'),
        'elements' => __('validation.attributes.elements'),
        'opening_balances' => __('validation.attributes.opening_balances'),
        'transfers' => __('validation.attributes.transfers'),
        'category' => __('validation.attributes.category'),
        'group' => __('validation.attributes.group'),
        'subgroup' => __('validation.attributes.subgroup'),
        'warehouse' => __('validation.attributes.warehouse'),
        'batch' => __('validation.attributes.batch'),
        'brand' => __('validation.attributes.brand'),
        'unit_of_measure' => __('validation.attributes.unit_of_measure'),
        'color' => __('validation.attributes.color'),
        'size_or_measurement' => __('validation.attributes.size_or_measurement'),
        'origin' => __('validation.attributes.origin'),
        'code' => __('validation.attributes.code'),
        'concept' => __('validation.attributes.concept'),
        'state' => __('validation.attributes.state'),
        'search' => __('validation.attributes.search'),
        'confirm_save' => __('validation.attributes.confirm_save'),
        'confirm_delete' => __('validation.attributes.confirm_delete'),
        'error_delete' => __('validation.attributes.error_delete'),
    ]);


    //Fields participan
    $fields_participant =   [
        'first_name' =>  [
            'label' =>  __('validation.attributes.first_name'),
            'error' => false,
            'msg'   =>  __('validation.required', ['attribute' => ''])
        ],
        'last_name' =>  [
            'label' =>  __('validation.attributes.last_name'),
            'error' => false,
            'msg'   =>  __('validation.required', ['attribute' => ''])
        ],
        'identification' =>  [
            'label'         =>  __('validation.attributes.personal_identification'),
            'error'         => false,
            'msg'           =>  __('validation.required', ['attribute' => '']),
            'msg_validate'  =>  __('validation.personal_identification', ['attribute' => '']),
            'msg_exist'     =>  __('validation.custom.personal_identification.unique', ['attribute' => __('validation.attributes.personal_identification')])
        ],
        'email' =>  [
            'label'             =>  __('validation.attributes.email'),
            'error'             => false,
            'msg'               =>  __('validation.required', ['attribute' => '']),
            'msg_validate'      =>  __('validation.email', ['attribute' => '']),
            'msg_exist'         =>  __('validation.custom.email.unique', ['attribute' => __('validation.attributes.email')])
        ],
        'cellphone' =>  [
            'label' =>  __('validation.attributes.mobile'),
            'placeholder'   =>  __('app.participant_new.cellphone_placeholder'),
            'error' => false,
            'msg'   =>  __('validation.required', ['attribute' => '']),
            'msg_validate'   =>  __('validation.mobile', ['attribute' => '']),
            'msg_exist'   =>  __('validation.custom.mobile.unique', ['attribute' => __('validation.attributes.mobile')])
        ],
        'position_company' =>  [
            'label' =>  __('validation.attributes.position_company'),
            'error' => false,
            'msg'   =>  __('validation.required', ['attribute' => ''])
        ],
        'date_join_company' =>  [
            'label' =>  __('validation.attributes.date_join_company'),
            'error' => false,
            'msg'   =>  __('validation.required', ['attribute' => ''])
        ],
        'birth_date' =>  [
            'label' =>  __('validation.attributes.birth_date'),
            'error' => false,
            'msg'   =>  __('validation.required', ['attribute' => ''])
        ],
        'profile' =>  [
            'label' =>  __('validation.attributes.profile'),
            'error' => false,
            'msg'   =>  __('validation.required', ['attribute' => '']),
        ],
        'profile_image' =>  [
            'label'             =>  __('validation.attributes.profile_image'),
            'error'             => false,
            'msg'               =>  __('validation.required', ['attribute' => '']),
            'msg_limit_size'    =>  __('messages.size_limit_logo_upload')
        ],
        'description' =>  [
            'label'         =>  __('validation.attributes.description'),
            'error'         => false,
            'msg'           =>  __('validation.required', ['attribute' => ''])
        ],
        'state' =>  [
            'label'     =>  __('validation.attributes.state'),
            'error'     => false,
            'msg'       =>  __('validation.required', ['attribute' => ''])
        ],
        'reason_change_state' =>  [
            'label'         =>  __('validation.attributes.reason_change_state'),
            'error'         => false,
            'msg'           =>  __('validation.required', ['attribute' => ''])
        ],
        'password_current' =>  [
            'label'         =>  __('validation.attributes.password_current'),
            'error'         => false,
            'msg'           =>  __('validation.required', ['attribute' => '']),
            'msg_min'       =>  __('validation.custom.password.min', [
                'attribute' => __('validation.attributes.password'),
                'min' => '8',
            ]),
            'msg_not_match' =>  __('validation.custom.password.not_match', [
                'attribute' => __('validation.attributes.password'),
            ])
        ],
        'password_new'  =>  [
            'label'             =>  __('validation.attributes.password_new'),
            'error'             => false,
            'msg'               =>  __('validation.required', ['attribute' => '']),
            'equal_current'     =>  __('validation.custom.password.equal_current', ['attribute' => __('validation.attributes.password')])
        ],
        'password_confirmation' =>  [
            'label'         =>  __('validation.attributes.password_confirmation'),
            'error'         => false,
            'msg'           =>  __('validation.required', ['attribute' => ''])
        ],
        'password' =>  [
            'label'         =>  __('validation.attributes.password'),
            'error'         => false,
            'msg'           =>  __('validation.required', ['attribute' => ''])
        ],
    ];

    //Texts from Laravel translations
    $texts = [
        "global" => $texts_messages,
        "main_section" => $text_main_section,
      //  "parameters" => __('app.parameters'),
      //  "roles" => __('app.roles'),
        "variables" => $text_variables,
    ];

    //List urls to use global in store from pinia/vue
    $list_urls = [
        'home' => route('home'),
        "logout" => route('logout'),
        /*'participant' => [
            "list_from_auth" => route('participants_list_from_auth'),
        ],*/
       /*"inventory" => [
            "get_category" => route('get_category'),
            "update_category" => route('update_category'),
            "save_category" => route('save_category'),
            "delete_category" => route('delete_category'),
            "get_group" => route('get_group'),
            "get_category_name" => route('get_category_name'),
            "update_group" => route('update_group'),
            "delete_group" => route('delete_group'),
            "save_group" => route('save_group'),
        ]
    */];

    //List fields to use in store pinia/vue
    $fields = [
        "participant" => $fields_participant,
    ];
?>
