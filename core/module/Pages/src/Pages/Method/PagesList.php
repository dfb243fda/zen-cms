<?php

namespace Pages\Method;

use App\Method\AbstractMethod;
use Pages\Model\Pages;

class PagesList extends AbstractMethod
{    
    protected $rootServiceLocator;
    
    protected $translator;
    
    protected $request;
    
    protected $pagesModel;
    
    public function init()
    {
        $this->rootServiceLocator = $this->serviceLocator->getServiceLocator();
        $this->translator = $this->rootServiceLocator->get('translator');
        $this->request = $this->rootServiceLocator->get('request');
        $this->pagesModel = new Pages($this->rootServiceLocator);
    }


    public function main()
    {
        if ('get_data' == $this->params()->fromRoute('task')) {            
            if (null !== $this->request->getPost('id')) {
                $result = $this->getPages((int)$this->request->getPost('id'));
            } else {
                $result = $this->getDomains();
            }
        } else {
            $result = $this->getWrapper();
        }
        return $result;
    }
    
    protected function getWrapper()
    {
        $result = array();
        
        $this->rootServiceLocator->get('viewHelperManager')->get('InlineScript')->appendFile(ROOT_URL_SEGMENT . '/js/Pages/pages.js');
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/' . CURRENT_THEME . '/tree_grid.phtml',
            'data' => array(
                'url' => $this->url()->fromRoute('admin/PagesList', array(
                    'task'   => 'get_data',
                )),  
                'columns' => array(
                    array(
                        array(                        
                            'title' => $this->translator->translate('Pages:Page name field'),
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
    
    protected function getPages($parentId)
    {
        $result = array();
        
        $result['items'] = array();
        
        $pages = $this->pagesModel->getPages($parentId);        
                
        foreach ($pages as $row) {            
            $row['icons'] = array(
                'editLink' => $this->url()->fromRoute('admin/method', array(
                    'module' => 'Pages',   
                    'method' => 'EditPage',
                    'id'     => $row['id']
                )),
                'addLink' => $this->url()->fromRoute('admin/AddPage', array(
                    'pageId'     => $row['id']
                )),
                'delLink' => 'zen.pages.delPage(\'' . $this->url()->fromRoute('direct', array(
                    'module' => 'Pages',   
                    'method' => 'DeletePage',
                )) . '\', ' . $row['id'] . ')',
            );
            
            $result['items'][] = $row;
        }
                
        return $result;
    }
    
    protected function getDomains()
    {
        $result = array();
        
        $result['items'] = array();
                        
        $domains = $this->pagesModel->getDomains();
        
        foreach ($domains as $row) {  
            
            if (!empty($row['children'])) {
                foreach ($row['children'] as $k=>$row2) {
                    $row['children'][$k]['icons'] = array(
                        'editLink' => $this->url()->fromRoute('admin/method', array(
                            'module' => 'Pages',   
                            'method' => 'EditPage',
                            'id' => $row2['id']
                        )),
                        'addLink' => $this->url()->fromRoute('admin/AddPage', array(
                            'pageId' => $row2['id']
                        )),
                        'delLink' => 'zen.pages.delPage(\'' . $this->url()->fromRoute('direct', array(
                            'module' => 'Pages',   
                            'method' => 'DeletePage',
                        )) . '\', ' . $row2['id'] . ')',
                    );
                }
            }
            
            
            $row['icons'] = array(
                'addLink' => $this->url()->fromRoute('admin/AddPage', array(
                    'domainId' => $row['domain_id'],
                )),
            );
            
            $result['items'][] = $row;
        }
                
        return $result;
    }
}