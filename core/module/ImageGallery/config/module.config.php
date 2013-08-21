<?php

return array(
    'ImageGallery' => array(
        'title' => 'i18n::ImageGallery module',
        'description' => 'i18n::ImageGallery module description',
        'version' => '0.1',
        
        'default_templates' => array(
            array(
                'title' => 'i18n::ImageGallery:Gallery template',
                'name' => 'image_gallery.phtml',
                'type' => 'content_template',
                'method' => 'FeGallery',
            ),
        ),
        
        'methods' => array(
            'GalleryList' => array(
                'service' => 'ImageGallery\Method\GalleryList',
                'title' => 'i18n::ImageGallery:Gallery list method',
                'description' => 'i18n::ImageGallery:Gallery list method description',
                'type' => 'be',
                'menu_group' => 'media',
            ),
            'Edit' => array(
                'service' => 'ImageGallery\Method\Edit',
                'title' => 'i18n::ImageGallery:Gallery edit method',
                'description' => 'i18n::ImageGallery:Gallery edit method description',
                'type' => 'be',
                'breadcrumbPrevMethod' => 'GalleryList',
            ),
            'AddGallery' => array(
                'service' => 'ImageGallery\Method\AddGallery',
                'title' => 'i18n::ImageGallery:Add gallery method',
                'description' => 'i18n::ImageGallery:Add gallery method description',
                'type' => 'be',
                'breadcrumbPrevMethod' => 'GalleryList',
            ),
            'AddImage' => array(
                'service' => 'ImageGallery\Method\AddImage',
                'title' => 'i18n::ImageGallery:Add image method',
                'description' => 'i18n::ImageGallery:Add image method description',
                'type' => 'be',
                'breadcrumbPrevMethod' => 'GalleryList',
            ),
            'Delete' => array(
                'service' => 'ImageGallery\Method\Delete',
                'title' => 'i18n::ImageGallery:Gallery delete method',
                'description' => 'i18n::ImageGallery:Gallery delete method description',
                'type' => 'be',
            ),
            'FeGallery' => array(
                'service' => 'ImageGallery\Method\FeGallery',
                'title' => 'i18n::ImageGallery:FeGallery method',
                'description' => 'i18n::ImageGallery:FeGallery method description',
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
    'menu_groups' => array(
        'media' => array(
            'title' => 'i18n::Media menu group',
        ),
    ),
    'router' => array(
        'routes' => array(
            'admin' => array(
                'child_routes' => array(
                    'GalleryList' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => 'ImageGallery/GalleryList[/:task{/.}][/][.:format]',
                            'defaults' => array(
                                'module' => 'ImageGallery',
                                'method' => 'GalleryList',
                            ),
                        ),
                    ),
                    'AddGallery' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => 'ImageGallery/AddGallery[/id_:id{/.}][/object_type_:objectTypeId{/.}][/][.:format]',
                            'defaults' => array(
                                'module' => 'ImageGallery',
                                'method' => 'AddGallery',
                            ),
                        ),
                    ),
                    'EditGallery' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => 'ImageGallery/EditGallery[/id_:id{/.}][/object_type_:objectTypeId{/.}][/][.:format]',
                            'defaults' => array(
                                'module' => 'ImageGallery',
                                'method' => 'Edit',
                            ),
                        ),
                    ),
                    'AddImage' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => 'ImageGallery/AddImage[/id_:id{/.}][/object_type_:objectTypeId{/.}][/][.:format]',
                            'defaults' => array(
                                'module' => 'ImageGallery',
                                'method' => 'AddImage',
                            ),
                        ),
                    )
                ),
            ),
        ),
    ),    
);