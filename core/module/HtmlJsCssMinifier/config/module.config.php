<?php
return array(
    'HtmlJsCssMinifier' => array(
        'title' => 'i18n::HtmlJsCssMinifier module',
        'description' => 'i18n::HtmlJsCssMinifier module description',
        
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
    'service_manager' => array(
        'invokables' => array(
            'HtmlJsCssMinifier\Service\Installer' => 'HtmlJsCssMinifier\Service\Installer',
            
            'HtmlJsCssMinifier\Listener\PublicResourcesComposer' => 'HtmlJsCssMinifier\Listener\PublicResourcesComposer', 
            'HtmlJsCssMinifier\Listener\HtmlMinifier' => 'HtmlJsCssMinifier\Listener\HtmlMinifier', 
            
            
            'HtmlJsCssMinifier\Service\HeaderSetter' => 'HtmlJsCssMinifier\Service\HeaderSetter',
            'HtmlJsCssMinifier\Service\HtmlJsCssMinifier' => 'HtmlJsCssMinifier\Service\HtmlJsCssMinifier',
        ),
    ),
    
    'dynamic_config' => array(
        'tabs' => array(
            'html_js_css' => array(
                'title' => 'i18n::HtmlJsCssMinifier config tab',
            ),
        ),
        'form' => array(
            'html_js_css' => array(
                'fieldsets' => array(
                    array(
                        'spec' => array(
                            'name' => 'HtmlJsCssMinifier',
                            'elements' => array(
                                array(
                                    'spec' => array(
                                        'name' => 'minifyHtml',
                                        'type' => 'checkbox',
                                        'options' => array(
                                            'label' => 'i18n::HtmlJsCssMinifier config minifyHtml',
                                            'description' => 'i18n::HtmlJsCssMinifier config minifyHtml description',
                                        ),
                                    ),
                                ),
                                array(
                                    'spec' => array(
                                        'name' => 'minifyJs',
                                        'type' => 'checkbox',
                                        'options' => array(
                                            'label' => 'i18n::HtmlJsCssMinifier config minifyJs',
                                            'description' => 'i18n::HtmlJsCssMinifier config minifyJs description',
                                        ),
                                    ),
                                ),
                                array(
                                    'spec' => array(
                                        'name' => 'minifyCss',
                                        'type' => 'checkbox',
                                        'options' => array(
                                            'label' => 'i18n::HtmlJsCssMinifier config minifyCss',
                                            'description' => 'i18n::HtmlJsCssMinifier config minifyCss description',
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
);
