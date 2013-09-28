<?php

namespace Catalog\Method;

use App\Method\AbstractMethod;

class AddProduct extends AbstractMethod
{
    public function main()
    {
        $catService = $this->serviceLocator->get('Catalog\Service\Catalog');
        $prodCollection = $this->serviceLocator->get('Catalog\Collection\ProductsCollection');
        $objectTypesCollection = $this->serviceLocator->get('objectTypesCollection');
        $request = $this->serviceLocator->get('request');
        $translator = $this->serviceLocator->get('translator');
        
        $result = array();
        
        $parentObjectId = (int)$this->params()->fromRoute('id', 0); 
        if ($parentObjectId) {
            if (!$catService->isObjectCategory($parentObjectId)) {
                $result['errMsg'] = 'Объект ' . $parentObjectId . ' не является категорией';
                return $result;
            }
        }     
        
        $prodCollection->setParentObjectId($parentObjectId);
        
        if (null !== $this->params()->fromRoute('objectTypeId')) {
            $objectTypeId = (int)$this->params()->fromRoute('objectTypeId');
            $prodCollection->setObjectTypeId($objectTypeId);
            
            if (!in_array($objectTypeId, $catService->getProductTypeIds())) {
                $result['errMsg'] = 'Тип данных ' . $objectTypeId . ' не является товаром';
                return $result;
            }
        }       
        
        if ($request->isPost()) {
            $form = $prodCollection->getForm(false);
            
            $data = $request->getPost()->toArray();
            if (empty($data['common']['name'])) {
                $data['common']['name'] = $translator->translate('Catalog:(Product without name)');
            }
            $form->setData($data);
            
            if ($form->isValid()) {
                if ($menuItemId = $prodCollection->addMenuItem($form->getData())) {
                    if (!$request->isXmlHttpRequest()) {
                        $this->flashMessenger()->addSuccessMessage('Товар добавлен');
                        $this->redirect()->toRoute('admin/method',array(
                            'module' => 'Catalog',
                            'method' => 'EditProduct',
                            'id' => $menuItemId,
                        ));
                    }
                    
                    return array(
                        'success' => true,
                        'msg' => 'Товар добавлен',
                    );    
                } else {
                    $result['success'] = false;
                    $result['errMsg'] = 'При добавлении товара произошли ошибки';
                }
            } else {
                $result['success'] = false;
            }
        } else {
            $form = $prodCollection->getForm(true);
        }
        
        $params = array(
            'objectTypeId' => '--OBJECT_TYPE--',    
        );
        if (0 != $parentObjectId) {
            $params['id'] = $parentObjectId;
        }
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/Catalog/catalog_form.phtml',
            'data' => array(
                'jsArgs' => array(
                    'changeObjectTypeUrlTemplate' => $this->url()->fromRoute('admin/AddProduct', $params),
                ),
                'form' => $form,
            ),
        );
        
        return $result;
    }
}