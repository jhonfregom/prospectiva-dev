
<?php
return [
    /*
    |--------------------------------------------------------------------------
    | App Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default messages used on app page
    |
    */

    'restore_password' => [
        'title'                     =>  'Recuperar contraseña',
        'title_main'                =>  'Recuperar ingreso</br>Cloud Back',
        'will_send_link'            =>  'Enviaremos un enlace de recuperación',
        'send_link'                 =>  'enviar enlace',
        'start'                     =>  'Iniciar',
        'error_email_paragraph1'    =>  'no existe correo asociado a esta cuenta, inténtelo de nuevo.',
        'error_email_paragraph2'    =>  'tiene un limite de tres opciones, de lo contrario debe intentarlo en 15 minutos.',
        'retry'                     =>  'intentar',
        'success_message'           =>  'se envió al correo :email, enlace para actualizar la contraseña.'
    ],

      'main' => [
        'modules_title' => 'módulos',
        'queries_title' => 'consultas',
        'modules' => [
            'variables' =>  [
                'title' =>  'variables',
                'description' => [
                    'creación de variables',
                ]
            ],
            'matrix' => [
                'title' =>  'matriz variables',
                'description' => [
                    'calificación variables',
                ]
            ],
            'graphics' => [
                'title' =>  'graficas variables',
                'description' => [
                    'matriz de variables',
                ]
            ],

        ],
        'queries' => [
            'analysis' =>  [
                'title' =>  'análisis mapa de variables',
                'description' => [
                ]
            ],
            'hypothesis' => [
                'title' =>  'análisis hipótesis de futuro',
                'description' => [
                ]
            ],
            'schuwartz' => [
                'title' =>  'schuwartz',
                'description' => [
                ]
            ],
        ],
    ],



    'inventory' => [
        'title' =>  'inventarios',
        'category' =>  'categoría',
        'confirm_save' => 'Confirma que desea guardar los cambios asignados?',        
        'apply' => 'Aplicar',
        'no' => 'No',
        'register' => 'registros',
        'success_delete' => 'Categoria eliminada correctamente',
        'error_delete' => 'Categoría con movimientos no puede eliminarse',
        'confirm_delete' => 'Confirma que desea eliminar la categoría?',
        'duplicate_cod_catg' => 'El código de Categoría ya existe!',
        'duplicate_message_cod_catg' => 'El código de categoría ya existe. Por favor registrar nuevo Código..!',
        'success_cod_catg' => 'Categoría creada correctamente!',
        'success_update_category' => 'Categoría actualizada correctamente!',   
        'error_update_category' => 'Error al actualizar la categoría, por favor intente de nuevo.',
    ]
];
