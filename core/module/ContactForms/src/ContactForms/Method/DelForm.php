<?php

namespace ContactForms\Method;

use App\Method\AbstractMethod;

class DelForm extends AbstractMethod
{    
    public function main()
    {
        $formsCollection = $this->serviceLocator->get('ContactForms\Collection\ContactForms');
        
        $result = array(
            'success' => false,
        );
        
        $formId = $this->params()->fromPost('id');
        if (null === $formId) {
            $result['errMsg'] = 'Не переданы все необходимые параметры';
            return $result;
        }
        $formId = (int)$formId;
        
        if ($formsCollection->delContactForm($formId)) {
            $result['success'] = true;
            $result['msg'] = 'Форма успешно удалена';
        } else {
            $result['success'] = false;
            $result['msg'] = 'Не удалось удалить форму';
        }
        
        return $result;        
    }
}