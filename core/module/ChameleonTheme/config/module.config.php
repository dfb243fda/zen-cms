<?php

return array(
    'ChameleonTheme' => array(
        'type' => 'be_theme',
        'title' => 'i18n::Chameleon theme module',
        'description' => 'i18n::Chameleon theme module description',
        'version' => '0.1',
        'defaultTemplate' => 'page_template/ChameleonTheme/default.phtml',
        'isRequired' => true,
        
        'methods' => array(
            'AdminMainPage' => array(
                'service' => 'ChameleonTheme\Method\AdminMainPage',
                'title' => 'i18n::Admin main page method',
                'description' => 'i18n::Admin main page method description',
                'type' => 'be',
                'showTitle' => false,
            ),
            'AdminMainMenu' => array(
                'service' => 'ChameleonTheme\Method\AdminMainMenu',
                'title' => 'i18n::Admin main menu method',
                'description' => 'i18n::Admin main menu method description',
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
    'method_manager' => array(
        'invokables' => array(
            'ChameleonTheme\Method\AdminMainMenu' => 'ChameleonTheme\Method\AdminMainMenu',
        ),
    ),
);