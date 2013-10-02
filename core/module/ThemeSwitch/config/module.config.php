<?php

return array(
    'ThemeSwitch' => array(
        'title' => 'i18n::ThemeSwitch module',
        'description' => 'i18n::ThemeSwitch module description',
        'version' => '0.1',
                
        'methods' => array(
            'ThemeSwitch' => array(
                'service' => 'ThemeSwitch\Method\ThemeSwitch',
                'title' => 'i18n::ThemeSwitch:ThemeSwitch method',
                'description' => 'i18n::ThemeSwitch:ThemeSwitch method description',
                'type' => 'be',
                'menu_group' => 'config',
            ),
            
        ),
    ),
    
    'service_manager' => array(
        'invokables' => array(
            'ThemeSwitch\Service\ThemeSwitch' => 'ThemeSwitch\Service\ThemeSwitch',
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
);