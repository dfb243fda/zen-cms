<?php

namespace App\Form\View\Helper;

use Zend\Form\View\Helper\FormRow;
use Zend\Form\ElementInterface;

class FormElementWrapper3C extends FormRow
{    
    public function __invoke(ElementInterface $element = null, $labelPosition = null, $renderErrors = null, $partial = null)
    {
        if (!$element) {
            return $this;
        }

        if ($renderErrors !== null) {
            $this->setRenderErrors($renderErrors);
        }

        if ($partial !== null) {
            $this->setPartial($partial);
        }

        return $this->render($element);
    }
    
    public function render(ElementInterface $element)
    {        
        $escapeHtmlHelper    = $this->getEscapeHtmlHelper();
        $labelHelper         = $this->getLabelHelper();
        $elementHelper       = $this->getElementHelper();
        $elementErrorsHelper = $this->getElementErrorsHelper();

        $label           = $element->getLabel();

        if (isset($label) && '' !== $label) {
            // Translate the label
            if (null !== ($translator = $this->getTranslator())) {
                $label = $translator->translate(
                    $label, $this->getTranslatorTextDomain()
                );
            }
        }        

        if ($this->partial) {
            $vars = array(
                'element'           => $element,
                'label'             => $label,
                'labelAttributes'   => $this->labelAttributes,
                'labelPosition'     => $this->labelPosition,
                'renderErrors'      => $this->renderErrors,
            );

            return $this->view->render($this->partial, $vars);
        }

        if ($this->renderErrors) {
            $elementErrors = $elementErrorsHelper->render($element);
        }
        
        
        
        
        $elementString = $elementHelper->render($element);

        if (!isset($label)) {
            $label = '';
        }
        
        $label = $escapeHtmlHelper($label);
        $labelAttributes = $element->getLabelAttributes();

        if (empty($labelAttributes)) {
            $labelAttributes = $this->labelAttributes;
        }

        
        if ('' != $label) {
            $label = $this->view->translateI18n($label);
            $element->setLabel($label);
            $label = $labelHelper($element);
        }
        
        $description = $element->getOption('description');
        if (null === $description) {
            $description = '';
        } else {
            $description = $this->view->translateI18n($description);
        }
                
        $markup = '';
        
        $markup .= '<div class="form-3c-element__col-1">' . $label . '</div>';
        $markup .= '<div class="form-3c-element__col-2">' . $elementString . $elementErrors . '</div>';
        $markup .= '<div class="form-3c-element__col-3">' . $description . '</div>';
          
        
        
        $addClass = array();
        
        $className = get_class($element);  
        
        $addClass[] = 'form-3c-element';
        $addClass[] = 'form-3c-element__' . strtolower(substr($className, strrpos($className, '\\')+1));
        if (count($element->getMessages()) > 0) {
            $addClass[] = 'form-3c-element__has_errors';
        }
        $addClass[] = 'clearfix';
        
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