<?php

namespace ContactForms\Method;

use App\Method\AbstractMethod;

class FormsList extends AbstractMethod
{
    public function main()
    {
        $formsList = $this->getServiceLocator()->get('ContactForms\Service\FormsList');
        
        if ('get_data' == $this->params()->fromRoute('task')) { 
            $result = $formsList->getData();
        } else {
            $result = array(
                'contentTemplate' => array(
                    'name' => 'content_template/ContactForms/forms_list.phtml',
                ),
            );
        }
        return $result;
    }   
}