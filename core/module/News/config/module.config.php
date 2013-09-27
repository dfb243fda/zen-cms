<?php

return array(
    'News' => array(
        'title' => 'i18n::News module',
        'description' => 'i18n::News module description',
        'version' => '0.1',
        
        'default_templates' => array(
            array(
                'title' => 'i18n::News:News list template',
                'name' => 'news_list.phtml',
                'type' => 'content_template',
                'method' => 'FeNewsList',
            ),
            array(
                'title' => 'i18n::News:News item template',
                'name' => 'news_item.phtml',
                'type' => 'content_template',
                'method' => 'FeNewsItem',
            ),
        ),
        
        'search_object_types' => array(
            array(
                'guid' => 'news',
                'with_descendants' => true,
            ),
        ),
        
        'methods' => array(
            'NewsList' => array(
                'service' => 'News\Method\NewsList',
                'title' => 'i18n::News:NewsList method',
                'description' => 'i18n::News:NewsList method description',
                'type' => 'be',
                'menu_group' => 'news',
            ),
            'EditRubric' => array(
                'service' => 'News\Method\EditRubric',
                'title' => 'i18n::News:EditRubric method',
                'description' => 'i18n::News:EditRubric method description',
                'type' => 'be',
                'breadcrumbPrevMethod' => 'NewsList',
            ),
            'EditNews' => array(
                'service' => 'News\Method\EditNews',
                'title' => 'i18n::News:EditNews method',
                'description' => 'i18n::News:EditNews method description',
                'type' => 'be',
                'breadcrumbPrevMethod' => 'NewsList',
            ),
            'AddRubric' => array(
                'service' => 'News\Method\AddRubric',
                'title' => 'i18n::News:AddRubric method',
                'description' => 'i18n::News:AddRubric method description',
                'type' => 'be',
                'breadcrumbPrevMethod' => 'NewsList',
            ),
            'AddNews' => array(
                'service' => 'News\Method\AddNews',
                'title' => 'i18n::News:AddNews method',
                'description' => 'i18n::News:AddNews method description',
                'type' => 'be',
                'breadcrumbPrevMethod' => 'NewsList',
            ),
            'DeleteRubric' => array(
                'service' => 'News\Method\DeleteRubric',
                'title' => 'i18n::News:DeleteRubric method',
                'description' => 'i18n::News:DeleteRubric method description',
                'type' => 'be',
            ),
            'DeleteNews' => array(
                'service' => 'News\Method\DeleteNews',
                'title' => 'i18n::News:DeleteNews method',
                'description' => 'i18n::News:DeleteNews method description',
                'type' => 'be',
            ),
            'FeNewsList' => array(
                'service' => 'News\Method\FeNewsList',
                'title' => 'i18n::News:FeNewsList method',
                'description' => 'i18n::News:FeNewsList method description',
                'type' => 'fe_content',
                'dynamic_templates' => true,
            ),
            'FeNewsItem' => array(
                'service' => 'News\Method\FeNewsItem',
                'title' => 'i18n::News:FeNewsItem method',
                'description' => 'i18n::News:FeNewsItem method description',
                'type' => 'fe_content',
                'dynamic_templates' => true,
            ),
        ),
    ),
    
    'service_manager' => array(
        'invokables' => array(
            'News\Service\Installer' => 'News\Service\Installer',
            'News\Service\NewsTree' => 'News\Service\NewsTree',
            'News\Service\News' => 'News\Service\News',
            'News\Collection\RubricsCollection' => 'News\Collection\RubricsCollection',
            'News\Collection\NewsCollection' => 'News\Collection\NewsCollection',
            
            'News\FormFactory\RubricFormFactory' => 'News\FormFactory\RubricFormFactory',
            'News\FormFactory\NewsFormFactory' => 'News\FormFactory\NewsFormFactory',
            
            'News\Entity\RubricEntity' => 'News\Entity\RubricEntity',
            'News\Entity\NewsEntity' => 'News\Entity\NewsEntity',
            
            'News\Service\NewsUrl' => 'News\Service\NewsUrl',
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
        'news' => array(
            'title' => 'i18n::News menu group',
        ),
    ),
    'router' => array(
        'routes' => array(
            'admin' => array(
                'child_routes' => array(
                    'NewsList' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => 'News/NewsList[/:task{/.}][/][.:format]',
                            'defaults' => array(
                                'module' => 'News',
                                'method' => 'NewsList',
                            ),
                        ),
                    ),
                    'EditRubric' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => 'News/EditRubric[/id_:id{/.}][/object_type_:objectTypeId{/.}][/][.:format]',
                            'defaults' => array(
                                'module' => 'News',
                                'method' => 'EditRubric',
                            ),
                        ),
                    ),
                    'EditNews' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => 'News/EditNews[/id_:id{/.}][/object_type_:objectTypeId{/.}][/][.:format]',
                            'defaults' => array(
                                'module' => 'News',
                                'method' => 'EditNews',
                            ),
                        ),
                    ),
                    'AddRubric' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => 'News/AddRubric[/id_:id{/.}][/object_type_:objectTypeId{/.}][/][.:format]',
                            'defaults' => array(
                                'module' => 'News',
                                'method' => 'AddRubric',
                            ),
                        ),
                    ),                   
                    'AddNews' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => 'News/AddNews[/id_:id{/.}][/object_type_:objectTypeId{/.}][/][.:format]',
                            'defaults' => array(
                                'module' => 'News',
                                'method' => 'AddNews',
                            ),
                        ),
                    )
                ),
            ),
        ),
    ),    
);