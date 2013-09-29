<?php

namespace ImageGallery\Method;

use App\Method\AbstractMethod;

class EditGallery extends AbstractMethod
{
    public function main()
    {        
        $galEntity = $this->serviceLocator->get('ImageGallery\Entity\GalleryEntity');
        $galService = $this->serviceLocator->get('ImageGallery\Service\ImageGallery');
        $request = $this->serviceLocator->get('request');
        
        $result = array();
        
        if (null === $this->params()->fromRoute('id')) {
            $result['errMsg'] = 'Не переданы все необходимые параметры';
            return $result;
        } 
        
        $objectId = (int)$this->params()->fromRoute('id');
  
        if (!$galService->isObjectGallery($objectId)) {
            $result['errMsg'] = 'Галерея ' . $objectId . ' не найдена';
            return $result;
        } 
                        
        $galEntity->setObjectId($objectId);
                
        if (null !== $this->params()->fromRoute('objectTypeId')) {    
            $objectTypeId = (int)$this->params()->fromRoute('objectTypeId');
            if (!in_array($objectTypeId, $galService->getGalleryTypeIds())) {
                $result['errMsg'] = 'Тип данных ' . $objectTypeId . ' не является галереей';
                return $result;
            }
            $galEntity->setObjectTypeId($objectTypeId);
        }        
            
        
        if ($request->isPost()) {
            $form = $galEntity->getForm(false);
            
            $data = $request->getPost()->toArray();
            if (empty($data['common']['name'])) {
                $data['common']['name'] = $translator->translate('ImageGallery:(Gallery without name)');
            }            
            $form->setData($data);
            
            if ($form->isValid()) {
                if ($galEntity->editGallery($form->getData())) {
                    if (!$request->isXmlHttpRequest()) {
                        $this->flashMessenger()->addSuccessMessage('Галерея успешно обновлена');
                        $this->redirect()->toRoute('admin/method',array(
                            'module' => 'ImageGallery',
                            'method' => 'EditGallery',
                            'id' => $objectId,
                        ));
                    }

                    return array(
                        'success' => true,
                        'msg' => 'Галерея успешно обновлена',
                    );  
                } else {
                    return array(
                        'success' => false,
                        'msg' => 'При обновлении категории произошли ошибки',
                    );  
                }                       
            } else {
                $result['success'] = false;
            }
        } else {
            $form = $galEntity->getForm(true);    
        }
        
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/ImageGallery/gallery_form.phtml',
            'data' => array(
                'jsArgs' => array(
                    'changeObjectTypeUrlTemplate' => $this->url()->fromRoute('admin/EditGallery', array(
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