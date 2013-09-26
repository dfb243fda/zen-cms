<?php

namespace News\Method;

use App\Method\AbstractMethod;
use News\Model\News;

class AddNews extends AbstractMethod
{
    public function init()
    {
        $this->rootServiceLocator = $this->serviceLocator->getServiceLocator();
        $this->translator = $this->rootServiceLocator->get('translator');
        $this->db = $this->rootServiceLocator->get('db');
        $this->objectTypesCollection = $this->rootServiceLocator->get('objectTypesCollection');
        $this->objectsCollection = $this->rootServiceLocator->get('objectsCollection');
        $this->newsModel = new News($this->rootServiceLocator);     
        $this->request = $this->rootServiceLocator->get('request');
    }

    public function main()
    {
        $result = array();
        
        $parentObjectId = (int)$this->params()->fromRoute('id', 0); 
        if ($parentObjectId) {
            if (!$this->newsModel->isObjectRubric($parentObjectId)) {
                $result['errMsg'] = 'Объект ' . $parentObjectId . ' не является рубрикой новостей';
                return $result;
            }
        }        
        
        $this->newsModel->setNewsType(News::RUBRIC)->setParentObjectId($parentObjectId);
        
        if (null === $this->params()->fromRoute('objectTypeId')) {
            $objectType = $this->objectTypesCollection->getType($this->newsModel->getRubricGuid());        
            $objectTypeId = $objectType->getId();
            $this->newsModel->setObjectTypeId($objectTypeId);
        } else {
            $objectTypeId = (int)$this->params()->fromRoute('objectTypeId');
            $this->newsModel->setObjectTypeId($objectTypeId);
            
            if (!$this->newsModel->isObjectTypeCorrect($objectTypeId)) {
                $result['errMsg'] = 'Передан неверный тип объекта ' . $objectTypeId;
                return $result;
            }
        }       
        
        $form = $this->newsModel->getForm();        
        $formConfig = $form['formConfig'];
        $formValues = $form['formValues'];
        $formMessages = array();
        
        if ($this->request->isPost()) {
            $tmp = $this->newsModel->add($this->request->getPost());
            if ($tmp['success']) {
                if (!$this->request->isXmlHttpRequest()) {
                    $this->flashMessenger()->addSuccessMessage('Рубрика успешно добавлена');
                    $this->redirect()->toRoute('admin/method',array(
                        'module' => 'News',
                        'method' => 'Edit',
                        'id' => $tmp['objectId'],
                    ));
                }

                return array(
                    'success' => true,
                    'msg' => 'Рубрика успешно добавлена',
                );         
            } else {
                $result['success'] = false;
                $formMessages = $tmp['form']->getMessages();
                $formValues = $tmp['form']->getData();
            }
        }
        
        $changeObjectTypeUrlParams = array(
            'objectTypeId' => '--OBJECT_TYPE--',            
        );
        if (0 != $parentObjectId) {
            $changeObjectTypeUrlParams['id'] = $parentObjectId;
        }
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/News/form_view.phtml',
            'data' => array(
                'jsArgs' => array(
                    'changeObjectTypeUrlTemplate' => $this->url()->fromRoute('admin/AddNews', $changeObjectTypeUrlParams),
                ),
                'formConfig' => $formConfig,
                'formValues' => $formValues,
                'formMsg' => $formMessages,
            ),
        );
        
        return $result;        
    }
}