<?php

namespace CustomFormElements\Form\View\Helper;

use CustomFormElements\Form\Element;
use Zend\Form\View\Helper\FormElement as BaseFormElement;
use Zend\Form\ElementInterface;

class FormElement extends BaseFormElement
{
    public function render(ElementInterface $element)
    {
        $renderer = $this->getView();
        if (!method_exists($renderer, 'plugin')) {
            // Bail early if renderer is not pluggable
            return '';
        }
        
        if ($element instanceof Element\AceEditor) {            
            $helper = $renderer->plugin('form_aceeditor');
            return $helper($element);
        }
        
        if ($element instanceof Element\CkEditor) {            
            $helper = $renderer->plugin('form_ckeditor');
            return $helper($element);
        }
        
        if ($element instanceof Element\TimePicker) {            
            $helper = $renderer->plugin('form_timepicker');
            return $helper($element);
        }
        
        if ($element instanceof Element\DateTimePicker) {            
            $helper = $renderer->plugin('form_datetimepicker');
            return $helper($element);
        }
        
        if ($element instanceof Element\DatePicker) {            
            $helper = $renderer->plugin('form_datepicker');
            return $helper($element);
        }
        
        if ($element instanceof Element\Image) {            
            $helper = $renderer->plugin('form_image');
            return $helper($element);
        }
        
        if ($element instanceof Element\ObjectTypeLink) {            
            $helper = $renderer->plugin('form_object_type_link');
            return $helper($element);
        }
        
        if ($element instanceof Element\TemplateLink) {            
            $helper = $renderer->plugin('form_template_link');
            return $helper($element);
        }
        
        if ($element instanceof Element\MultiText) {            
            $helper = $renderer->plugin('form_multi_text');
            return $helper($element);
        }
        
        if ($element instanceof Element\Composite) {            
            $helper = $renderer->plugin('form_composite');
            return $helper($element);
        }
        
        if ($element instanceof Element\ColorPicker) {            
            $helper = $renderer->plugin('form_colorpicker');
            return $helper($element);
        }

        return parent::render($element);
    }
}