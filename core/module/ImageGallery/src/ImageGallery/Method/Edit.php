<?php

namespace ImageGallery\Method;

use App\Method\AbstractMethod;
use ImageGallery\Model\ImageGallery;

class Edit extends AbstractMethod
{
    protected $rootServiceLocator;
    
    protected $translator;
    
    protected $db;
    
    protected $imageGalleryModel;
    
    public function init()
    {
        $this->rootServiceLocator = $this->serviceLocator->getServiceLocator();
        $this->imageGalleryModel = new ImageGallery($this->rootServiceLocator);        
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
  
        if ($this->imageGalleryModel->isObjectRubric($objectId)) {
            $galleryType = ImageGallery::RUBRIC;
        } elseif ($this->imageGalleryModel->isObjectItem($objectId)) {
            $galleryType = ImageGallery::ITEM;
        } else {
            $result['errMsg'] = 'Объект ' . $objectId . ' не является ни галереей, ни картинкой';
            return $result;
        }
        
        $this->imageGalleryModel->setGalleryType($galleryType)->setObjectId($objectId);
        
        if (null !== $this->params()->fromRoute('objectTypeId')) {
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
            $tmp = $this->imageGalleryModel->edit($this->request->getPost());
            if ($tmp['success']) {
                if (!$this->request->isXmlHttpRequest()) {
                    $this->flashMessenger()->addSuccessMessage('Галерея успешно обновлена');
                    $this->redirect()->toRoute('admin/method',array(
                        'module' => 'ImageGallery',
                        'method' => 'Edit',
                        'id' => $objectId,
                    ));
                }

                return array(
                    'success' => true,
                    'msg' => 'Галерея успешно обновлена',
                );         
            } else {
                $result['success'] = false;
                $formMessages = $tmp['form']->getMessages();
                $formValues = $tmp['form']->getData();
            }
        }
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/ImageGallery/form_view.phtml',
            'data' => array(
                'jsArgs' => array(
                    'changeObjectTypeUrlTemplate' => $this->url()->fromRoute('admin/EditGallery', array(
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