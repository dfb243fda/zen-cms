<?php

namespace Pages\Method;

use App\Method\AbstractMethod;
use Pages\Model\Domains;

class DomainsList extends AbstractMethod
{    
    protected $rootServiceLocator;
    
    protected $translator;
    
    protected $domainsModel;
    
    protected $request;
    
    public function init()
    {        
        $this->rootServiceLocator = $this->serviceLocator->getServiceLocator();
        $this->translator = $this->rootServiceLocator->get('translator');
        $this->domainsModel = new Domains($this->rootServiceLocator);        
        $this->request = $this->rootServiceLocator->get('request');
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
        
        $this->rootServiceLocator->get('viewHelperManager')->get('HeadScript')->appendFile(ROOT_URL_SEGMENT . '/js/Pages/pages.js');
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/' . CURRENT_THEME . '/tree_grid.phtml',
            'data' => array(
                'createBtn' => array(
                    'text' => 'Добавить домен',
                    'link' => $this->url()->fromRoute('admin/method', array(
                        'module' => 'Pages',
                        'method' => 'AddDomain',
                    )),
                ),
                'url' => $this->url()->fromRoute('admin/DomainsList', array(
                    'task'   => 'get_data',
                )),  
                'columns' => array(
                    array(
                        array(                        
                            'title' => $this->translator->translate('Pages:Domain field'),
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
    
    protected function getDomains()
    {
        $result = array();
        
        $result['items'] = array();
        
        $domains = $this->domainsModel->getDomains();
                
        foreach ($domains as $row) {            
            $row['icons'] = array(
                'editLink' => $this->url()->fromRoute('admin/method', array(
                    'module' => 'Pages',   
                    'method' => 'EditDomain',
                    'id'     => $row['id']
                )),
                'delLink' => 'zen.pages.delDomain(\'' . $this->url()->fromRoute('admin/method', array(
                    'module' => 'Pages',   
                    'method' => 'DeleteDomain',
                )) . '\', ' . $row['id'] . ')',
            );
            
            $result['items'][] = $row;
        }
                
        return $result;
    }
}