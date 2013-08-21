<?php

return array(
    'FeTheme' => array(
        'title' => 'i18n::FeTheme module',
        'description' => 'i18n::FeTheme module description',
        'dynamic_templates' => true,
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
);