<?php

namespace Menu\Method;

use App\Method\AbstractMethod;
use Menu\Model\Menu;


class MenuList extends AbstractMethod
{
    public function init()
    {
        $this->rootServiceLocator = $this->serviceLocator->getServiceLocator();
        $this->translator = $this->rootServiceLocator->get('translator');
        $this->request = $this->rootServiceLocator->get('request');
        $this->menuModel = new Menu($this->rootServiceLocator);    
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
        
        $this->rootServiceLocator->get('viewHelperManager')->get('HeadScript')->appendFile(ROOT_URL_SEGMENT . '/js/Menu/menu.js');
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/' . CURRENT_THEME . '/tree_grid.phtml',
            'data' => array(
                'createBtn' => array(
                    'text' => 'Добавить меню',
                    'link' => $this->url()->fromRoute('admin/method', array(
                        'module' => 'Menu',
                        'method' => 'AddMenu',
                    )),
                ),
                'url' => $this->url()->fromRoute('admin/MenuList', array(
                    'task'   => 'get_data',
                )),  
                'columns' => array(
                    array(
                        array(                        
                            'title' => $this->translator->translate('Menu:Name field'),
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
        $items = $this->menuModel->getItems($parentId);
        
        foreach ($items as $k=>$row) {
            $items[$k]['icons'] = array();
            $items[$k]['icons']['editLink'] = $this->url()->fromRoute('admin/method', array(
                'module' => 'Menu',
                'method' => 'Edit',
                'id' => $row['id']
            ));
            if (0 == $parentId) {
                $items[$k]['icons']['addLink'] = $this->url()->fromRoute('admin/method', array(
                    'module' => 'Menu',
                    'method' => 'AddMenuItem',
                    'id' => $row['id']
                ));
                $items[$k]['icons']['delLink'] = 'zen.menu.delMenu(\'' . $this->url()->fromRoute('admin/method', array(
                    'module' => 'Menu',   
                    'method' => 'Delete',
                )) . '\', ' . $row['id'] . ')';
            } else {
                $items[$k]['icons']['delLink'] = 'zen.menu.delMenuItem(\'' . $this->url()->fromRoute('admin/method', array(
                    'module' => 'Menu',   
                    'method' => 'Delete',
                )) . '\', ' . $row['id'] . ')';
            }
            
            
        }
        
        return array(
            'items' => $items,
        );
    }
    
}