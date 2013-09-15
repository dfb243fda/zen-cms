<?php

return array(
    'ObjectTypes' => array(
        'title' => 'i18n::Object types module',
        'description' => 'i18n::Object types module description',
        'version' => '0.1',
        'priority' => -3,
        'isRequired' => true,
        
        'methods' => array(            
            'ObjectTypesList' => array(
                'service' => 'ObjectTypes\Method\ObjectTypesList',
                'title' => 'i18n::Object types list method',
                'description' => 'i18n::Object types list method description',
                'type' => 'be',
                'menu_group' => 'objectTypes',
            ),
            'GuidesList' => array(
                'service' => 'ObjectTypes\Method\GuidesList',
                'title' => 'i18n::Guides list method',
                'description' => 'i18n::Guides list method description',
                'type' => 'be',
                'breadcrumbPrevMethod' => 'ObjectTypesList',
            ),
            'AddObjectType' => array(
                'service' => 'ObjectTypes\Method\AddObjectType',
                'title' => 'i18n::Add object type method',
                'description' => 'i18n::Add object type method description',
                'type' => 'be',   
            ),
            'EditObjectType' => array(
                'service' => 'ObjectTypes\Method\EditObjectType',
                'title' => 'i18n::Edit object type method',
                'description' => 'i18n::Edit object type method description',
                'type' => 'be',
                'breadcrumbPrevMethod' => 'ObjectTypesList',
            ),
            'DelObjectType' => array(
                'service' => 'ObjectTypes\Method\DelObjectType',
                'title' => 'i18n::Delete object type method',
                'description' => 'i18n::Delete object type method description',
                'type' => 'be',
            ),
            'AddField' => array(
                'service' => 'ObjectTypes\Method\AddField',
                'title' => 'i18n::Add field method',
                'description' => 'i18n::Add field method description',
                'type' => 'be',
            ),
            'EditField' => array(
                'service' => 'ObjectTypes\Method\EditField',
                'title' => 'i18n::Edit field method',
                'description' => 'i18n::Edit field method description',
                'type' => 'be',
            ),
            'DelField' => array(
                'service' => 'ObjectTypes\Method\DelField',
                'title' => 'i18n::Delete field method',
                'description' => 'i18n::Delete field method description',
                'type' => 'be',
            ),
            'SortField' => array(
                'service' => 'ObjectTypes\Method\SortField',
                'title' => 'i18n::Sort field method',
                'description' => 'i18n::Sort field method description',
                'type' => 'be',
            ),
            'AddGroup' => array(
                'service' => 'ObjectTypes\Method\AddGroup',
                'title' => 'i18n::Add group method',
                'description' => 'i18n::Add group method description',
                'type' => 'be',
            ),
            'EditGroup' => array(
                'service' => 'ObjectTypes\Method\EditGroup',
                'title' => 'i18n::Edit group method',
                'description' => 'i18n::Edit group method description',
                'type' => 'be',
            ),
            'DelGroup' => array(
                'service' => 'ObjectTypes\Method\DelGroup',
                'title' => 'i18n::Delete group method',
                'description' => 'i18n::Delete group method description',
                'type' => 'be',
            ),
            'SortGroup' => array(
                'service' => 'ObjectTypes\Method\SortGroup',
                'title' => 'i18n::Sort group method',
                'description' => 'i18n::Sort group method description',
                'type' => 'be',
            ),
            
            'GuideItemsList' => array(
                'service' => 'ObjectTypes\Method\GuideItemsList',
                'title' => 'i18n::Guide items list method',
                'description' => 'i18n::Guide items list method description',
                'type' => 'be',
                'breadcrumbPrevMethod' => 'GuidesList',
            ),
            'EditGuideItem' => array(
                'service' => 'ObjectTypes\Method\EditGuideItem',
                'title' => 'i18n::Edit guide item method',
                'description' => 'i18n::Edit guide item method description',
                'type' => 'be',
                'breadcrumbPrevMethod' => 'GuideItemsList',
            ),
            'AddGuideItem' => array(
                'service' => 'ObjectTypes\Method\AddGuideItem',
                'title' => 'i18n::Add guide item method',
                'description' => 'i18n::Add guide item method description',
                'type' => 'be',
                'breadcrumbPrevMethod' => 'GuideItemsList',
            ),
            'DelGuideItem' => array(
                'service' => 'ObjectTypes\Method\DelGuideItem',
                'title' => 'i18n::Delete guide item method',
                'description' => 'i18n::Delete guide item method description',
                'type' => 'be',
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
        'objectTypes' => array(
            'title' => 'i18n::Object types menu group',
        ),
    ),    
    'service_manager' => array(
        'invokables' => array(
            'ObjectProperty\Text'          => 'ObjectTypes\ObjectProperty\Text',
            'ObjectProperty\Checkbox'      => 'ObjectTypes\ObjectProperty\Checkbox',
            'ObjectProperty\Password'      => 'ObjectTypes\ObjectProperty\Password',
            'ObjectProperty\Select'        => 'ObjectTypes\ObjectProperty\Select',
            'ObjectProperty\Textarea'      => 'ObjectTypes\ObjectProperty\Textarea',
            'ObjectProperty\MultiCheckbox' => 'ObjectTypes\ObjectProperty\MultiCheckbox',
            'ObjectProperty\Url'           => 'ObjectTypes\ObjectProperty\Url',
            'ObjectProperty\Number'        => 'ObjectTypes\ObjectProperty\Number',        
            
            'ObjectTypes\Service\Installer' => 'ObjectTypes\Service\Installer',
            'ObjectTypes\Service\ObjectTypesTree' => 'ObjectTypes\Service\ObjectTypesTree',
            'ObjectTypes\Service\GuidesList' => 'ObjectTypes\Service\GuidesList',
            'ObjectTypes\Service\GuideItemsList' => 'ObjectTypes\Service\GuideItemsList',
            
            'ObjectTypes\Entity\ObjectTypeAdmin' => 'ObjectTypes\Entity\ObjectTypeAdmin',
            'ObjectTypes\Collection\FieldsAdminCollection' => 'ObjectTypes\Collection\FieldsAdminCollection',
        ),
        'shared' => array(
            'ObjectTypes\Entity\ObjectTypeAdmin' => false,
            'ObjectTypes\Collection\FieldsAdminCollection' => false,
        ),
    ),
    'router' => array(
        'routes' => array(
            'admin' => array(
                'child_routes' => array(
                    'ObjectTypesList' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => 'ObjectTypes/ObjectTypesList[/:task{/.}][/][.:format]',
                            'defaults' => array(
                                'module' => 'ObjectTypes',
                                'method' => 'ObjectTypesList',
                            ),
                        ),
                    ),
                    'GuidesList' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => 'ObjectTypes/GuidesList[/:task{/.}][/][.:format]',
                            'defaults' => array(
                                'module' => 'ObjectTypes',
                                'method' => 'GuidesList',
                            ),
                        ),
                    ),
                    'AddObjectTypeField' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => 'ObjectTypes/AddField/:groupId/:objectTypeId[/][.:format]',
                            'defaults' => array(
                                'module' => 'ObjectTypes',
                                'method' => 'AddField',
                            ),
                        ),
                    ),
                    'EditFieldsGroup' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => 'ObjectTypes/EditGroup/:groupId/:objectTypeId[/][.:format]',
                            'defaults' => array(
                                'module' => 'ObjectTypes',
                                'method' => 'EditGroup',
                            ),
                        ),
                    ),
                    'EditObjectTypeField' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => 'ObjectTypes/EditField/:groupId/:fieldId[/][.:format]',
                            'defaults' => array(
                                'module' => 'ObjectTypes',
                                'method' => 'EditField',
                            ),
                        ),
                    ),
                    'GuideItemsList' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => 'ObjectTypes/GuideItemsList/:id[/:task{/.}][/][.:format]',
                            'defaults' => array(
                                'module' => 'ObjectTypes',
                                'method' => 'GuideItemsList',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),    
);
