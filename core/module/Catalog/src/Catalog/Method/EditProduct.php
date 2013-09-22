<?php

namespace Catalog\Method;

use App\Method\AbstractMethod;

class EditProduct extends AbstractMethod
{
    public function main()
    {        
        $prodEntity = $this->serviceLocator->get('Catalog\Entity\ProductEntity');
        $catService = $this->serviceLocator->get('Catalog\Service\Catalog');
        $request = $this->serviceLocator->get('request');
        $objectsCollection = $this->serviceLocator->get('objectsCollection');
        
        $result = array();
        
        if (null === $this->params()->fromRoute('id')) {
            $result['errMsg'] = 'Не переданы все необходимые параметры';            
            return $result;
        } 
        
        $objectId = (int)$this->params()->fromRoute('id');
  
        if (!$catService->isObjectProduct($objectId)) {
            $result['errMsg'] = 'Пункт меню ' . $objectId . ' не найден';
            return $result;
        }
        
        $object = $objectsCollection->getObject($objectId);
        
        $prodEntity->setObjectId($objectId);
                
        if (null === $this->params()->fromRoute('objectTypeId')) {            
            $objectTypeId = $object->getTypeId();
        } else {
            $objectTypeId = (int)$this->params()->fromRoute('objectTypeId');
            if (!in_array($objectTypeId, $catService->getProductTypeIds())) {
                $result['errMsg'] = 'Передан неверный тип объекта ' . $objectTypeId;
                return $result;
            }
        }
        $prodEntity->setObjectTypeId($objectTypeId);
            
        
        if ($request->isPost()) {
            $form = $prodEntity->getForm(false);
            $form->setData($request->getPost());
            
            if ($form->isValid()) {
                if ($prodEntity->editProduct($form->getData())) {
                    if (!$request->isXmlHttpRequest()) {
                        $this->flashMessenger()->addSuccessMessage('Товар успешно обновлен');
                        $this->redirect()->toRoute('admin/method',array(
                            'module' => 'Catalog',
                            'method' => 'EditProduct',
                            'id' => $objectId,
                        ));
                    }

                    return array(
                        'success' => true,
                        'msg' => 'Товар успешно обновлен',
                    );  
                } else {
                    return array(
                        'success' => false,
                        'msg' => 'При обновлении товара произошли ошибки',
                    );  
                }                       
            } else {
                $result['success'] = false;
            }
        } else {
            $form = $prodEntity->getForm(true);    
        }
        
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/Catalog/catalog_form.phtml',
            'data' => array(
                'jsArgs' => array(
                    'changeObjectTypeUrlTemplate' => $this->url()->fromRoute('admin/EditProduct', array(
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