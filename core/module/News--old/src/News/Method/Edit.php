<?php

namespace News\Method;

use App\Method\AbstractMethod;
use News\Model\News;

class Edit extends AbstractMethod
{
    protected $rootServiceLocator;
    
    protected $translator;
    
    protected $db;
    
    protected $newsModel;
    
    public function init()
    {
        $this->rootServiceLocator = $this->serviceLocator->getServiceLocator();
        $this->newsModel = new News($this->rootServiceLocator);        
        $this->request = $this->rootServiceLocator->get('request');
    }

    public function main()
    {
        $result = array();
        
        if (null === $this->params()->fromRoute('id')) {
            $result['errMsg'] = 'Не переданы все необходимые параметры';
            return $result;
        } 
        
        $objectId = (int)$this->params()->fromRoute('id');
  
        if ($this->newsModel->isObjectRubric($objectId)) {
            $newsType = News::RUBRIC;
        } elseif ($this->newsModel->isObjectItem($objectId)) {
            $newsType = News::ITEM;
        } else {
            $result['errMsg'] = 'Объект ' . $objectId . ' не является ни рубрикой, ни новостью';
            return $result;
        }
        
        $this->newsModel->setNewsType($newsType)->setObjectId($objectId);
        
        if (null !== $this->params()->fromRoute('objectTypeId')) {
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
            $tmp = $this->newsModel->edit($this->request->getPost());
            if ($tmp['success']) {
                if (!$this->request->isXmlHttpRequest()) {
                    if ($newsType == News::RUBRIC) {
                        $this->flashMessenger()->addSuccessMessage('Рубрика успешно обновлена');
                    } else {
                        $this->flashMessenger()->addSuccessMessage('Новость успешно обновлена');
                    }
                    
                    $this->redirect()->toRoute('admin/method', array(
                        'module' => 'News',
                        'method' => 'Edit',
                        'id'     => $objectId,
                    ));
                }

                return array(
                    'success' => true,
                    'msg' => 'Меню успешно обновлено',
                );         
            } else {
                $result['success'] = false;
                $formMessages = $tmp['form']->getMessages();
                $formValues = $tmp['form']->getData();
            }
        }
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/News/form_view.phtml',
            'data' => array(
                'jsArgs' => array(
                    'changeObjectTypeUrlTemplate' => $this->url()->fromRoute('admin/EditNews', array(
                        'id' => $objectId,
                        'objectTypeId' => '--OBJECT_TYPE--',            
                    )),
                ),
                'formConfig' => $formConfig,
                'formValues' => $formValues,
                'formMsg'    => $formMessages,
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