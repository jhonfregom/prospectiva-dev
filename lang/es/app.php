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
        'subtitle'                  =>  'Enviaremos un enlace de recuperación',
        'title_main'                =>  'Recuperar ingreso</br>Cloud Back',
        'will_send_link'            =>  'Enviaremos un enlace de recuperación',
        'send_link'                 =>  'enviar enlace',
        'start'                     =>  'Iniciar',
        'error_email_paragraph1'    =>  'no existe correo asociado a esta cuenta, inténtelo de nuevo.',
        'error_email_paragraph2'    =>  'tiene un limite de tres opciones, de lo contrario debe intentarlo en 15 minutos.',
        'retry'                     =>  'intentar',
        'success_message'           =>  'se envió al correo :email, enlace para actualizar la contraseña.',
        'back_to_login'             =>  'Volver al login'
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
                'description' => 'Como resultado de la definición y descripción de las variables, aquí podrás construir la matriz de relaciones cruzadas entre las variables seleccionadas.Evaluarás qué tanto influyen unas sobre otras y cuán dependientes son.Este ejercicio te permitirá identificar cuáles variables son estructurantes, cuáles son resultado del sistema y cuáles tienen efectos dominó.La matriz es una herramienta poderosa para descubrir conexiones ocultas y priorizar lo estratégico.En esta sección identificarás podrás incluir las variables clave que influyen o son influenciadas por el sistema que estás analizando.Pueden ser factores políticos, económicos, sociales, tecnológicos, ambientales, legales o de otra índole que, por su impacto o sensibilidad, deben ser observados de cerca. Este paso es esencial: define el punto de partida del análisis prospectivo. Puedes editar cada campo hasta dos veces. De forma posterior, esta opción no podrá ser editada.<br><br>Al momento de valorar, ten en cuenta la siguiente convención:<br><br><strong>Influencia</strong><br>3 Influencia directa fuerte<br>2 Influencia directa media<br>1 Influencia débil o potencial<br>0 Influencia nula<br><br><strong>Dependencia</strong><br>3 Dependencia directa fuerte<br>2 Dependencia directa media<br>1 Dependencia débil o potencial<br>0 Dependencia nula<br><br>',
                'save' => 'Guardar Matriz',
                'no_variables_message' => 'No hay variables para mostrar.',
                'create_variables_message_part1' => 'Por favor,',
                'create_variables_link_text' => 'crea algunas variables',
                'create_variables_message_part2' => 'primero para poder calificarlas en la matriz.',
                'code' => 'CÓDIGO',
                'name' => 'NOMBRE',
                'total_dependency' => 'TOTAL DEPENDENCIA',
                'total_influence' => 'TOTAL INFLUENCIA',
                'interpretation_title' => 'Interpretación de valores:',
                'strong_influence' => 'Influencia directa fuerte',
                'medium_influence' => 'Influencia directa media',
                'weak_influence' => 'Influencia débil o potencial',
                'null_influence' => 'Influencia nula',
                'strong_dependency' => 'Dependencia directa fuerte',
                'medium_dependency' => 'Dependencia directa media',
                'weak_dependency' => 'Dependencia débil o potencial',
                'null_dependency' => 'Dependencia nula',
                'summary' => 'RESUMEN',
                'dependency' => 'DEPENDENCIA',
                'influence' => 'INFLUENCIA',
                'section_title' => 'CALIFICACIÓN DE VARIABLES',
                'save_success' => 'Matriz guardada correctamente.',
                'save_error' => 'Error al guardar la matriz.'
            ],
            'graphics' => [
                'title' =>  'graficas variables',
                'description' => 'En esta sección visualizarás el resultado de la matriz de relaciones mediante un gráfico de influencia (Y) vs dependencia (X). Este gráfico te ayudará a interpretar de forma clara y rápida el papel que juega cada variable dentro del sistema, clasificando las variables en:<br><br>Zona de poder – Cuadrante superior izquierdo<br>Zona de indiferencia – Cuadrante inferior izquierdo<br>Zona de conflicto – Cuadrante superior derecho<br>Zona de salida - Cuadrante inferior derecho<br>Con esta vista podrás distinguir qué factores son clave para la transformación del futuro y cuáles son más reactivos.<br><br>'
            ],
            'analysis' =>  [
                'title' =>  'análisis mapa de variables',
                'description' => [
                ]
            ],
            'hypothesis' => [
                'title' =>  'direccionadores de futuro',
                'description' => [
                ],
                'subtitle' => 'En esta sección se generan las hipótesis para las dos variables más cercanas a la zona de poder.',
                'table' => [
                    'h' => 'H',
                    'variable' => 'VARIABLE',
                    'descriptionH0' => 'HIPÓTESIS H0',
                    'descriptionH1' => 'HIPÓTESIS H1',
                    'edit' => 'Editar',
                    'save' => 'Guardar',
                    'locked' => 'BLOQUEADO'
                ],
                'note' => 'Las hipótesis se bloquean después de dos ediciones manuales. Cada textarea tiene un límite de 40 palabras. Ambos campos (H0 y H1) se editan juntos.'
            ],
            'schwartz' => [
                'title' =>  'ejes de peter schwartz',
                'description' => [
                ]
            ],
            'initialconditions' => [
                'title' =>  'condiciones iniciales',
                'description' => 'Esta sección te permite establecer las condiciones de contexto o supuestos que acompañan cada escenario, con respecto a las variables trabajadas. Aquí defines qué debe estar ocurriendo en el entorno para que ese futuro sea viable: políticas públicas, innovaciones tecnológicas, comportamientos sociales, entre otros. Las condiciones hacen que tus escenarios no sean solo imaginativos, sino coherentes y plausibles.<br>Puedes editar cada campo hasta dos veces. De forma posterior, esta opción no podrá ser editada.'
            ],
            'scenarios' => [
                'title' =>  'escenarios',
                'description' => [
                ]
            ],
            'conclusions' => [
                'title' =>  'conclusiones',
                'description' => 'En esta sección analizarás lo aprendido del proceso prospectivo. Reflexionarás sobre los hallazgos clave, las tendencias detectadas, las alertas tempranas y las estrategias que deberían considerarse. Las conclusiones permiten traducir los escenarios en acciones, aprendizajes y decisiones para el presente. Para cumplir con este apartado, responde las siguientes preguntas:… .<br><br>Puedes editar cada campo hasta dos veces. De forma posterior, esta opción no podrá ser editada.'
            ],
            'results' => [
                'title' =>  'resultados',
                'description' => [
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
                    'conclusions' => 'Conclusiones',
                    'status' => 'Estado'
                ]
            ],
        ],
        ],
        'queries' => [
            
    ],



    'variables' => [
        'title' =>  'variables',
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
        'title_introduction' => 'Introducción',
        'content_introduction' => 'La estrategia empresarial como clave para el desarrollo regional, se impone como una tarea que debe ser tenida en cuenta desde el análisis de la actualidad empresarial y contrastada con el análisis de la actualidad del entorno empresarial. Esta de manera definitiva deber dar cuenta de los requerimientos que deben implementar las organizaciones frente a sus futuros más deseables, en un entorno cada vez más incierto.\nLa generación de una visión a futuro generado con estrategias altamente efectivas aportará a las empresas y al sector al que pertenezca una gran ventaja competitiva, generando valor no solo para la organización sino para quienes estas logren impactar.<br>Por lo tanto y con el presente ejercicio prospectivo se pretende hacer una revisión detallada de cada variable a intervenir y como mediante una evaluación consciente de las mismas es posible aportar estratégicamente a esa visión de futuro que las empresas cada vez requieren con mayor urgencia.<br>A continuación, se relaciona la tendencia, el tópico o tema a ser analizado a lo largo de esta herramienta, ten en cuenta todas las indicaciones del manual para su fácil comprensión y ejecución.',
        
    ],
    'conclusions_section' => [
        'table' => [
            'close' => 'Cerrar',
        ],
        'messages' => [
            'close_success' => 'Conclusiones cerradas correctamente.',
            'close_error' => 'Error al cerrar las conclusiones.'
        ]
    ],
];
