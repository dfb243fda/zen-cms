<?php

namespace News\Method;

use App\Method\AbstractMethod;

class EditRubric extends AbstractMethod
{
    public function main()
    {        
        $rubricEntity = $this->serviceLocator->get('News\Entity\RubricEntity');
        $newsService = $this->serviceLocator->get('News\Service\News');
        $request = $this->serviceLocator->get('request');
        $translator = $this->serviceLocator->get('translator');
        
        $result = array();
        
        if (null === $this->params()->fromRoute('id')) {
            $result['errMsg'] = 'Не переданы все необходимые параметры';
            return $result;
        } 
        
        $objectId = (int)$this->params()->fromRoute('id');
  
        if (!$newsService->isObjectRubric($objectId)) {
            $result['errMsg'] = 'Рубрика ' . $objectId . ' не найдена';
            return $result;
        } 
                        
        $rubricEntity->setObjectId($objectId);
                
        if (null !== $this->params()->fromRoute('objectTypeId')) {    
            $objectTypeId = (int)$this->params()->fromRoute('objectTypeId');
            if (!in_array($objectTypeId, $newsService->getRubricTypeIds())) {
                $result['errMsg'] = 'Рубрика ' . $objectTypeId . ' не найдена';
                return $result;
            }
            $rubricEntity->setObjectTypeId($objectTypeId);
        }        
            
        
        if ($request->isPost()) {
            $form = $rubricEntity->getForm(false);
            
            $data = $request->getPost()->toArray();
            if (empty($data['common']['name'])) {
                $data['common']['name'] = $translator->translate('News:(Rubric without name)');
            }
            $form->setData($data);
            
            if ($form->isValid()) {
                if ($rubricEntity->editRubric($form->getData())) {
                    if (!$request->isXmlHttpRequest()) {
                        $this->flashMessenger()->addSuccessMessage('Рубрика успешно обновлена');
                        $this->redirect()->toRoute('admin/method',array(
                            'module' => 'News',
                            'method' => 'EditRubric',
                            'id' => $objectId,
                        ));
                    }

                    return array(
                        'success' => true,
                        'msg' => 'Рубрика успешно обновлена',
                    );  
                } else {
                    return array(
                        'success' => false,
                        'msg' => 'При обновлении рубрики произошли ошибки',
                    );  
                }                       
            } else {
                $result['success'] = false;
            }
        } else {
            $form = $rubricEntity->getForm(true);    
        }
        
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/News/news_form.phtml',
            'data' => array(
                'jsArgs' => array(
                    'changeObjectTypeUrlTemplate' => $this->url()->fromRoute('admin/EditRubric', array(
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