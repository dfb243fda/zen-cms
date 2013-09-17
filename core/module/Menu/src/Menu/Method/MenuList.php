<?php

namespace Menu\Method;

use App\Method\AbstractMethod;

class MenuList extends AbstractMethod
{
    public function main()
    {
        if ('get_data' == $this->params()->fromRoute('task')) {
            $menuTree = $this->serviceLocator->get('Menu\Service\MenuTree');
            
            $parentId = (int)$this->params()->fromPost('id', 0);
            $result = $menuTree->getItems($parentId);
        } else {
            $result = array(
                'contentTemplate' => array(
                    'name' => 'content_template/Menu/menu_tree.phtml',
                ),
            );
        }
        return $result;
    }    
}