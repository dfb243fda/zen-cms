<?php

return array(
    'ImageGallery' => array(
        'title' => 'i18n::ImageGallery module',
        'description' => 'i18n::ImageGallery module description',
        'version' => '0.1',
        
        'default_templates' => array(
            array(
                'title' => 'i18n::ImageGallery:Lightbox2 template',
                'name' => 'lightbox2.phtml',
                'type' => 'content_template',
                'method' => 'FeImageGallery',
            ),
        ),
        
        'methods' => array(
            'GalleryList' => array(
                'service' => 'ImageGallery\Method\GalleryList',
                'title' => 'i18n::ImageGallery:GalleryList method',
                'description' => 'i18n::ImageGallery:GalleryList method description',
                'type' => 'be',
                'menu_group' => 'media',
            ),
            'EditGallery' => array(
                'service' => 'ImageGallery\Method\EditGallery',
                'title' => 'i18n::ImageGallery:EditGallery method',
                'description' => 'i18n::ImageGallery:EditGallery method description',
                'type' => 'be',
                'breadcrumbPrevMethod' => 'GalleryList',
            ),
            'EditImage' => array(
                'service' => 'ImageGallery\Method\EditImage',
                'title' => 'i18n::ImageGallery:EditImage method',
                'description' => 'i18n::ImageGallery:EditImage method description',
                'type' => 'be',
                'breadcrumbPrevMethod' => 'GalleryList',
            ),
            'AddGallery' => array(
                'service' => 'ImageGallery\Method\AddGallery',
                'title' => 'i18n::ImageGallery:AddGallery method',
                'description' => 'i18n::ImageGallery:AddGallery method description',
                'type' => 'be',
                'breadcrumbPrevMethod' => 'GalleryList',
            ),
            'AddImage' => array(
                'service' => 'ImageGallery\Method\AddImage',
                'title' => 'i18n::ImageGallery:AddImage method',
                'description' => 'i18n::ImageGallery:AddImage method description',
                'type' => 'be',
                'breadcrumbPrevMethod' => 'GalleryList',
            ),
            'DeleteGallery' => array(
                'service' => 'ImageGallery\Method\DeleteGallery',
                'title' => 'i18n::ImageGallery:DeleteGallery method',
                'description' => 'i18n::ImageGallery:DeleteGallery method description',
                'type' => 'be',
            ),
            'DeleteImage' => array(
                'service' => 'ImageGallery\Method\DeleteImage',
                'title' => 'i18n::ImageGallery:DeleteImage method',
                'description' => 'i18n::ImageGallery:DeleteImage method description',
                'type' => 'be',
            ),
            'FeImageGallery' => array(
                'service' => 'ImageGallery\Method\FeImageGallery',
                'title' => 'i18n::ImageGallery:FeImageGallery method',
                'description' => 'i18n::ImageGallery:FeImageGallery method description',
                'type' => 'fe_content',
                'dynamic_templates' => true,
            ),
        ),
    ),
    
    'service_manager' => array(
        'invokables' => array(
            'ImageGallery\Service\Installer' => 'Catalog\Service\Installer',
            'ImageGallery\Service\GalleryTree' => 'ImageGallery\Service\GalleryTree',
            'ImageGallery\Service\ImageGallery' => 'ImageGallery\Service\ImageGallery',
            'ImageGallery\Collection\GalleriesCollection' => 'ImageGallery\Collection\GalleriesCollection',
            'ImageGallery\Collection\ImagesCollection' => 'ImageGallery\Collection\ImagesCollection',
            
            'ImageGallery\FormFactory\GalleryFormFactory' => 'ImageGallery\FormFactory\GalleryFormFactory',
            'ImageGallery\FormFactory\ImageFormFactory' => 'ImageGallery\FormFactory\ImageFormFactory',
            
            'ImageGallery\Entity\GalleryEntity' => 'ImageGallery\Entity\GalleryEntity',
            'ImageGallery\Entity\ImageEntity' => 'ImageGallery\Entity\ImageEntity',
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
                    'EditGallery' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => 'ImageGallery/EditGallery[/id_:id{/.}][/object_type_:objectTypeId{/.}][/][.:format]',
                            'defaults' => array(
                                'module' => 'ImageGallery',
                                'method' => 'EditGallery',
                            ),
                        ),
                    ),
                    'EditImage' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => 'ImageGallery/EditImage[/id_:id{/.}][/object_type_:objectTypeId{/.}][/][.:format]',
                            'defaults' => array(
                                'module' => 'ImageGallery',
                                'method' => 'EditImage',
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