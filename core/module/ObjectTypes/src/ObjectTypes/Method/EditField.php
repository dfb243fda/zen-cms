<?php

namespace ObjectTypes\Method;

use App\Method\AbstractMethod;

use ObjectTypes\Model\ObjectTypes as ObjectTypesModel;

class EditField extends AbstractMethod
{
    protected $translator;
    
    protected $serviceManager;
    
    protected $request;
    
    protected $objectTypesModel;
    
    public function init()
    {
        $this->rootServiceLocator = $this->serviceLocator->getServiceLocator();
        $this->translator = $this->rootServiceLocator->get('translator');
        $this->request = $this->rootServiceLocator->get('request');
        $this->objectTypesModel = new ObjectTypesModel($this->rootServiceLocator);
    }
    
    public function main()
    {
        $result = array();
        
        if (null !== $this->params()->fromRoute('fieldId') && null !== $this->params()->fromRoute('groupId')) {            
            $fieldId = (int)$this->params()->fromRoute('fieldId');
            $groupId = (int)$this->params()->fromRoute('groupId');
            
            $tmp = $this->objectTypesModel->editField($fieldId, $groupId, $this->request->getPost());
            
            if ($tmp['success']) {
                $result['name'] = $tmp['name'];
                $result['title'] = $tmp['title'];
                $result['fieldTypeName'] = $tmp['fieldTypeName'];
                
                $result['success'] = true;
                $result['msg'] = 'Поле успешно изменено';
            } else {
                $result['success'] = false;
                $form = $tmp['form'];
                $result['formMsg'] = $form->getMessages();
/*                
                $form = $tmp['form'];                
                $formMessages = $form->getMessages();
                if (!empty($formMessages)) {
                    $result['errMsg'] = array();
                    foreach ($formMessages as $field=>$messages) {
                        foreach ($messages as $msg) {
                            $result['errMsg'][] = $field . ' ' . $msg;
                        }
                    } 
                }  
*/
            }
        } else {
            $result['success'] = false;
            $result['errMsg'] = 'Не переданы все необходимые параметры';
        }
        
        return $result;
    }
    
}