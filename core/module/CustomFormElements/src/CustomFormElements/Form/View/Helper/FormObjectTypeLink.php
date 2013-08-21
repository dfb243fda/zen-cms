<?php

namespace CustomFormElements\Form\View\Helper;

use Zend\Form\View\Helper\FormSelect;
use Zend\Form\ElementInterface;

class FormObjectTypeLink extends FormSelect
{
    public function render(ElementInterface $element)
    {
        $result = parent::render($element);
        
        $value = $element->getValue();
                
        if ($value) {
            $editLink = $this->view->url('admin/method', array(
                'module' => 'ObjectTypes',
                'method' => 'EditObjectType',
                'id'     => $value,
            ));
            
            $result .= '<div class="object-type-edit-link"><a target="_blank" href="' . $editLink . '">' . $this->translator->translate('ObjectTypeLink:Edit object type') . '</a></div>';
        }
        
        
        
        return $result;
    }
}