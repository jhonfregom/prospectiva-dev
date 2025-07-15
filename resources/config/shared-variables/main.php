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

    //Texts matriz section
    $text_matriz = array_merge(
        __('app.main.modules.matrix'), [
        'item' => __('validation.attributes.item'),
        'elements' => __('validation.attributes.elements'),
    ]);

    //Texts schwartz section
    $text_schwartz = [
        'title' => 'Ejes de Peter Schwartz',
        'hypothesis' => [
            'h1_plus' => 'HIPÓTESIS 1+',
            'h1_minus' => 'HIPÓTESIS 1-',
            'h2_plus' => 'HIPÓTESIS 2+',
            'h2_minus' => 'HIPÓTESIS 2-',
        ],
        'scenarios' => [
            'scenario_1' => 'ESCENARIO 1',
            'scenario_2' => 'ESCENARIO 2',
            'scenario_3' => 'ESCENARIO 3',
            'scenario_4' => 'ESCENARIO 4',
        ],
        'actions' => [
            'edit' => 'Editar',
            'save' => 'Guardar'
        ],
        'messages' => [
            'save_error' => 'Error al guardar: ',
            'try_again' => 'Intenta de nuevo.',
            'edit_limit_reached' => 'Has alcanzado el límite de ediciones para este escenario.'
        ]
    ];

    //Texts variables section
    $text_variables_section = [
        'title' => 'Variables',
        'subtitle' => 'Gestiona las variables del proyecto',
        'table' => [
            'variable' => 'VARIABLE',
            'name' => 'NOMBRE',
            'description' => 'DESCRIPCIÓN',
            'score' => 'SCORE',
            'state' => 'ESTADO',
            'actions' => 'ACCIONES',
            'edit' => 'Editar',
            'save' => 'Guardar',
            'delete' => 'Eliminar',
            'new' => 'Nuevo'
        ],
        'modal' => [
            'title' => 'Nueva Variable',
            'name_label' => 'Nombre de la Variable',
            'name_placeholder' => 'Ingrese el nombre de la variable',
            'save' => 'Guardar',
            'cancel' => 'Cancelar'
        ],
        'messages' => [
            'create_success' => 'Variable creada exitosamente',
            'create_error' => 'Error al crear la variable',
            'update_error' => 'Error al actualizar la variable',
            'delete_success' => 'Variable eliminada correctamente',
            'delete_error' => 'Error al eliminar la variable',
            'delete_confirm_title' => 'Eliminar Variable',
            'delete_confirm_message' => '¿Está seguro de eliminar esta variable?',
            'delete_confirm_yes' => 'Eliminar',
            'delete_confirm_no' => 'Cancelar',
            'limit_reached' => 'Se ha alcanzado el límite máximo de 15 variables permitidas'
        ],
        'description_placeholder' => 'Escriba la descripción de la variable'
    ];

    //Texts hypothesis section
    $text_hypothesis = [
        'title' => 'Direccionadores de futuro',
        'subtitle' => 'En esta sección se generan las hipótesis para las dos variables más cercanas a la zona de poder.',
        'table' => [
            'h' => 'H',
            'variable' => 'VARIABLE',
            'descriptionH0' => 'HIPÓTESIS H0',
            'descriptionH1' => 'HIPÓTESIS H1',
            'edit' => 'Editar',
            'save' => 'Guardar',
            'locked' => 'BLOQUEADO',
            'actions' => 'ACCIONES'
        ],
        'note' => 'Las hipótesis se bloquean después de dos ediciones manuales. Cada textarea tiene un límite de 40 palabras. Ambos campos (H0 y H1) se editan juntos.'
    ];

    //Texts results section
    $text_results_section = [
        'title' => 'Resultados',
        'subtitle' => 'Listado de usuarios y sus datos',
        'filters' => [
            'id' => 'ID',
            'first_name' => 'Nombre',
            'last_name' => 'Apellido',
            'document_id' => 'Identificación',
            'search_placeholder' => 'Buscar...'
        ],
        'table' => [
            'id' => 'ID',
            'first_name' => 'Nombre',
            'last_name' => 'Apellido',
            'document_id' => 'Identificación',
            'email' => 'Email',
            'variables_count' => 'Total Variables',
            'variables_list' => 'Variables Creadas',
            'matriz' => 'Matriz',
            'zone_analyses' => 'Análisis Mapa de Variables',
            'future_drivers' => 'Direccionadores de Futuro',
            'initial_conditions' => 'Condiciones Iniciales',
            'scenarios' => 'Escenarios',
            'conclusions' => 'Conclusiones'
        ]
    ];

    // Textos para la sección estratégica (StrategicScenarioTable.vue)
    $text_strategic = [
        'main_title' => 'PROSPECTIVA ESTRATÉGICA PARA LA GENERACIÓN DE COMPETITIVIDAD EMPRESARIAL',
        'scenario_label' => 'escenario:',
        'plan_label' => 'PLAN PROSPECTIVO CONSTRUCCIÓN DE ESCENARIOS',
        'name' => 'NOMBRE',
        'hypothesis1' => 'Hipótesis 1+',
        'hypothesis2' => 'Hipótesis 2+',
        'year1' => 'AÑO 1',
        'year2' => 'AÑO 2',
        'year3' => 'AÑO 3',
        'save' => 'Guardar',
        'edit' => 'Editar',
        'edit_limit' => 'Este campo ya no se puede editar más.',
        'save_error' => 'Error al guardar. Intenta de nuevo.'
    ];

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
        "matriz" => $text_matriz,
        "schwartz" => $text_schwartz,
        "strategic" => $text_strategic,
        "variables_section" => $text_variables_section,
        "hypothesis" => $text_hypothesis,
        "results" => $text_results_section,
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
