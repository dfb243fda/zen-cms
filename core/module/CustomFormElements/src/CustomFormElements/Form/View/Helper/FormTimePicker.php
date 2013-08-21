<?php

namespace CustomFormElements\Form\View\Helper;

use Zend\Form\View\Helper\FormText;
use Zend\Form\ElementInterface;

class FormTimePicker extends FormText
{
    public function render(ElementInterface $element)
    {
        $name = $element->getName();
        if ($name === null || $name === '') {
            throw new Exception\DomainException(sprintf(
                '%s requires that the element has an assigned name; none discovered',
                __METHOD__
            ));
        }
        
        $attributes          = $element->getAttributes();
        if (isset($attributes['id'])) {
            $id = $attributes['id'];
        } else {
            $id = str_replace(array('[', ']'), '_', $name);
            $attributes['id'] = $id;
        }
        $attributes['name']  = $name;
        $attributes['type']  = $this->getType($element);
        $attributes['value'] = $element->getValue();
        
        $jsAttributes = $element->getJsAttributes();
        
        $escape     = $this->getEscapeHtmlHelper();
        
        $this->getView()->headScript()->appendFile(ROOT_URL_SEGMENT . '/js/core/jquery_plugins/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.js');
        $this->getView()->headScript()->appendFile(ROOT_URL_SEGMENT . '/js/core/jquery_plugins/jquery-ui-timepicker-addon/language/ru_RU.js');
        $this->getView()->inlineScript()->appendScript('$(\'#'.$escape($id).'\').timepicker('.json_encode($jsAttributes).');');
        
        return sprintf(
            '<input %s%s',
            $this->createAttributesString($attributes),
            $this->getInlineClosingBracket()
        );
    }
}