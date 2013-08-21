<?php

namespace App\Form\View\Helper;

use Zend\Form\View\Helper\FormRow;

use Zend\Form\ElementInterface;

class FormElementWrapper extends FormRow
{
    protected $inputErrorClass = '';
    
    public function render(ElementInterface $element)
    {
        $markup = parent::render($element);
        
        $addClass = array();
        
        $className = get_class($element);  
        
        $addClass[] = 'form-element';
        $addClass[] = 'form-element__' . strtolower(substr($className, strrpos($className, '\\')+1));
        if (count($element->getMessages()) > 0) {
            $addClass[] = 'form-element__has_errors';
        }
        
        $markup = '<div class="' . implode(' ', $addClass) . '">' . $markup . '</div>';
        
        return $markup;
    }
    
    protected function getElementErrorsHelper()
    {
        $helper = parent::getElementErrorsHelper();
        $helper->setAttributes(array(
            'class' => 'form-element__errors',
        ));
        
        
        return $helper;
    }
}