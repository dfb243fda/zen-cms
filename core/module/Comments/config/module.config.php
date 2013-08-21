<?php

return array(
    'Comments' => array(
        'title' => 'i18n::Comments module',
        'description' => 'i18n::Comments module description',
        'version' => '0.1',
        
        'defaultTemplate' => 'content_template/Comments/CommentsList/comments_list.phtml',
        
        'permission_resources' => array(
            array(
                'resource' => 'add_comments',
                'privelege' => '',
                'name' => 'i18n::Comments:add_comments',
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
    
    'service_manager' => array(
        'invokables' => array(
            'Comments\Service\Comments' => 'Comments\Service\Comments',
        ),        
    ),
    
    'view_manager' => array(
        'invokables' => array(
            'commentsList' => 'Comments\View\Helper\CommentsList',
        ),
    ),
    
    'dynamic_config' => array(
        'tabs' => array(
            'comments' => array(
                'title' => 'i18n::Comments config tab',
            ),
        ),
        'form' => array(
            'comments' => array(
                'fieldsets' => array(
                    'comments' => array(
                        'spec' => array(
                            'name' => 'comments',
                            'elements' => array(
                                'activate_standard_comments' => array(
                                    'spec' => array(
                                        'name' => 'activate_standard_comments',
                                        'type' => 'checkbox',
                                        'options' => array(
                                            'label' => 'i18n::Comments:activate_standard_comments',
                                            'description' => 'i18n::Comments:activate_standard_comments description',
                                        ),
                                    ),
                                ),
                                'items_on_page' => array(
                                    'spec' => array(
                                        'name' => 'items_on_page',
                                        'options' => array(
                                            'label' => 'i18n::Comments:items_on_page',
                                            'description' => 'i18n::Comments:items_on_page description',
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