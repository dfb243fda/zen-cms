<?php

namespace Pages\Method;

use App\Method\AbstractMethod;

class AddDomain extends AbstractMethod
{
    public function main()
    {
        $domainsCollection = $this->serviceLocator->get('Pages\Collection\Domains');
        $request = $this->serviceLocator->get('request');
        
        $result = array();
                       
        $form = $domainsCollection->getForm();   
        
        if ($request->isPost()) {
            $form->setData($request->getPost());
            
            if ($form->isValid()) {
                if ($domainId = $domainsCollection->addDomain($form->getData())) {
                    if (!$request->isXmlHttpRequest()) {
                        $this->flashMessenger()->addSuccessMessage('Домен успешно добавлен');
                        $this->redirect()->toRoute('admin/method', array(
                            'module' => 'Pages',
                            'method' => 'EditDomain',
                            'id'     => $domainId,
                        ));
                    }

                    return array(
                        'success' => true,
                        'msg' => 'Домен успешно добавлен',
                    );         
                } else {
                    return array(
                        'success' => false,
                        'msg' => 'При добавлении домена произошли ошибки',
                    ); 
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
        
        return $result;  
    }
}