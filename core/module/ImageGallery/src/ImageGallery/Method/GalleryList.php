<?php

namespace ImageGallery\Method;

use App\Method\AbstractMethod;
use ImageGallery\Model\ImageGallery;


class GalleryList extends AbstractMethod
{
    public function init()
    {
        $this->rootServiceLocator = $this->serviceLocator->getServiceLocator();
        $this->translator = $this->rootServiceLocator->get('translator');
        $this->request = $this->rootServiceLocator->get('request');
        $this->imageGalleryModel = new ImageGallery($this->rootServiceLocator);    
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
        
        $this->rootServiceLocator->get('viewHelperManager')->get('HeadScript')->appendFile(ROOT_URL_SEGMENT . '/js/ImageGallery/gallery.js');
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/' . CURRENT_THEME . '/tree_grid.phtml',
            'data' => array(
                'createBtn' => array(
                    'text' => 'Добавить галерею',
                    'link' => $this->url()->fromRoute('admin/method', array(
                        'module' => 'ImageGallery',
                        'method' => 'AddGallery',
                    )),
                ),
                'url' => $this->url()->fromRoute('admin/GalleryList', array(
                    'task'   => 'get_data',
                )),  
                'columns' => array(
                    array(
                        array(                        
                            'title' => $this->translator->translate('ImageGallery:Gallery name field'),
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
        $items = $this->imageGalleryModel->getItems($parentId);
        
        foreach ($items as $k=>$row) {
            $items[$k]['icons'] = array();
            $items[$k]['icons']['editLink'] = $this->url()->fromRoute('admin/method', array(
                'module' => 'ImageGallery',
                'method' => 'Edit',
                'id' => $row['id']
            ));
            if (0 == $parentId) {
                $items[$k]['icons']['addLink'] = $this->url()->fromRoute('admin/method', array(
                    'module' => 'ImageGallery',
                    'method' => 'AddImage',
                    'id' => $row['id']
                ));
                $items[$k]['icons']['delLink'] = 'zen.image_gallery.delGallery(\'' . $this->url()->fromRoute('admin/method', array(
                    'module' => 'ImageGallery',   
                    'method' => 'Delete',
                )) . '\', ' . $row['id'] . ')';
            } else {
                $items[$k]['icons']['delLink'] = 'zen.image_gallery.delImage(\'' . $this->url()->fromRoute('admin/method', array(
                    'module' => 'ImageGallery',   
                    'method' => 'Delete',
                )) . '\', ' . $row['id'] . ')';
            }
            
            
        }
        
        return array(
            'items' => $items,
        );
    }
    
}