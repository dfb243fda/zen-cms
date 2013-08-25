<?php

namespace Pages\Method;

use App\Method\AbstractMethod;

class EditDomain extends AbstractMethod
{
    public function main()
    {
        $domainEntity = $this->serviceLocator->get('Pages\Entity\Domain');
        $request = $this->serviceLocator->get('request');
        
        $result = array();
        
        if (null === $this->params()->fromRoute('id')) {
            $result['errMsg'] = 'Не переданы все необходимые параметры';
            return $result;
        } 
        
        $domainId = (int)$this->params()->fromRoute('id');
        
        $domainEntity->setDomainId($domainId);
        
        $form = $domainEntity->getForm();        
        
        if ($request->isPost()) {
            $form->setData($request->getPost());
            
            if ($form->isValid()) {
                if ($domainEntity->editDomain($form->getData())) {
                    if (!$request->isXmlHttpRequest()) {
                        $this->flashMessenger()->addSuccessMessage('Домен успешно обновлен');
                        $this->redirect()->refresh();
                    }
                    
                    return array(
                        'success' => true,
                        'msg' => 'Домен успешно обновлен',
                    );    
                } else {
                    $result['success'] = false;
                    $result['errMsg'] = 'При обновлении домена произошли ошибки';
                }
            } else {
                $result['success'] = false;
            }
        }        
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/Pages/domain_form_view.phtml',
            'data' => array(
                'form' => $form,           
            ),
        );
        
        if ($this->flashMessenger()->hasSuccessMessages()) {
            $result['msg'] = $this->flashMessenger()->getSuccessMessages();
        } 
        if ($this->flashMessenger()->hasErrorMessages()) {
            $result['errMsg'] = $this->flashMessenger()->getErrorMessages();
        }
        
        return $result;      
    }
}