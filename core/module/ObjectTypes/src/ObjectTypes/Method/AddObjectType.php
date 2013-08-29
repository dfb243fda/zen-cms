<?php

namespace ObjectTypes\Method;

use App\Method\AbstractMethod;

class AddObjectType extends AbstractMethod
{    
    public function main()
    {
        $objectTypesCollection = $this->serviceLocator->get('ObjectTypesCollection');
        $translator = $this->serviceLocator->get('translator');
        
        $result = array();
        
        $parentId = (int)$this->params()->fromRoute('id', 0);
        
        if (0 != $parentId) {
            if (null === $objectTypesCollection->getType($parentId)) {
                $result['success'] = false;
                $result['errMsg'] = 'Тип данных ' . $parentId . ' не найден';
                return $result;
            }
        }
        
        $objectTypeId = $objectTypesCollection->addType($parentId, $translator->translate('ObjectTypes:New object type'));

        $this->redirect()->toRoute('admin/method', array(
            'module' => 'ObjectTypes',
            'method' => 'EditObjectType',
            'id'     => $objectTypeId
        ));
        
        $result['success'] = true;
        return $result;
    }
}