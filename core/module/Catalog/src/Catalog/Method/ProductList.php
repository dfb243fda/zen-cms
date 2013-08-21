<?php

namespace Catalog\Method;

use App\Method\AbstractMethod;
use Catalog\Model\Catalog;


class ProductList extends AbstractMethod
{
    public function init()
    {
        $this->rootServiceLocator = $this->serviceLocator->getServiceLocator();
        $this->translator = $this->rootServiceLocator->get('translator');
        $this->request = $this->rootServiceLocator->get('request');
        $this->catalogModel = new Catalog($this->rootServiceLocator);    
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
        
        $this->rootServiceLocator->get('viewHelperManager')->get('HeadScript')->appendFile(ROOT_URL_SEGMENT . '/js/Catalog/catalog.js');
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/' . CURRENT_THEME . '/tree_grid.phtml',
            'data' => array(
                'createBtn' => array(
                    'text' => 'Добавить каталог',
                    'link' => $this->url()->fromRoute('admin/method', array(
                        'module' => 'Catalog',
                        'method' => 'AddCatalog',
                    )),
                ),
                'url' => $this->url()->fromRoute('admin/ProductList', array(
                    'task'   => 'get_data',
                )),  
                'columns' => array(
                    array(
                        array(                        
                            'title' => $this->translator->translate('Catalog:Name field'),
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
        $items = $this->catalogModel->getItems($parentId);
        
        foreach ($items as $k=>$row) {
            $items[$k]['icons'] = array();
            $items[$k]['icons']['editLink'] = $this->url()->fromRoute('admin/method', array(
                'module' => 'Catalog',
                'method' => 'Edit',
                'id' => $row['id']
            ));
            if (0 == $parentId) {
                $items[$k]['icons']['addLink'] = $this->url()->fromRoute('admin/method', array(
                    'module' => 'Catalog',
                    'method' => 'AddProduct',
                    'id' => $row['id']
                ));
                $items[$k]['icons']['delLink'] = 'zen.catalog.delCatalog(\'' . $this->url()->fromRoute('admin/method', array(
                    'module' => 'Catalog',   
                    'method' => 'Delete',
                )) . '\', ' . $row['id'] . ')';
            } else {
                $items[$k]['icons']['delLink'] = 'zen.catalog.delProduct(\'' . $this->url()->fromRoute('admin/method', array(
                    'module' => 'Catalog',   
                    'method' => 'Delete',
                )) . '\', ' . $row['id'] . ')';
            }
            
            
        }
        
        return array(
            'items' => $items,
        );
    }
    
}