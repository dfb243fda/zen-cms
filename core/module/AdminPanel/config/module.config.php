<?php

return array(
    'AdminPanel' => array(
        'title' => 'i18n::Admin panel module',
        'description' => 'i18n::Admin panel module description',
        'version' => '0.1',
        
        'permission_resources' => array(
            array(
                'resource' => 'admin_access',
                'privelege' => '',
                'name' => 'i18n::Admin panel access',
            ),
        ),
        
        'priority' => -7,
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
            'admin' => array(
                'type' => 'segment',
                'options' => array(
                    'route'    => '/admin[/]',
                    'defaults' => array(
                        'controller' => 'AdminPanel\Controller\Admin',
                        'action'     => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'method' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '[:module{/.}][/:method{/.}][/:id{/.}][/][.:format]',
                        ),
                    ),
                ),
            ),
        ),
    ),    
    'service_manager' => array(
        'invokables' => array(            
            'AdminPanel\Service\PageData' => 'AdminPanel\Service\PageData',
            'AdminPanel\Service\UserData' => 'AdminPanel\Service\UserData',
            'AdminPanel\Service\SystemInfo' => 'AdminPanel\Service\SystemInfo',
            'AdminPanel\Service\Errors' => 'AdminPanel\Service\Errors',
            'AdminPanel\View\RendererStrategyOptions' => 'AdminPanel\View\RendererStrategyOptions',
            
            'AdminPanel\View\ResultComposer\HtmlComposer' => 'AdminPanel\View\ResultComposer\HtmlComposer',
            'AdminPanel\View\ResultComposer\JsonHtmlComposer' => 'AdminPanel\View\ResultComposer\JsonHtmlComposer',
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'AdminPanel\Controller\Admin' => 'AdminPanel\Controller\AdminController',
        ),
    ),    
);
