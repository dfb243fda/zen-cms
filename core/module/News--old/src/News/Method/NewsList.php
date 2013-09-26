<?php

namespace News\Method;

use App\Method\AbstractMethod;
use News\Model\News;


class NewsList extends AbstractMethod
{
    public function init()
    {
        $this->rootServiceLocator = $this->serviceLocator->getServiceLocator();
        $this->translator = $this->rootServiceLocator->get('translator');
        $this->request = $this->rootServiceLocator->get('request');
        $this->newsModel = new News($this->rootServiceLocator);    
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
        
        $this->rootServiceLocator->get('viewHelperManager')->get('HeadScript')->appendFile(ROOT_URL_SEGMENT . '/js/News/news.js');
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/' . CURRENT_THEME . '/tree_grid.phtml',
            'data' => array(
                'createBtn' => array(
                    'text' => 'Добавить категорию новостей',
                    'link' => $this->url()->fromRoute('admin/method', array(
                        'module' => 'News',
                        'method' => 'AddNews',
                    )),
                ),
                'url' => $this->url()->fromRoute('admin/NewsList', array(
                    'task'   => 'get_data',
                )),  
                'columns' => array(
                    array(
                        array(                        
                            'title' => $this->translator->translate('News:Title field'),
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
        $items = $this->newsModel->getItems($parentId);
        
        foreach ($items as $k=>$row) {
            $items[$k]['icons'] = array();
            $items[$k]['icons']['editLink'] = $this->url()->fromRoute('admin/method', array(
                'module' => 'News',
                'method' => 'Edit',
                'id' => $row['id']
            ));
            if (0 == $parentId) {
                $items[$k]['icons']['addLink'] = $this->url()->fromRoute('admin/method', array(
                    'module' => 'News',
                    'method' => 'AddNewsItem',
                    'id' => $row['id']
                ));
                $items[$k]['icons']['delLink'] = 'zen.news.delNews(\'' . $this->url()->fromRoute('admin/method', array(
                    'module' => 'News',   
                    'method' => 'Delete',
                )) . '\', ' . $row['id'] . ')';
            } else {
                $items[$k]['icons']['delLink'] = 'zen.news.delNewsItem(\'' . $this->url()->fromRoute('admin/method', array(
                    'module' => 'News',   
                    'method' => 'Delete',
                )) . '\', ' . $row['id'] . ')';
            }
            
            
        }
        
        return array(
            'items' => $items,
        );
    }
    
}