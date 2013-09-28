<?php

namespace Catalog\Method;

use App\Method\AbstractMethod;

class EditCategory extends AbstractMethod
{
    public function main()
    {        
        $catEntity = $this->serviceLocator->get('Catalog\Entity\CategoryEntity');
        $catService = $this->serviceLocator->get('Catalog\Service\Catalog');
        $request = $this->serviceLocator->get('request');
        
        $result = array();
        
        if (null === $this->params()->fromRoute('id')) {
            $result['errMsg'] = 'Не переданы все необходимые параметры';
            return $result;
        } 
        
        $objectId = (int)$this->params()->fromRoute('id');
  
        if (!$catService->isObjectCategory($objectId)) {
            $result['errMsg'] = 'Категория ' . $objectId . ' не найдена';
            return $result;
        } 
                        
        $catEntity->setObjectId($objectId);
                
        if (null !== $this->params()->fromRoute('objectTypeId')) {    
            $objectTypeId = (int)$this->params()->fromRoute('objectTypeId');
            if (!in_array($objectTypeId, $catService->getCategoryTypeIds())) {
                $result['errMsg'] = 'Категория ' . $objectTypeId . ' не найдена';
                return $result;
            }
            $catEntity->setObjectTypeId($objectTypeId);
        }        
            
        
        if ($request->isPost()) {
            $form = $catEntity->getForm(false);
            
            $data = $request->getPost()->toArray();
            if (empty($data['common']['name'])) {
                $data['common']['name'] = $translator->translate('Catalog:(Category without name)');
            }            
            $form->setData($data);
            
            if ($form->isValid()) {
                if ($catEntity->editCategory($form->getData())) {
                    if (!$request->isXmlHttpRequest()) {
                        $this->flashMessenger()->addSuccessMessage('Категория успешно обновлена');
                        $this->redirect()->toRoute('admin/method',array(
                            'module' => 'Catalog',
                            'method' => 'EditCategory',
                            'id' => $objectId,
                        ));
                    }

                    return array(
                        'success' => true,
                        'msg' => 'Категория успешно обновлена',
                    );  
                } else {
                    return array(
                        'success' => false,
                        'msg' => 'При обновлении категории произошли ошибки',
                    );  
                }                       
            } else {
                $result['success'] = false;
            }
        } else {
            $form = $catEntity->getForm(true);    
        }
        
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/Catalog/catalog_form.phtml',
            'data' => array(
                'jsArgs' => array(
                    'changeObjectTypeUrlTemplate' => $this->url()->fromRoute('admin/EditCategory', array(
                        'id' => $objectId,
                        'objectTypeId' => '--OBJECT_TYPE--',            
                    )),
                ),
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