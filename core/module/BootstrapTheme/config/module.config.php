<?php

return array(
    'BootstrapTheme' => array(
        'title' => 'i18n::BootstrapTheme module',
        'description' => 'i18n::BootstrapTheme module description',
        'version' => '0.1',
        
        'be_template' => 'page_template/BootstrapTheme/default.phtml',
        'be_main_page_method' => array('AdminPanel', 'AdminMainPage'),
        
        'type' => 'be_theme',
        'themeImage' => '/img/BootstrapTheme/theme_image.png',
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