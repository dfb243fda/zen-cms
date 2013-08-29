<?php

namespace ObjectTypes\Method;

use App\Method\AbstractMethod;

class GuidesList extends AbstractMethod
{    
    public function init()
    {
        $this->translator = $this->serviceLocator->get('translator');
    }


    public function main()
    {        
        if ($this->params()->fromRoute('task') && 'get_data' == $this->params()->fromRoute('task')) {            
            $result = $this->getData();
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
            ),
            array(
                'title' => $this->translator->translate('ObjectTypes:Guides'),
                'link' => $this->url()->fromRoute('admin/method', array(
                    'module' => 'ObjectTypes',
                    'method' => 'GuidesList',
                )),
                'active' => true,
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
                    'text' => 'Создать справочник',
                ),
                'url' => $this->url()->fromRoute('admin/GuidesList', array(
                    'task' => 'get_data',
                )),  
                'columns' => array(
                    array(
                        array(                        
                            'title' => $this->translator->translate('ObjectTypes:Guide name field'),
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
    
    protected function getData()
    {
        $result = array();
        
        $result['items'] = array();        
        
        $objectTypesCollection = $this->serviceLocator->get('objectTypesCollection');
        $guides = $objectTypesCollection->getGuidesData();
               
        foreach ($guides as $id=>$row) {            
            $row['state'] = 'open';
            
            $row['icons'] = array(
                'showLink' => $this->url()->fromRoute('admin/GuideItemsList', array(
                    'id' => $row['id']
                )),
                'editLink' => $this->url()->fromRoute('admin/method', array(
                    'module' => 'ObjectTypes',   
                    'method' => 'EditObjectType',
                    'id' => $row['id']
                )),
                'addLink' => $this->url()->fromRoute('admin', array(
                    'module' => 'ObjectTypes',   
                    'method' => 'AddObjectType',
                    'id' => $row['id'],
                )),
            );
            
            if (!$row['is_locked']) {
                $row['icons']['delLink'] = 'zen.objectTypes.delObjectType(\'' . $this->url()->fromRoute('admin', array(
                    'module' => 'ObjectTypes',   
                    'method' => 'DelObjectType',
                    'id' => $row['id']
                )) . '\')';
            }
            
            $result['items'][] = $row;
        }
                
        return $result;
    }
}