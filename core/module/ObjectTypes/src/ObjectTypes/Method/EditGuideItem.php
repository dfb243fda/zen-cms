<?php

namespace ObjectTypes\Method;

use App\Method\AbstractMethod;

class EditGuideItem extends AbstractMethod
{    
    public function main()
    {
        $guideItemsCollection = $this->serviceLocator->get('ObjectTypes\Collection\GuideItemsCollection');
        $request = $this->serviceLocator->get('request');
        
        $result = array();
        
        if (null === $this->params()->fromRoute('id')) {
            $result['errMsg'] = 'Не переданы все необходимые параметры';
            return $result;
        } 
        
        $guideItemId = (int)$this->params()->fromRoute('id');
        
        
        if (null === ($guideItem = $guideItemsCollection->getGuideItem($guideItemId))) {
            $result['errMsg'] = 'Термин ' . $guideItemId . ' не найден';
            return $result;
        }
        
        
        $form = $guideItem->getForm();        
        
        if ($request->isPost()) {
            $form->setData($this->params()->fromPost());
            if ($form->isValid()) {                
                $guideItem->editGuideItem($form->getData());
                
                if (!$request->isXmlHttpRequest()) {
                    $this->flashMessenger()->addSuccessMessage('Термин успешно обновлен');                    
                    return $this->redirect()->refresh();
                }

                return array(
                    'success' => true,
                    'msg' => 'Термин успешно обновлен',
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