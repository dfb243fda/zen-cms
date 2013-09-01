<?php

return array(
    'DirectAccessToMethods' => array(
        'title' => 'i18n::Direct access to methods module',
        'description' => 'i18n::Direct access to methods module description',
        'version' => '0.1',
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
    'service_manager' => array(
        'invokables' => array(
            'DirectAccessToMethods\Service\DirectAccess' => 'DirectAccessToMethods\Service\DirectAccess',
            'DirectAccessToMethods\Service\SystemInfo' => 'DirectAccessToMethods\Service\SystemInfo',
            'DirectAccessToMethods\Service\Errors' => 'DirectAccessToMethods\Service\Errors',
            'DirectAccessToMethods\View\RendererStrategyOptions' => 'DirectAccessToMethods\View\RendererStrategyOptions',
        ),
    ),
    'view_helpers' => array(
        'invokables' => array(
            'DirectAccessToMethods\View\Helper\ExecuteMethod' => 'DirectAccessToMethods\View\Helper\ExecuteMethod',
        ),        
        'aliases' => array(
            'executeMethod' => 'DirectAccessToMethods\View\Helper\ExecuteMethod',
        ),
    ),
    'router' => array(
        'routes' => array(
            'direct' => array(
                'type' => 'segment',
                'options' => array(
                    'route'    => '/direct/:module/:method{/.}[/:param1{/.}][/:param2{/.}][/:param3{/.}][/:param4{/.}][/:param5{/.}][/][.:format]',
                    'defaults' => array(
                        'controller' => 'DirectAccessToMethods\Controller\Direct',
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),    
    'controllers' => array(
        'invokables' => array(
            'DirectAccessToMethods\Controller\Direct' => 'DirectAccessToMethods\Controller\DirectController',
        ),
    ),    
);
