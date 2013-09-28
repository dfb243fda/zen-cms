<?php

namespace Catalog\Method;

use App\Method\AbstractMethod;

class AddCategory extends AbstractMethod
{
    public function main()
    {
        $catalogService = $this->serviceLocator->get('Catalog\Service\Catalog');
        $categoriesCollection = $this->serviceLocator->get('Catalog\Collection\CategoriesCollection');
        $objectTypesCollection = $this->serviceLocator->get('objectTypesCollection');
        $request = $this->serviceLocator->get('request');
        $translator = $this->serviceLocator->get('translator');
        
        $result = array();
        
        if (null !== $this->params()->fromRoute('objectTypeId')) {
            $objectTypeId = (int)$this->params()->fromRoute('objectTypeId');
            $categoriesCollection->setObjectTypeId($objectTypeId);
            
            if (!in_array($objectTypeId, $catalogService->getCategoryTypeIds())) {
                $result['errMsg'] = 'Тип данных ' . $objectTypeId . ' не является категорией товаров';
                return $result;
            }
        }       
        
        if ($request->isPost()) {
            $form = $categoriesCollection->getForm(false);
            
            $data = $request->getPost()->toArray();
            if (empty($data['common']['name'])) {
                $data['common']['name'] = $translator->translate('Catalog:(Category without name)');
            }
            $form->setData($data);
            
            if ($form->isValid()) {                
                if ($catId = $categoriesCollection->addCategory($form->getData())) {
                    if (!$request->isXmlHttpRequest()) {
                        $this->flashMessenger()->addSuccessMessage('Категория создана');
                        $this->redirect()->toRoute('admin/method',array(
                            'module' => 'Catalog',
                            'method' => 'EditCategory',
                            'id' => $catId,
                        ));
                    }
                    
                    return array(
                        'success' => true,
                        'msg' => 'Категория создана',
                    );    
                } else {
                    $result['success'] = false;
                    $result['errMsg'] = 'При создании категории произошли ошибки';
                }
            } else {
                $result['success'] = false;
            }
        } else {
            $form = $categoriesCollection->getForm(true);
        }
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/Catalog/catalog_form.phtml',
            'data' => array(
                'jsArgs' => array(
                    'changeObjectTypeUrlTemplate' => $this->url()->fromRoute('admin/AddCategory', array(
                        'objectTypeId' => '--OBJECT_TYPE--',    
                    )),
                ),
                'form' => $form,
            ),
        );
        
        return $result;
    }
    
}