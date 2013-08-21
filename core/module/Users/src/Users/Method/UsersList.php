<?php

namespace Users\Method;

use App\Method\AbstractMethod;
use Users\Model\Users;


class UsersList extends AbstractMethod
{
    public function init()
    {
        $this->rootServiceLocator = $this->serviceLocator->getServiceLocator();
        $this->translator = $this->rootServiceLocator->get('translator');
        $this->request = $this->rootServiceLocator->get('request');
        $this->usersModel = new Users($this->rootServiceLocator);    
    }


    public function main()
    {
        if ('get_data' == $this->params()->fromRoute('task')) {
            $result = $this->getItems();
        } else {
            $result = $this->getWrapper();
        }
        return $result;
    }
    
    protected function getWrapper()
    {
        $result = array();
        
        $this->rootServiceLocator->get('viewHelperManager')->get('HeadScript')->appendFile(ROOT_URL_SEGMENT . '/js/Users/users.js');
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/' . CURRENT_THEME . '/tree_grid.phtml',
            'data' => array(
                'createBtn' => array(
                    'text' => 'Добавить пользователя',
                    'link' => $this->url()->fromRoute('admin/method', array(
                        'module' => 'Users',
                        'method' => 'AddUser',
                    )),
                ),
                'url' => $this->url()->fromRoute('admin/UsersList', array(
                    'task'   => 'get_data',
                )),  
                'columns' => array(
                    array(
                        array(                        
                            'title' => $this->translator->translate('Users:Name field'),
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
    
    protected function getItems()
    {                
        $items = $this->usersModel->getItems();
        
        foreach ($items as $k=>$row) {
            $items[$k]['icons'] = array();
            $items[$k]['icons']['editLink'] = $this->url()->fromRoute('admin/method', array(
                'module' => 'Users',
                'method' => 'EditUser',
                'id' => $row['user_id']
            ));
            $items[$k]['icons']['delLink'] = 'zen.users.delUser(\'' . $this->url()->fromRoute('admin/method', array(
                'module' => 'Users',   
                'method' => 'DeleteUser',
            )) . '\', ' . $row['user_id'] . ')';            
        }
        
        return array(
            'items' => $items,
        );
    }
    
}