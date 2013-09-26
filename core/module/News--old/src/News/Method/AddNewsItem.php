<?php

namespace News\Method;

use App\Method\AbstractMethod;
use News\Model\News;

class AddNewsItem extends AbstractMethod
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
                
        if (null === $this->params()->fromRoute('id')) {
            $result['errMsg'] = 'Не передана рубрика для добавления';
            return $result;
        }
        
        $parentObjectId = (int)$this->params()->fromRoute('id'); 
        if (!$this->newsModel->isObjectRubric($parentObjectId)) {
            $result['errMsg'] = 'Тип объекта ' . $parentObjectId . ' не является рубрикой новостей';
            return $result;
        }
        
        $this->newsModel->setNewsType(News::ITEM)->setParentObjectId($parentObjectId);
        
        if (null === $this->params()->fromRoute('objectTypeId')) {
            $objectType = $this->objectTypesCollection->getType($this->newsModel->getItemGuid());        
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
                    $this->flashMessenger()->addSuccessMessage('Новость успешно добавлена');
                    $this->redirect()->toRoute('admin/method',array(
                        'module' => 'News',
                        'method' => 'Edit',
                        'id' => $tmp['objectId'],
                    ));
                }

                return array(
                    'success' => 1,
                    'msg' => 'Новость успешно добавлена',
                );         
            } else {
                $formMessages = $tmp['form']->getMessages();
                $formValues = $tmp['form']->getData();
            }
        }
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/News/form_view.phtml',
            'data' => array(
                'jsArgs' => array(
                    'changeObjectTypeUrlTemplate' => $this->url()->fromRoute('admin/AddNewsItem', array(
                        'id' => $parentObjectId,
                        'objectTypeId' => '--OBJECT_TYPE--',            
                    )),
                ),
                'formConfig' => $formConfig,
                'formValues' => $formValues,
                'formMsg' => $formMessages,
            ),
        );
        
        return $result;
    }
}