<?php

return array(
    'Elfinder' => array(
        'title' => 'i18n::File manager elfinder module',
        'description' => 'i18n::File manager elfinder module description',
        'version' => '0.1',     
        
        'permission_resources' => array(
            array(
                'resource' => 'file_system_access',
                'privelege' => '',
                'name' => 'i18n::File system access',
            ),
        ),
        
        'methods' => array(
            'files' => array(
                'service' => 'Elfinder\Method\Files',
                'title' => 'i18n::Elfinder:Files method',
                'description' => 'i18n::Elfinder:Files method description',
                'menu_group' => 'media',
                'type' => 'be',
            ),
        ),
    ),
    'service_manager' => array(
        'invokables' => array(
            'Elfinder\Service\Connector' => 'Elfinder\Service\Connector',
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
            'elfinder' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/elfinder/connector',
                    'defaults' => array(
                        'controller' => 'Elfinder\Controller\Connector',
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),
    'menu_groups' => array(
        'media' => array(
            'title' => 'i18n::Media menu group',
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Elfinder\Controller\Connector' => 'Elfinder\Controller\ConnectorController',
        ),
    ),
);