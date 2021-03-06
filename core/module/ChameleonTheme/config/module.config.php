<?php

return array(
    'ChameleonTheme' => array(
        'type' => 'be_theme',
        'title' => 'i18n::Chameleon theme module',
        'description' => 'i18n::Chameleon theme module description',
        'version' => '0.1',
        'be_template' => 'page_template/ChameleonTheme/default.phtml',
        'be_main_page_method' => array('AdminPanel', 'AdminMainPage'),
        'isRequired' => true,
        'themeImage' => '/img/ChameleonTheme/theme_image.png',
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