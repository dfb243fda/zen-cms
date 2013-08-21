<?php

namespace CustomFormElements\Form\View\Helper;

use Zend\Form\View\Helper\FormSelect;
use Zend\Form\ElementInterface;

class FormTemplateLink extends FormSelect
{
    public function render(ElementInterface $element)
    {
        $result = parent::render($element);
        
        $value = $element->getValue();
                
        $options = $element->getOptions();
        
        if ($value) {
            $editLink = $this->view->url('admin/method', array(
                'module' => 'Templates',
                'method' => 'EditTemplate',
                'id'     => $value,
            ));
            
            $result .= '<div class="template-edit-link"><a target="_blank" href="' . $editLink . '">' . $this->translator->translate('TemplateLink:Edit template') . '</a></div>';
        } elseif (isset($options['module']) && $options['module']) {
            $addLinkAttr = array(
                'module' => 'Templates',
                'method' => 'AddTemplate',
                'templateModule' => $options['module'],
            );
            
            if (isset($options['method']) && $options['method']) {
                $addLinkAttr['templateMethod'] = $options['method'];
            }
            
            $addLink = $this->view->url('admin/AddTemplate', $addLinkAttr);
            
            $result .= '<div class="template-edit-link"><a target="_blank" href="' . $addLink . '">' . $this->translator->translate('TemplateLink:Add template') . '</a></div>';
        }
        
        
        
        return $result;
    }
}