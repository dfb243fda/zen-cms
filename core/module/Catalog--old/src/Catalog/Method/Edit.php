<?php

namespace Catalog\Method;

use App\Method\AbstractMethod;
use Catalog\Model\Catalog;

class Edit extends AbstractMethod
{
    protected $rootServiceLocator;
    
    protected $translator;
    
    protected $db;
    
    protected $catalogModel;
    
    public function init()
    {
        $this->rootServiceLocator = $this->serviceLocator->getServiceLocator();
        $this->catalogModel = new Catalog($this->rootServiceLocator);        
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
  
        if ($this->catalogModel->isObjectRubric($objectId)) {
            $catalogType = Catalog::RUBRIC;
        } elseif ($this->catalogModel->isObjectItem($objectId)) {
            $catalogType = Catalog::ITEM;
        } else {
            $result['errMsg'] = 'Объект ' . $objectId . ' не является ни рубрикой, ни новостью';
            return $result;
        }
        
        $this->catalogModel->setCatalogType($catalogType)->setObjectId($objectId);
        
        if (null !== $this->params()->fromRoute('objectTypeId')) {
            $objectTypeId = (int)$this->params()->fromRoute('objectTypeId');
            $this->catalogModel->setObjectTypeId($objectTypeId);
            
            if (!$this->catalogModel->isObjectTypeCorrect($objectTypeId)) {
                $result['errMsg'] = 'Передан неверный тип объекта ' . $objectTypeId;
                return $result;
            }
        }
        
        $form = $this->catalogModel->getForm();        
        $formConfig = $form['formConfig'];
        $formValues = $form['formValues'];
        $formMessages = array();
        
        if ($this->request->isPost()) {
            $tmp = $this->catalogModel->edit($this->request->getPost());
            if ($tmp['success']) {
                if ($catalogType == Catalog::RUBRIC) {
                    $successMsg = 'Каталог успешно обновлен';
                } else {
                    $successMsg = 'Товар успешно обновлен';
                }
                
                if (!$this->request->isXmlHttpRequest()) {
                    $this->flashMessenger()->addSuccessMessage($successMsg);
                    $this->redirect()->toRoute('admin/method', array(
                        'module' => 'Catalog',
                        'method' => 'Edit',
                        'id'     => $objectId,
                    ));
                }

                return array(
                    'success' => true,
                    'msg' => $successMsg,
                );                         
            } else {
                $result['success'] = false;
                $formMessages = $tmp['form']->getMessages();
                $formValues = $tmp['form']->getData();
            }
        }
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/Catalog/form_view.phtml',
            'data' => array(
                'jsArgs' => array(
                    'changeObjectTypeUrlTemplate' => $this->url()->fromRoute('admin/EditCatalog', array(
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