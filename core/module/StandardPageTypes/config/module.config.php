<?php

return array(
    'StandardPageTypes' => array(
        'title' => 'i18n::Standard page types module',
        'description' => 'i18n::Standard page types module description',
        'version' => '0.1',

        'methods' => array(
            'StandardPage' => array(
                'service' => 'StandardPageTypes\Method\StandardPage',
                'title' => 'i18n::Standard page method',
                'description' => 'i18n::Standard page method description',
                'type' => 'fe_page',
            ),
            'PageLink' => array(
                'service' => 'StandardPageTypes\Method\PageLink',
                'title' => 'i18n::PageLink method',
                'description' => 'i18n::PageLink method description',
                'type' => 'fe_page',
            ),
        ),
    ),
    
    'service_manager' => array(
        'invokables' => array(
            'StandardPageTypes\Service\Installer' => 'StandardPageTypes\Service\Installer',
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