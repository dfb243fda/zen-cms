<?php

return array(
    'FrontEnd' => array(
        'title' => 'i18n::Front end module',
        'description' => 'i18n::Front end module description',
        'version' => '0.1',
        
        'priority' => -8,
        'isRequired' => true,
    ),
    'translator' => array(
        'translation_file_patterns' => array(
            array(
                'type'     => 'phparray',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.php',
            ),
        ),
    ),
    'router' => array(
        'routes' => array(
            'fe' => array(
                'type' => 'segment',
                'options' => array(
                    'route'    => '/',
                    'defaults' => array(
                        'controller' => 'FrontEnd\Controller\Fe',
                        'action'     => 'index',
                    ),
                ),
                'may_terminate' => true,                
                'child_routes' => array(
                    'page' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '[:pageAlias{/.}][/:itemId{/.}][/][.:format]',
                        ),
                    ),
                ),
            ),
        ),
    ),    
    'controllers' => array(
        'invokables' => array(
            'FrontEnd\Controller\Fe' => 'FrontEnd\Controller\FeController',      
        ),
    ),    
);
