<?php

namespace News\Method;

use App\Method\AbstractMethod;

class AddNews extends AbstractMethod
{
    public function main()
    {
        $newsService = $this->serviceLocator->get('News\Service\News');
        $newsCollection = $this->serviceLocator->get('News\Collection\NewsCollection');
        $objectTypesCollection = $this->serviceLocator->get('objectTypesCollection');
        $request = $this->serviceLocator->get('request');
        $translator = $this->serviceLocator->get('translator');
        
        $result = array();
        
        $parentObjectId = (int)$this->params()->fromRoute('id', 0); 
        if ($parentObjectId) {
            if (!$newsService->isObjectRubric($parentObjectId)) {
                $result['errMsg'] = 'Объект ' . $parentObjectId . ' не является рубрикой';
                return $result;
            }
        }     
        
        $newsCollection->setParentObjectId($parentObjectId);
        
        if (null !== $this->params()->fromRoute('objectTypeId')) {
            $objectTypeId = (int)$this->params()->fromRoute('objectTypeId');
            $newsCollection->setObjectTypeId($objectTypeId);
            
            if (!in_array($objectTypeId, $newsService->getProductTypeIds())) {
                $result['errMsg'] = 'Тип данных ' . $objectTypeId . ' не является новостью';
                return $result;
            }
        }       
        
        if ($request->isPost()) {
            $form = $newsCollection->getForm(false);
            
            $data = $request->getPost()->toArray();
            if (empty($data['common']['name'])) {
                $data['common']['name'] = $translator->translate('News:(News without name)');
            }
            $form->setData($data);
            
            if ($form->isValid()) {
                if ($menuItemId = $newsCollection->addNews($form->getData())) {
                    if (!$request->isXmlHttpRequest()) {
                        $this->flashMessenger()->addSuccessMessage('Новость добавлена');
                        $this->redirect()->toRoute('admin/method',array(
                            'module' => 'News',
                            'method' => 'EditNews',
                            'id' => $menuItemId,
                        ));
                    }
                    
                    return array(
                        'success' => true,
                        'msg' => 'Новость добавлена',
                    );    
                } else {
                    $result['success'] = false;
                    $result['errMsg'] = 'При добавлении новости произошли ошибки';
                }
            } else {
                $result['success'] = false;
            }
        } else {
            $form = $newsCollection->getForm(true);
        }
        
        $params = array(
            'objectTypeId' => '--OBJECT_TYPE--',    
        );
        if (0 != $parentObjectId) {
            $params['id'] = $parentObjectId;
        }
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/News/news_form.phtml',
            'data' => array(
                'jsArgs' => array(
                    'changeObjectTypeUrlTemplate' => $this->url()->fromRoute('admin/AddNews', $params),
                ),
                'form' => $form,
            ),
        );
        
        return $result;
    }
}