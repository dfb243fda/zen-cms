<?php

namespace ImageGallery\Method;

use App\Method\AbstractMethod;

class EditImage extends AbstractMethod
{
    public function main()
    {        
        $imageEntity = $this->serviceLocator->get('ImageGallery\Entity\ImageEntity');
        $galService = $this->serviceLocator->get('ImageGallery\Service\ImageGallery');
        $request = $this->serviceLocator->get('request');
        
        $result = array();
        
        if (null === $this->params()->fromRoute('id')) {
            $result['errMsg'] = 'Не переданы все необходимые параметры';            
            return $result;
        } 
        
        $objectId = (int)$this->params()->fromRoute('id');
  
        if (!$galService->isObjectImage($objectId)) {
            $result['errMsg'] = 'Изображение ' . $objectId . ' не найдено';
            return $result;
        }
        
        $imageEntity->setObjectId($objectId);
                
        if (null !== $this->params()->fromRoute('objectTypeId')) {  
            $objectTypeId = (int)$this->params()->fromRoute('objectTypeId');
            if (!in_array($objectTypeId, $galService->getImageTypeIds())) {
                $result['errMsg'] = 'Передан неверный тип объекта ' . $objectTypeId;
                return $result;
            }
            $imageEntity->setObjectTypeId($objectTypeId);
        }
        
        if ($request->isPost()) {
            $form = $imageEntity->getForm(false);
            
            $data = $request->getPost()->toArray();
            if (empty($data['common']['name'])) {
                $data['common']['name'] = $translator->translate('ImageGallery:(Image without name)');
            }
            $form->setData($data);
            
            if ($form->isValid()) {
                if ($imageEntity->editImage($form->getData())) {
                    if (!$request->isXmlHttpRequest()) {
                        $this->flashMessenger()->addSuccessMessage('Изображение успешно обновлено');
                        $this->redirect()->toRoute('admin/method',array(
                            'module' => 'ImageGallery',
                            'method' => 'EditImage',
                            'id' => $objectId,
                        ));
                    }

                    return array(
                        'success' => true,
                        'msg' => 'Изображение успешно обновлено',
                    );  
                } else {
                    return array(
                        'success' => false,
                        'msg' => 'При обновлении товара произошли ошибки',
                    );  
                }                       
            } else {
                $result['success'] = false;
            }
        } else {
            $form = $imageEntity->getForm(true);    
        }
        
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/ImageGallery/gallery_form.phtml',
            'data' => array(
                'jsArgs' => array(
                    'changeObjectTypeUrlTemplate' => $this->url()->fromRoute('admin/EditImage', array(
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