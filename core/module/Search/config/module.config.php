<?php

return array(
    'Search' => array(
        'title' => 'i18n::Search module',
        'description' => 'i18n::Search module description',
        'version' => '0.1',
        
        'priority' => 1,
        
        'default_templates' => array(
            array(
                'title' => 'i18n::Search:SearchForm template',
                'name' => 'search_form.phtml',
                'type' => 'content_template',
                'method' => 'SearchForm',
            ),
            array(
                'title' => 'i18n::Search:SearchResult template',
                'name' => 'search_result.phtml',
                'type' => 'content_template',
                'method' => 'SearchResult',
            ),
        ),
        
        'methods' => array(
            'Search' => array(
                'service' => 'Search\Method\Search',
                'title' => 'i18n::Search:Search method',
                'description' => 'i18n::Search:Search method description',
                'type' => 'be',
                'menu_group' => 'search',
            ),
            'SearchForm' => array(
                'service' => 'Search\Method\SearchForm',
                'title' => 'i18n::Search:SearchForm method',
                'description' => 'i18n::Search:SearchForm method description',
                'type' => 'fe_content',
                'dynamic_templates' => true,
            ),
            'SearchResult' => array(
                'service' => 'Search\Method\SearchResult',
                'title' => 'i18n::Search:SearchResult method',
                'description' => 'i18n::Search:SearchResult method description',
                'type' => 'fe_content',
                'dynamic_templates' => true,
            ),
        ),
    ),
    'service_manager' => array(
        'invokables' => array(
            'Search\Listener\ModuleSearchObjectTypesCollector' => 'Search\Listener\ModuleSearchObjectTypesCollector',
            'Search\Service\Installer' => 'Search\Service\Installer',
            'Search\Service\SearchIndexer' => 'Search\Service\SearchIndexer',
            'Search\Service\SearchEngine' => 'Search\Service\SearchEngine',
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
    'menu_groups' => array(
        'search' => array(
            'title' => 'i18n::Search menu group',
        ),
    ),
    'dynamic_config' => array(
        'tabs' => array(
            'search' => array(
                'title' => 'i18n::Search config tab',
            ),
        ),
        'form' => array(
            'search' => array(
                'fieldsets' => array(
                    array(
                        'spec' => array(
                            'name' => 'search',
                            'elements' => array(
                                array(
                                    'spec' => array(
                                        'name' => 'items_on_page',
                                        'options' => array(
                                            'label' => 'i18n::Search config items on page',
                                            'description' => 'i18n::Search config items on page description',
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
                'input_filter' => array(
                    'search' => array(
                        'type' => 'Zend\InputFilter\InputFilter',
                        'items_on_page' => array(
                            'required' => true,
                            'validators' => array(
                                array('name' => 'Regex', 'options' => array('pattern' => '/[0-9]+/')),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
);