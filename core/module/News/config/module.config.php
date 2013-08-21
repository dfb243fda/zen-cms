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
                'guid' => 'news-item',
                'with_descendants' => true,
            ),
        ),
        
        'methods' => array(
            'NewsList' => array(
                'service' => 'News\Method\NewsList',
                'title' => 'i18n::News:News list method',
                'description' => 'i18n::News:News list method description',
                'type' => 'be',
                'menu_group' => 'news',
            ),
            'Edit' => array(
                'service' => 'News\Method\Edit',
                'title' => 'i18n::News:News edit method',
                'description' => 'i18n::News:News edit method description',
                'type' => 'be',
                'breadcrumbPrevMethod' => 'NewsList',
            ),
            'AddNews' => array(
                'service' => 'News\Method\AddNews',
                'title' => 'i18n::News:Add news method',
                'description' => 'i18n::News:Add news method description',
                'type' => 'be',
                'breadcrumbPrevMethod' => 'NewsList',
            ),
            'AddNewsItem' => array(
                'service' => 'News\Method\AddNewsItem',
                'title' => 'i18n::News:Add news item method',
                'description' => 'i18n::News:Add news item method description',
                'type' => 'be',
                'breadcrumbPrevMethod' => 'NewsList',
            ),
            'Delete' => array(
                'service' => 'News\Method\Delete',
                'title' => 'i18n::News:News delete method',
                'description' => 'i18n::News:News delete method description',
                'type' => 'be',
            ),
            'FeNewsList' => array(
                'service' => 'News\Method\FeNewsList',
                'title' => 'i18n::News:FeNews list method',
                'description' => 'i18n::News:FeNews list method description',
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
    
    'search_url_query' => array(
        'news-item' => function($sm, $objectId) {
            $newsService = $sm->get('News\Service\News');
            
            return $newsService->getSingleNewsUrlQuery($objectId);
        },
    ),
            
    'service_manager' => array(
        'invokables' => array(
            'News\Service\News' => 'News\Service\News',
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
                    'AddNews' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => 'News/AddNews[/id_:id{/.}][/object_type_:objectTypeId{/.}][/][.:format]',
                            'defaults' => array(
                                'module' => 'News',
                                'method' => 'AddNews',
                            ),
                        ),
                    ),
                    'EditNews' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => 'News/EditNews[/id_:id{/.}][/object_type_:objectTypeId{/.}][/][.:format]',
                            'defaults' => array(
                                'module' => 'News',
                                'method' => 'Edit',
                            ),
                        ),
                    ),
                    'AddNewsItem' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => 'News/AddNewsItem[/id_:id{/.}][/object_type_:objectTypeId{/.}][/][.:format]',
                            'defaults' => array(
                                'module' => 'News',
                                'method' => 'AddNewsItem',
                            ),
                        ),
                    )
                ),
            ),            
        ),
    ),    
);