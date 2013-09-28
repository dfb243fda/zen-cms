<?php

namespace News\Method;

use App\Method\AbstractMethod;

class EditNews extends AbstractMethod
{
    public function main()
    {        
        $newsEntity = $this->serviceLocator->get('News\Entity\NewsEntity');
        $newsService = $this->serviceLocator->get('News\Service\News');
        $request = $this->serviceLocator->get('request');
        
        $result = array();
        
        if (null === $this->params()->fromRoute('id')) {
            $result['errMsg'] = 'Не переданы все необходимые параметры';            
            return $result;
        } 
        
        $objectId = (int)$this->params()->fromRoute('id');
  
        if (!$newsService->isObjectNews($objectId)) {
            $result['errMsg'] = 'Новость ' . $objectId . ' не найдена';
            return $result;
        }
        
        $newsEntity->setObjectId($objectId);
                
        if (null !== $this->params()->fromRoute('objectTypeId')) {  
            $objectTypeId = (int)$this->params()->fromRoute('objectTypeId');
            if (!in_array($objectTypeId, $newsService->getNewsTypeIds())) {
                $result['errMsg'] = 'Передан неверный тип объекта ' . $objectTypeId;
                return $result;
            }
            $newsEntity->setObjectTypeId($objectTypeId);
        }
        
        if ($request->isPost()) {
            $form = $newsEntity->getForm(false);
            $form->setData($request->getPost());
            
            if ($form->isValid()) {
                if ($newsEntity->editNews($form->getData())) {
                    if (!$request->isXmlHttpRequest()) {
                        $this->flashMessenger()->addSuccessMessage('Новость успешно обновлена');
                        $this->redirect()->toRoute('admin/method',array(
                            'module' => 'News',
                            'method' => 'EditNews',
                            'id' => $objectId,
                        ));
                    }

                    return array(
                        'success' => true,
                        'msg' => 'Новость успешно обновлена',
                    );  
                } else {
                    return array(
                        'success' => false,
                        'msg' => 'При обновлении новости произошли ошибки',
                    );  
                }                       
            } else {
                $result['success'] = false;
            }
        } else {
            $form = $newsEntity->getForm(true);    
        }
        
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/News/news_form.phtml',
            'data' => array(
                'jsArgs' => array(
                    'changeObjectTypeUrlTemplate' => $this->url()->fromRoute('admin/EditNews', array(
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