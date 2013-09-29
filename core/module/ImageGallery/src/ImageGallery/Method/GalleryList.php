<?php

namespace ImageGallery\Method;

use App\Method\AbstractMethod;

class GalleryList extends AbstractMethod
{
    public function main()
    {
        if ('get_data' == $this->params()->fromRoute('task')) {
            $galleryTree = $this->serviceLocator->get('ImageGallery\Service\GalleryTree');
            
            $parentId = (int)$this->params()->fromPost('id', 0);
            $result = $galleryTree->getItems($parentId);
        } else {
            $result = array(
                'contentTemplate' => array(
                    'name' => 'content_template/ImageGallery/gallery_tree.phtml',
                ),
            );
        }
        return $result;
    }    
}