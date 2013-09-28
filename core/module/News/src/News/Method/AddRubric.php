<?php

namespace News\Method;

use App\Method\AbstractMethod;

class AddRubric extends AbstractMethod
{
    public function main()
    {
        $newsService = $this->serviceLocator->get('News\Service\News');
        $rubricsCollection = $this->serviceLocator->get('News\Collection\RubricsCollection');
        $objectTypesCollection = $this->serviceLocator->get('objectTypesCollection');
        $request = $this->serviceLocator->get('request');
        $translator = $this->serviceLocator->get('translator');
        
        $result = array();
        
        if (null !== $this->params()->fromRoute('objectTypeId')) {
            $objectTypeId = (int)$this->params()->fromRoute('objectTypeId');
            $rubricsCollection->setObjectTypeId($objectTypeId);
            
            if (!in_array($objectTypeId, $newsService->getRubricTypeIds())) {
                $result['errMsg'] = 'Тип данных ' . $objectTypeId . ' не является рубрикой новостей';
                return $result;
            }
        }       
        
        if ($request->isPost()) {
            $form = $rubricsCollection->getForm(false);
            
            $data = $request->getPost()->toArray();
            if (empty($data['common']['name'])) {
                $data['common']['name'] = $translator->translate('News:(Rubric without name)');
            }
            $form->setData($data);
            
            if ($form->isValid()) {                
                if ($rubricId = $rubricsCollection->addRubric($form->getData())) {
                    if (!$request->isXmlHttpRequest()) {
                        $this->flashMessenger()->addSuccessMessage('Рубрика создана');
                        $this->redirect()->toRoute('admin/method',array(
                            'module' => 'News',
                            'method' => 'EditRubric',
                            'id' => $rubricId,
                        ));
                    }
                    
                    return array(
                        'success' => true,
                        'msg' => 'Рубрика создана',
                    );    
                } else {
                    $result['success'] = false;
                    $result['errMsg'] = 'При создании рубрики произошли ошибки';
                }
            } else {
                $result['success'] = false;
            }
        } else {
            $form = $rubricsCollection->getForm(true);
        }
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/News/news_form.phtml',
            'data' => array(
                'jsArgs' => array(
                    'changeObjectTypeUrlTemplate' => $this->url()->fromRoute('admin/AddRubric', array(
                        'objectTypeId' => '--OBJECT_TYPE--',    
                    )),
                ),
                'form' => $form,
            ),
        );
        
        return $result;
    }
    
}