<?php

namespace ImageGallery\Method;

use App\Method\AbstractMethod;

class AddGallery extends AbstractMethod
{
    public function main()
    {
        $galleryService = $this->serviceLocator->get('ImageGallery\Service\ImageGallery');
        $galleriesCollection = $this->serviceLocator->get('ImageGallery\Collection\GalleriesCollection');
        $objectTypesCollection = $this->serviceLocator->get('objectTypesCollection');
        $request = $this->serviceLocator->get('request');
        $translator = $this->serviceLocator->get('translator');
        
        $result = array();
        
        if (null !== $this->params()->fromRoute('objectTypeId')) {
            $objectTypeId = (int)$this->params()->fromRoute('objectTypeId');
            $galleriesCollection->setObjectTypeId($objectTypeId);
            
            if (!in_array($objectTypeId, $galleryService->getGalleryTypeIds())) {
                $result['errMsg'] = 'Тип данных ' . $objectTypeId . ' не является галереей';
                return $result;
            }
        }       
        
        if ($request->isPost()) {
            $form = $galleriesCollection->getForm(false);
            
            $data = $request->getPost()->toArray();
            if (empty($data['common']['name'])) {
                $data['common']['name'] = $translator->translate('ImageGallery:(Gallery without name)');
            }
            $form->setData($data);
            
            if ($form->isValid()) {                
                if ($galId = $galleriesCollection->addGallery($form->getData())) {
                    if (!$request->isXmlHttpRequest()) {
                        $this->flashMessenger()->addSuccessMessage('Галерея создана');
                        $this->redirect()->toRoute('admin/method',array(
                            'module' => 'ImageGallery',
                            'method' => 'EditGallery',
                            'id' => $galId,
                        ));
                    }
                    
                    return array(
                        'success' => true,
                        'msg' => 'Галерея создана',
                    );    
                } else {
                    $result['success'] = false;
                    $result['errMsg'] = 'При создании галереи произошли ошибки';
                }
            } else {
                $result['success'] = false;
            }
        } else {
            $form = $galleriesCollection->getForm(true);
        }
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/ImageGallery/gallery_form.phtml',
            'data' => array(
                'jsArgs' => array(
                    'changeObjectTypeUrlTemplate' => $this->url()->fromRoute('admin/AddGallery', array(
                        'objectTypeId' => '--OBJECT_TYPE--',    
                    )),
                ),
                'form' => $form,
            ),
        );
        
        return $result;
    }
    
}