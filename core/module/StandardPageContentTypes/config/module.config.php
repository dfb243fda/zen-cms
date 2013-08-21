<?php

return array(
    'StandardPageContentTypes' => array(
        'title' => 'i18n::Standard page content types module',
        'description' => 'i18n::Standard page content types module description',
        'version' => '0.1',

        'default_templates' => array(
            array(
                'title' => 'i18n::Simple text template',
                'name' => 'simple_text.phtml',
                'type' => 'content_template',
                'method' => 'SimpleText',
            ),
        ),
        
        'methods' => array(
            'SimpleText' => array(
                'service' => 'StandardPageContentTypes\Method\SimpleText',
                'title' => 'i18n::Simple text method',
                'description' => 'i18n::Simple text method description',
                'type' => 'fe_content',
                'dynamic_templates' => true,
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
);