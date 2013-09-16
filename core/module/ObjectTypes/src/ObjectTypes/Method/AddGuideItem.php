<?php

namespace ObjectTypes\Method;

use App\Method\AbstractMethod;

class AddGuideItem extends AbstractMethod
{
    public function main()
    {
        $objectTypesCollection = $this->serviceLocator->get('objectTypesCollection');
        $request = $this->serviceLocator->get('request');
        
        $result = array();
        
        if (null === $this->params()->fromRoute('id')) {
            $result['errMsg'] = 'Не переданы все необходимые параметры';
            return $result;
        } 
        
        $guideId = (int)$this->params()->fromRoute('id');
        
        if (!$objectType = $objectTypesCollection->getType($guideId)) {
            $result['errMsg'] = 'Не найден тип данных ' . $guideId;
            return $result;
        }
        
        if (!$objectType->getIsGuidable()) {
            $result['errMsg'] = 'Тип данных ' . $objectType->getName() . ' не является справочником';
            return $result;
        }
                
        $form = $objectType->getForm(false, true);    
        
        if ($request->isPost()) {
            $form->setData($this->params()->fromPost());
            if ($form->isValid()) {
                
                $guideItemsCollection = $this->serviceLocator->get('ObjectTypes\Collection\GuideItemsCollection');
                $guideItemsCollection->setGuideId($guideId);
                
                $guideItemId = $guideItemsCollection->addGuideItem($form->getData());
                
                if (!$request->isXmlHttpRequest()) {
                    $this->flashMessenger()->addSuccessMessage('Термин успешно добавлен');
                    
                    $this->redirect()->toRoute('admin/method', array(
                        'module' => 'ObjectTypes',
                        'method' => 'EditGuideItem',
                        'id'     => $guideItemId,
                    ));
                }

                return array(
                    'success' => true,
                    'msg' => 'Термин успешно добавлен',
                );         
            } else {
                $result['success'] = false;
            }
        }
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/ObjectTypes/guide_item_form_view.phtml',
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