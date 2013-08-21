<?php

namespace ImageGallery\Method;

use App\Method\AbstractMethod;
use ImageGallery\Model\ImageGallery;

class AddImage extends AbstractMethod
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
                
        if (null === $this->params()->fromRoute('id')) {
            $result['errMsg'] = 'Не передана рубрика для добавления';
            return $result;
        }
        
        $parentObjectId = (int)$this->params()->fromRoute('id'); 
        if (!$this->imageGalleryModel->isObjectRubric($parentObjectId)) {
            $result['errMsg'] = 'Тип объекта ' . $parentObjectId . ' не является рубрикой новостей';
            return $result;
        }
        
        $this->imageGalleryModel->setGalleryType(ImageGallery::ITEM)->setParentObjectId($parentObjectId);
        
        if (null === $this->params()->fromRoute('objectTypeId')) {
            $objectType = $this->objectTypesCollection->getType($this->imageGalleryModel->getItemGuid());        
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
                    $this->flashMessenger()->addSuccessMessage('Картинка успешно добавлена');
                    $this->redirect()->toRoute('admin/method',array(                        
                        'module' => 'ImageGallery',
                        'method' => 'Edit',
                        'id' => $tmp['objectId'],
                    ));
                }

                return array(
                    'success' => 1,
                    'msg' => 'Картинка успешно добавлена',
                );         
            } else {
                $formMessages = $tmp['form']->getMessages();
                $formValues = $tmp['form']->getData();
            }
        }
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/ImageGallery/form_view.phtml',
            'data' => array(
                'jsArgs' => array(
                    'changeObjectTypeUrlTemplate' => $this->url()->fromRoute('admin/AddImage', array(
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