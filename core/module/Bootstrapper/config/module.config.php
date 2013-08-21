<?php

return array(
    'Bootstrapper' => array(
        'title' => 'i18n::Bootstrapper module',
        'description' => 'i18n::Bootstrapper module description',
        'version' => '0.1',
        
        'permission_resources' => array(
            array(
                'resource' => '',
                'privelege' => '',
                'name' => 'i18n::Full system access',
            ),
            array(
                'resource' => 'get_errors',
                'privelege' => '',
                'name' => 'i18n::Display errors access',
            ),
        ),
        
        'priority' => -11,
        'isRequired' => true,
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
    'dynamic_config' => array(
        'tabs' => array(
            'general' => array(
                'title' => 'i18n::General config tab',
            ),
            'date_time_formats' => array(
                'title' => 'i18n::Date time formats tab',
            )
        ),
        'form' => array(
            'general' => array(
                'fieldsets' => array(
                    'system' => array(
                        'spec' => array(
                            'name' => 'system',
                            'elements' => array(
                                'site_name' => array(
                                    'spec' => array(
                                        'name' => 'site_name',
                                        'options' => array(
                                            'label' => 'i18n::Dynamic config system site name',
                                            'description' => 'i18n::Dynamic config system site name description',
                                        ),
                                    ),
                                ),
                                'site_desc' => array(
                                    'spec' => array(
                                        'name' => 'site_desc',
                                        'options' => array(
                                            'label' => 'i18n::Dynamic config system site desc',
                                            'description' => 'i18n::Dynamic config system site desc description',
                                        ),
                                    ),
                                ),
                                'admin_email' => array(
                                    'spec' => array(
                                        'name' => 'admin_email',
                                        'options' => array(
                                            'label' => 'i18n::Dynamic config system admin email',
                                            'description' => 'i18n::Dynamic config system admin email description',
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
                'input_filter' => array(
                    'system' => array(
                        'type' => 'Zend\InputFilter\InputFilter',
                        'site_name' => array(
                            'required' => true,
                        ),
                        'admin_email' => array(
                            'required' => true,
                            'filters' => array(
                                array('name' => 'StringTrim'),  
                                array('name' => 'StringToLower'),
                            ),
                            'validators' => array(
                                array('name' => 'EmailAddress'),
                            ),
                        ),
                    ),
                ),
            ),
            'date_time_formats' => array(
                'fieldsets' => array(
                    'system' => array(
                        'spec' => array(
                            'name' => 'system',
                            'elements' => array(
                                'date_format' => array(
                                    'spec' => array(
                                        'name' => 'date_format',
                                        'options' => array(
                                            'label' => 'i18n::Dynamic config system date format',
                                            'description' => 'i18n::Dynamic config system date format description',
                                        ),
                                    ),
                                ),
                                'js_date_format' => array(
                                    'spec' => array(
                                        'name' => 'js_date_format',
                                        'options' => array(
                                            'label' => 'i18n::Dynamic config system js date format',
                                            'description' => 'i18n::Dynamic config system js date format description',
                                        ),
                                    ),
                                ),
                                'time_format' => array(
                                    'spec' => array(
                                        'name' => 'time_format',
                                        'options' => array(
                                            'label' => 'i18n::Dynamic config system time format',
                                            'description' => 'i18n::Dynamic config system time format description',
                                        ),
                                    ),
                                ),
                                'js_time_format' => array(
                                    'spec' => array(
                                        'name' => 'js_time_format',
                                        'options' => array(
                                            'label' => 'i18n::Dynamic config system js time format',
                                            'description' => 'i18n::Dynamic config system js time format description',
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
                'input_filter' => array(
                    'system' => array(
                        'type' => 'Zend\InputFilter\InputFilter',
                        'date_format' => array(
                            'required' => true,
                        ),
                        'js_date_format' => array(
                            'required' => true,
                        ),
                        'time_format' => array(
                            'required' => true,
                        ),
                        'js_time_format' => array(
                            'required' => true,
                        ),
                    ),
                ),
            ),
        ),
    ),
);