<?php

namespace Templates\Form;

class TemplateWithMarkersForm extends TemplateForm
{    
    public function init()
    {
        parent::init();
        
        $translator = $this->serviceLocator->getServiceLocator()->get('translator');
        
        $this->add(array(
                'type' => 'textarea',
                'name' => 'markers',
                'options' => array(
                    'label' => $translator->translate('Templates:Template markers'),
                ),
            ));
    }    
    
}