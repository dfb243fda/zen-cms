<?php

return array(
    'CustomFormElements' => array(
        'title' => 'i18n::Custom form elements module',
        'description' => 'i18n::Custom form elements module description',
        'version' => '0.1',        
        
        'priority' => -3,
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
            'CustomFormElements\ObjectProperty\CkEditor'       => 'CustomFormElements\ObjectProperty\CkEditor',
            'CustomFormElements\ObjectProperty\PageLink'       => 'CustomFormElements\ObjectProperty\PageLink',
            'CustomFormElements\ObjectProperty\TimePicker'     => 'CustomFormElements\ObjectProperty\TimePicker',
            'CustomFormElements\ObjectProperty\DateTimePicker' => 'CustomFormElements\ObjectProperty\DateTimePicker',
            'CustomFormElements\ObjectProperty\DatePicker'     => 'CustomFormElements\ObjectProperty\DatePicker',
            'CustomFormElements\ObjectProperty\Image'          => 'CustomFormElements\ObjectProperty\Image',
            'CustomFormElements\ObjectProperty\MultiText'      => 'CustomFormElements\ObjectProperty\MultiText',
            'CustomFormElements\ObjectProperty\Composite'      => 'CustomFormElements\ObjectProperty\Composite',
            'CustomFormElements\ObjectProperty\ColorPicker'    => 'CustomFormElements\ObjectProperty\ColorPicker',
            
            'CustomFormElements\Service\Installer'             => 'CustomFormElements\Service\Installer',
        ),
        'aliases' => array(
            'ObjectProperty\CkEditor'       => 'CustomFormElements\ObjectProperty\CkEditor',
            'ObjectProperty\PageLink'       => 'CustomFormElements\ObjectProperty\PageLink',
            'ObjectProperty\TimePicker'     => 'CustomFormElements\ObjectProperty\TimePicker',
            'ObjectProperty\DateTimePicker' => 'CustomFormElements\ObjectProperty\DateTimePicker',
            'ObjectProperty\DatePicker'     => 'CustomFormElements\ObjectProperty\DatePicker',
            'ObjectProperty\Image'          => 'CustomFormElements\ObjectProperty\Image',
            'ObjectProperty\MultiText'      => 'CustomFormElements\ObjectProperty\MultiText',
            'ObjectProperty\Composite'      => 'CustomFormElements\ObjectProperty\Composite',
            'ObjectProperty\ColorPicker'    => 'CustomFormElements\ObjectProperty\ColorPicker',
        ),
    ),
    'form_elements' => array(
        'invokables' => array(
            'aceEditor'      => 'CustomFormElements\Form\Element\AceEditor',
            'ckeditor'       => 'CustomFormElements\Form\Element\CkEditor',
            'pageLink'       => 'CustomFormElements\Form\Element\PageLink',            
            'timePicker'     => 'CustomFormElements\Form\Element\TimePicker',
            'dateTimePicker' => 'CustomFormElements\Form\Element\DateTimePicker',
            'datePicker'     => 'CustomFormElements\Form\Element\DatePicker',
            'image'          => 'CustomFormElements\Form\Element\Image',
            'objectTypeLink' => 'CustomFormElements\Form\Element\ObjectTypeLink',
            'templateLink'   => 'CustomFormElements\Form\Element\TemplateLink',
            'multiText'      => 'CustomFormElements\Form\Element\MultiText',
            'composite'      => 'CustomFormElements\Form\Element\Composite',
            'colorPicker'    => 'CustomFormElements\Form\Element\ColorPicker',
        ),
    ),
    'view_helpers' => array(
        'invokables' => array(
            'formElement'           => 'CustomFormElements\Form\View\Helper\FormElement',
            'form_aceeditor'        => 'CustomFormElements\Form\View\Helper\FormAceEditor',
            'form_ckeditor'         => 'CustomFormElements\Form\View\Helper\FormCkEditor',
            'form_timepicker'       => 'CustomFormElements\Form\View\Helper\FormTimePicker',
            'form_datetimepicker'   => 'CustomFormElements\Form\View\Helper\FormDateTimePicker',
            'form_datepicker'       => 'CustomFormElements\Form\View\Helper\FormDatePicker',
            'form_image'            => 'CustomFormElements\Form\View\Helper\FormImage',
            'form_object_type_link' => 'CustomFormElements\Form\View\Helper\FormObjectTypeLink',
            'form_template_link' => 'CustomFormElements\Form\View\Helper\FormTemplateLink',
            'form_multi_text'       => 'CustomFormElements\Form\View\Helper\FormMultiText',
            'form_composite'        => 'CustomFormElements\Form\View\Helper\FormComposite',
            'form_colorpicker'      => 'CustomFormElements\Form\View\Helper\FormColorPicker',
        ),
    ),
);