<?php

namespace News\Method;

use App\Method\AbstractMethod;

class NewsList extends AbstractMethod
{
    public function main()
    {
        if ('get_data' == $this->params()->fromRoute('task')) {
            $menuTree = $this->serviceLocator->get('News\Service\NewsTree');
            
            $parentId = (int)$this->params()->fromPost('id', 0);
            $result = $menuTree->getItems($parentId);
        } else {
            $result = array(
                'contentTemplate' => array(
                    'name' => 'content_template/News/news_tree.phtml',
                ),
            );
        }
        return $result;
    }    
}