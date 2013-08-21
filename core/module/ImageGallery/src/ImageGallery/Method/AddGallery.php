<?php

namespace ImageGallery\Method;

use App\Method\AbstractMethod;
use ImageGallery\Model\ImageGallery;

class AddGallery extends AbstractMethod
{
    public function init()
    {
        $this->rootServiceLocator = $this->serviceLocator->getServiceLocator();
        $this->translator = $this->rootServiceLocator->get('translator');
        $this->db = $this->rootServiceLocator->get('db');
        $this->objectTypesCollection = $this->rootServiceLocator->get('objectTypesCollection');
        $this->objectsCollection = $this->rootServiceLocator->get('objectsCollection');
        $this->imageGalleryModel = new ImageGallery($this->rootServiceLocator);     
        $this->request = $this->rootServiceLocator->get('request');
    }

    public function main()
    {
        $result = array();
        
        $parentObjectId = (int)$this->params()->fromRoute('id', 0); 
        if ($parentObjectId) {
            if (!$this->imageGalleryModel->isObjectRubric($parentObjectId)) {
                $result['errMsg'] = 'Объект ' . $parentObjectId . ' не является галереей';
                return $result;
            }
        }        
        
        $this->imageGalleryModel->setGalleryType(ImageGallery::RUBRIC)->setParentObjectId($parentObjectId);
        
        if (null === $this->params()->fromRoute('objectTypeId')) {
            $objectType = $this->objectTypesCollection->getType($this->imageGalleryModel->getRubricGuid());        
            $objectTypeId = $objectType->getId();
            $this->imageGalleryModel->setObjectTypeId($objectTypeId);
        } else {
            $objectTypeId = (int)$this->params()->fromRoute('objectTypeId');
            $this->imageGalleryModel->setObjectTypeId($objectTypeId);
            
            if (!$this->imageGalleryModel->isObjectTypeCorrect($objectTypeId)) {
                $result['errMsg'] = 'Передан неверный тип объекта ' . $objectTypeId;
                return $result;
            }
        }       
        
        $form = $this->imageGalleryModel->getForm();        
        $formConfig = $form['formConfig'];
        $formValues = $form['formValues'];
        $formMessages = array();
        
        if ($this->request->isPost()) {
            $tmp = $this->imageGalleryModel->add($this->request->getPost());
            if ($tmp['success']) {
                if (!$this->request->isXmlHttpRequest()) {
                    $this->flashMessenger()->addSuccessMessage('Галерея успешно добавлена');
                    $this->redirect()->toRoute('admin/method',array(
                        'module' => 'ImageGallery',
                        'method' => 'Edit',
                        'id' => $tmp['objectId'],
                    ));
                }

                return array(
                    'success' => true,
                    'msg' => 'Галерея успешно добавлена',
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
            'name' => 'content_template/ImageGallery/form_view.phtml',
            'data' => array(
                'jsArgs' => array(
                    'changeObjectTypeUrlTemplate' => $this->url()->fromRoute('admin/AddGallery', $changeObjectTypeUrlParams),
                ),
                'formConfig' => $formConfig,
                'formValues' => $formValues,
                'formMsg' => $formMessages,
            ),
        );
        
        return $result;        
    }
}