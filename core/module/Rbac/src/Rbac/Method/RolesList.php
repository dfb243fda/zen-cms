<?php

namespace Rbac\Method;

use App\Method\AbstractMethod;
use Rbac\Model\Roles;


class RolesList extends AbstractMethod
{
    public function init()
    {
        $this->rootServiceLocator = $this->serviceLocator->getServiceLocator();
        $this->translator = $this->rootServiceLocator->get('translator');
        $this->request = $this->rootServiceLocator->get('request');
        $this->rolesModel = new Roles($this->rootServiceLocator);    
    }


    public function main()
    {
        if ('get_data' == $this->params()->fromRoute('task')) {
            $result = $this->getItems((int)$this->request->getPost('id', 0));
        } else {
            $result = $this->getWrapper();
        }
        return $result;
    }
    
    protected function getWrapper()
    {
        $result = array();
        
        $this->rootServiceLocator->get('viewHelperManager')->get('HeadScript')->appendFile(ROOT_URL_SEGMENT . '/js/Rbac/roles.js');
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/' . CURRENT_THEME . '/tree_grid.phtml',
            'data' => array(
                'createBtn' => array(
                    'text' => 'Добавить роль',
                    'link' => $this->url()->fromRoute('admin/method', array(
                        'module' => 'Rbac',
                        'method' => 'AddRole',
                    )),
                ),
                'url' => $this->url()->fromRoute('admin/RolesList', array(
                    'task'   => 'get_data',
                )),  
                'columns' => array(
                    array(
                        array(                        
                            'title' => $this->translator->translate('Rbac:Name field'),
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
    
    protected function getItems($parentId)
    {                
        $items = $this->rolesModel->getItems($parentId);
        
        foreach ($items as $k=>$row) {
            $items[$k]['icons'] = array();
            $items[$k]['icons']['editLink'] = $this->url()->fromRoute('admin/method', array(
                'module' => 'Rbac',
                'method' => 'EditRole',
                'id' => $row['id']
            ));
            $items[$k]['icons']['addLink'] = $this->url()->fromRoute('admin/method', array(
                'module' => 'Rbac',
                'method' => 'AddRole',
                'id' => $row['id']
            ));
            $items[$k]['icons']['delLink'] = 'zen.roles.delRole(\'' . $this->url()->fromRoute('admin/method', array(
                'module' => 'Rbac',   
                'method' => 'DeleteRole',
            )) . '\', ' . $row['id'] . ')';            
        }
        
        return array(
            'items' => $items,
        );
    }
    
}