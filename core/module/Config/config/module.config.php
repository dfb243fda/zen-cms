<?php

return array(
    'Config' => array(
        'title' => 'i18n::Config module',
        'description' => 'i18n::Config module description',
        'version' => '0.1',
        'isRequired' => true,
        
        'methods' => array(
            'DynamicConfig' => array(
                'service' => 'Config\Method\DynamicConfig',
                'title' => 'i18n::Dynamic config method',
                'description' => 'i18n::Dynamic config method description',
                'type' => 'be',
                'menu_group' => 'config',
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
    'service_manager' => array(
        'factories' => array(
            'configManager' => 'Config\Service\ConfigManagerFactory',
        ),
        'invokables' => array(
            'Config\Service\ConfigSettings' => 'Config\Service\ConfigSettings',
            'Config\FormFactory\Config' => 'Config\FormFactory\Config',
        ),
        'shared' => array(
            'Config\Service\ConfigSettings' => false,
            'Config\FormFactory\Config' => false,
        ),
    ),
    'menu_groups' => array(
        'config' => array(
            'title' => 'i18n::Config menu group',
        ),
    ),
);