<?php
return array(
    'HtmlJsCssOptimizer' => array(
        'title' => 'i18n::HtmlJsCssOptimizer module',
        'description' => 'i18n::HtmlJsCssOptimizer module description',
        
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
    'router' => array(
        'routes' => array(
            'munee' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/munee',
                    'defaults' => array(
                        'controller' => 'HtmlJsCssOptimizer\Controllers\Munee',
                        'action'     => 'munee',
                    ),
                ),
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'HtmlJsCssOptimizer\Controllers\Munee' => 'HtmlJsCssOptimizer\Controllers\MuneeController',
        ),
    ),
    'service_manager' => array(
        'invokables' => array(
            'HtmlJsCssOptimizer\Service\HeaderSetter' => 'HtmlJsCssOptimizer\Service\HeaderSetter',
            'HtmlJsCssOptimizer\Service\HtmlJsCssOptimizer' => 'HtmlJsCssOptimizer\Service\HtmlJsCssOptimizer',
        ),
    ),
    
    'dynamic_config' => array(
        'tabs' => array(
            'html_js_css' => array(
                'title' => 'i18n::HtmlJsCssOptimizer config tab',
            ),
        ),
        'form' => array(
            'html_js_css' => array(
                'fieldsets' => array(
                    'HtmlJsCssOptimizer' => array(
                        'spec' => array(
                            'name' => 'HtmlJsCssOptimizer',
                            'elements' => array(
                                'minifyHtml' => array(
                                    'spec' => array(
                                        'name' => 'minifyHtml',
                                        'type' => 'checkbox',
                                        'options' => array(
                                            'label' => 'i18n::HtmlJsCssOptimizer config minifyHtml',
                                            'description' => 'i18n::HtmlJsCssOptimizer config minifyHtml description',
                                        ),
                                    ),
                                ),
                                'minifyJs' => array(
                                    'spec' => array(
                                        'name' => 'minifyJs',
                                        'type' => 'checkbox',
                                        'options' => array(
                                            'label' => 'i18n::HtmlJsCssOptimizer config minifyJs',
                                            'description' => 'i18n::HtmlJsCssOptimizer config minifyJs description',
                                        ),
                                    ),
                                ),
                                'minifyCss' => array(
                                    'spec' => array(
                                        'name' => 'minifyCss',
                                        'type' => 'checkbox',
                                        'options' => array(
                                            'label' => 'i18n::HtmlJsCssOptimizer config minifyCss',
                                            'description' => 'i18n::HtmlJsCssOptimizer config minifyCss description',
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
