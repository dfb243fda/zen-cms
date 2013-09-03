<?php

namespace ObjectTypes\Method;

use App\Method\AbstractMethod;

class ObjectTypesList extends AbstractMethod
{    
    public function main()
    {
        $objectTypesTree = $this->getServiceLocator()->get('ObjectTypes\Service\ObjectTypesTree');
                
        if ('get_data' == $this->params()->fromRoute('task')) {            
            $parentId = (int)$this->params()->fromPost('id', 0);
            $result = $objectTypesTree->getObjectTypes($parentId);
        } else {
            $translator = $this->serviceLocator->get('translator');
            
            $result = array(
                'tabs' => array(
                    array(
                        'title' => $translator->translate('ObjectTypes:Object types'),
                        'link' => $this->url()->fromRoute('admin/method', array(
                            'module' => 'ObjectTypes',
                            'method' => 'ObjectTypesList',                    
                        )),
                        'active' => true,
                    ),
                    array(
                        'title' => $translator->translate('ObjectTypes:Guides'),
                        'link' => $this->url()->fromRoute('admin/method', array(
                            'module' => 'ObjectTypes',
                            'method' => 'GuidesList',
                        )),
                    ),
                ),
                'contentTemplate' => array(
                    'name' => 'content_template/ObjectTypes/object_types_tree.phtml',
                ),
            );
        }
        return $result;
    }
}