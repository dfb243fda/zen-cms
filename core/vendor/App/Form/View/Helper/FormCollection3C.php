<?php

namespace App\Form\View\Helper;

use Zend\Form\View\Helper\FormCollection;
use Zend\Form\Element\Collection as CollectionElement;
use Zend\Form\ElementInterface;

class FormCollection3C extends FormCollection
{
    protected $defaultElementHelper = 'formElementWrapper3C';
        
    public function renderTemplate(CollectionElement $collection)
    {
        $html = parent::renderTemplate($collection);
        
        $buttonText = $collection->getOption('addBtnText');
        
        if (null === $buttonText) {
            $buttonText = $this->getTranslator()->translate('App:Add collection item');
        }        
        
        if ($collection->getOption('allow_add')) {
            $html .= '<button class="add-collection-item" onclick="return zen.currentTheme.forms.addCollectionItem(this)">' . $buttonText . '</button>';
        }        
        
        return $html;
    }
}
