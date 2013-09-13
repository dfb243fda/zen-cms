<?php

namespace App\Form\View\Helper;

use Zend\Form\View\Helper\FormCollection;
use Zend\Form\Element\Collection as CollectionElement;

class FormCollection3C extends FormCollection
{
    protected $defaultElementHelper = 'formElementWrapper3C';
        
    public function renderTemplate(CollectionElement $collection)
    {
        $html = parent::renderTemplate($collection);
        
        $addButtonText = $collection->getOption('addBtnText');        
        if (null === $addButtonText) {
            $addButtonText = $this->getTranslator()->translate('App:Add collection item');
        }        
        
        $delButtonText = $collection->getOption('delBtnText');        
        if (null === $delButtonText) {
            $delButtonText = $this->getTranslator()->translate('App:Delete collection item');
        }  
        
        if ($collection->getOption('allow_add')) {
            $html .= '<button class="add-collection-item" onclick="return zen.currentTheme.forms.addCollectionItem(this)">' . $addButtonText . '</button>';
            
            $html .= '<button class="del-collection-item" onclick="return zen.currentTheme.forms.delCollectionItem(this)">' . $delButtonText . '</button>';
        }        
        
        return $html;
    }
}
