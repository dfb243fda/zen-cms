<?php

namespace ContactForms\Method;

use App\Method\AbstractMethod;

class FormsList extends AbstractMethod
{
    protected $formsTable = 'contact_forms';
    
    public function init()
    {
        $this->rootServiceLocator = $this->getServiceLocator();
        $this->translator = $this->rootServiceLocator->get('translator');
        $this->db = $this->rootServiceLocator->get('db');
        $this->request = $this->rootServiceLocator->get('request');
        
        $this->formsTable = DB_PREF . $this->formsTable;
    }
    
    public function main()
    {
        if ('get_data' == $this->params()->fromRoute('task')) { 
            $result = $this->getData();
        } else {
            $result = $this->getWrapper();
        }
        return $result;
    }
    
    protected function getData()
    {
        $result = array();
        
        $result['items'] = array();
        
        $sqlRes = $this->db->query('select * from ' . $this->formsTable, array())->toArray();
        
        foreach ($sqlRes as $row) {
            $row['state'] = 'open';
            
            $row['icons'] = array(
                'editLink' => $this->url()->fromRoute('admin/method', array(
                    'module' => 'ContactForms',   
                    'method' => 'EditForm',
                    'id'     => $row['id']
                )),
                'addLink' => $this->url()->fromRoute('admin/method', array(
                    'module' => 'ContactForms',   
                    'method' => 'AddForm',
                )),
                'delLink' => 'zen.contact_forms.delForm(\'' . $this->url()->fromRoute('admin/method', array(
                    'module' => 'ContactForms',   
                    'method' => 'DelForm',
                )) . '\', ' . $row['id'] . ')',
            );
            
            $result['items'][] = $row;
        }
        
        return $result;
    }
    
    protected function getWrapper()
    {
        $result = array();
        
        $this->rootServiceLocator->get('viewHelperManager')->get('HeadScript')->appendFile(ROOT_URL_SEGMENT . '/js/ContactForms/contact_forms.js');
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/' . CURRENT_THEME . '/tree_grid.phtml',
            'data' => array(
                'createBtn' => array(
                    'text' => $this->translator->translate('Contact forms create form'),
                    'link' => $this->url()->fromRoute('admin/method', array(
                        'module' => 'ContactForms',
                        'method' => 'AddForm',
                    )),
                ),
                'url' => $this->url()->fromRoute('admin/FormsList', array(
                    'task'   => 'get_data',
                )),  
                'columns' => array(
                    array(
                        array(                        
                            'title' => $this->translator->translate('ContactForms:Name field'),
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
}