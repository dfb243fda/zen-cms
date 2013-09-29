<?php

namespace ImageGallery\Method;

use App\Method\AbstractMethod;

class AddImage extends AbstractMethod
{
    public function main()
    {
        $galService = $this->serviceLocator->get('ImageGallery\Service\ImageGallery');
        $imagesCollection = $this->serviceLocator->get('ImageGallery\Collection\ImagesCollection');
        $objectTypesCollection = $this->serviceLocator->get('objectTypesCollection');
        $request = $this->serviceLocator->get('request');
        $translator = $this->serviceLocator->get('translator');
        
        $result = array();
        
        $parentObjectId = (int)$this->params()->fromRoute('id', 0); 
        if ($parentObjectId) {
            if (!$galService->isObjectGallery($parentObjectId)) {
                $result['errMsg'] = 'Галерея ' . $parentObjectId . ' не найдена';
                return $result;
            }
        }     
        
        $imagesCollection->setParentObjectId($parentObjectId);
        
        if (null !== $this->params()->fromRoute('objectTypeId')) {
            $objectTypeId = (int)$this->params()->fromRoute('objectTypeId');
            $imagesCollection->setObjectTypeId($objectTypeId);
            
            if (!in_array($objectTypeId, $galService->getImageTypeIds())) {
                $result['errMsg'] = 'Тип данных ' . $objectTypeId . ' не является изображением';
                return $result;
            }
        }       
        
        if ($request->isPost()) {
            $form = $imagesCollection->getForm(false);
            
            $data = $request->getPost()->toArray();
            if (empty($data['common']['name'])) {
                $data['common']['name'] = $translator->translate('ImageGallery:(Image without name)');
            }
            $form->setData($data);
            
            if ($form->isValid()) {
                if ($imgId = $imagesCollection->addImage($form->getData())) {
                    if (!$request->isXmlHttpRequest()) {
                        $this->flashMessenger()->addSuccessMessage('Изображение добавлено');
                        return $this->redirect()->toRoute('admin/method',array(
                            'module' => 'ImageGallery',
                            'method' => 'EditImage',
                            'id' => $imgId,
                        ));
                    }
                    
                    return array(
                        'success' => true,
                        'msg' => 'Изображение добавлено',
                    );    
                } else {
                    $result['success'] = false;
                    $result['errMsg'] = 'При добавлении изображения произошли ошибки';
                }
            } else {
                $result['success'] = false;
            }
        } else {
            $form = $imagesCollection->getForm(true);
        }
        
        $params = array(
            'objectTypeId' => '--OBJECT_TYPE--',    
        );
        if (0 != $parentObjectId) {
            $params['id'] = $parentObjectId;
        }
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/ImageGallery/gallery_form.phtml',
            'data' => array(
                'jsArgs' => array(
                    'changeObjectTypeUrlTemplate' => $this->url()->fromRoute('admin/AddImage', $params),
                ),
                'form' => $form,
            ),
        );
        
        return $result;
    }
}