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
        
        'methods' => array(
            'AdminMainPage' => array(
                'service' => 'AdminPanel\Method\AdminMainPage',
                'title' => 'i18n::AdminPanel:AdminMainPage method',
                'description' => 'i18n::AdminPanel:AdminMainPage method description',
                'type' => 'be',
                'showTitle' => false,
            ),
            'AdminMainMenu' => array(
                'service' => 'AdminPanel\Method\AdminMainMenu',
                'title' => 'i18n::AdminPanel:AdminMainMenu method',
                'description' => 'i18n::AdminPanel:AdminMainMenu method description',
            ),
        ),
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
            'AdminPanel\View\RendererStrategyOptions' => 'AdminPanel\View\RendererStrategyOptions',
            
            'AdminPanel\View\ResultComposer\HtmlComposer' => 'AdminPanel\View\ResultComposer\HtmlComposer',
            'AdminPanel\View\ResultComposer\JsonHtmlComposer' => 'AdminPanel\View\ResultComposer\JsonHtmlComposer',
            
            'AdminPanel\Service\AdminMainPage' => 'AdminPanel\Service\AdminMainPage',
            'AdminPanel\Service\AdminMainMenu' => 'AdminPanel\Service\AdminMainMenu',
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'AdminPanel\Controller\Admin' => 'AdminPanel\Controller\AdminController',
        ),
    ),   
);
