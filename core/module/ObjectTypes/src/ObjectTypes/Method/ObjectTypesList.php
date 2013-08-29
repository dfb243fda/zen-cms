<?php

namespace ObjectTypes\Method;

use App\Method\AbstractMethod;

class ObjectTypesList extends AbstractMethod
{    
    public function init()
    {
        $this->translator = $this->serviceLocator->get('translator');
    }


    public function main()
    {
        if ($this->params()->fromRoute('task') && 'get_data' == $this->params()->fromRoute('task')) {            
            $parentId = (int)$this->params()->fromPost('id', 0);
            $result = $this->getData($parentId);
        } else {
            $result = $this->getWrapper();
        }
        
        return $result;
    }
    
    protected function getWrapper()
    {
        $result = array();
        
        $this->serviceLocator->get('viewHelperManager')->get('HeadScript')->appendFile(ROOT_URL_SEGMENT . '/js/ObjectTypes/object_types.js');
        
        $result['tabs'] = array(
            array(
                'title' => $this->translator->translate('ObjectTypes:Object types'),
                'link' => $this->url()->fromRoute('admin/method', array(
                    'module' => 'ObjectTypes',
                    'method' => 'ObjectTypesList',                    
                )),
                'active' => true,
            ),
            array(
                'title' => $this->translator->translate('ObjectTypes:Guides'),
                'link' => $this->url()->fromRoute('admin/method', array(
                    'module' => 'ObjectTypes',
                    'method' => 'GuidesList',
                )),
            ),
        );
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/' . CURRENT_THEME . '/tree_grid.phtml',
            'data' => array(
                'createBtn' => array(
                    'link' => $this->url()->fromRoute('admin/method', array(
                        'module' => 'ObjectTypes',
                        'method' => 'AddObjectType',
                    )),
                    'text' => 'Создать тип данных',
                ),
                'url' => $this->url()->fromRoute('admin/ObjectTypesList', array(
                    'task' => 'get_data',
                )),  
                'columns' => array(
                    array(
                        array(                        
                            'title' => $this->translator->translate('ObjectTypes:Object type name field'),
                            'field' => 'name',
                            'width' => '200',
                        ),
                        array(                        
                            'title' => '',
                            'field' => 'icons',
                            'width' => '200',
                        )
                    )                    
                ),
            ),            
        );
        
        return $result;
    }
    
    protected function getData($parentId)
    {
        $result = array();
        
        $result['items'] = array();        
        
        $objectTypesCollection = $this->serviceLocator->get('objectTypesCollection');
        $objectTypes = $objectTypesCollection->getChildrenTypesList($parentId);
        
        foreach ($objectTypes as $row) {
            if ($row['children_cnt'] > 0) {
                $row['state'] = 'closed';
            }
            else {
                $row['state'] = 'open';
            }
            
            $row['icons'] = array();
            
            if ($row['is_guidable']) {
                $row['icons']['showLink'] = $this->url()->fromRoute('admin/GuideItemsList', array(
                    'id' => $row['id']
                ));
            }
            
            $row['icons']['editLink'] = $this->url()->fromRoute('admin/method', array(
                'module' => 'ObjectTypes',   
                'method' => 'EditObjectType',
                'id' => $row['id']
            ));
            
            $row['icons']['addLink'] = $this->url()->fromRoute('admin/method', array(
                'module' => 'ObjectTypes',   
                'method' => 'AddObjectType',
                'id' => $row['id'],
            ));
            
            if (!$row['is_locked']) {
                $row['icons']['delLink'] = 'zen.objectTypes.delObjectType(\'' . $this->url()->fromRoute('admin/method', array(
                    'module' => 'ObjectTypes',   
                    'method' => 'DelObjectType',
                )) . '\', ' . $row['id'] . ')';
            }
            
            $result['items'][] = $row;
        }
                
        return $result;
    }
}