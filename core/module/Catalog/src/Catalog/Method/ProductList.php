<?php

namespace Catalog\Method;

use App\Method\AbstractMethod;

class ProductList extends AbstractMethod
{
    public function main()
    {
        if ('get_data' == $this->params()->fromRoute('task')) {
            $menuTree = $this->serviceLocator->get('Catalog\Service\ProductsTree');
            
            $parentId = (int)$this->params()->fromPost('id', 0);
            $result = $menuTree->getItems($parentId);
        } else {
            $result = array(
                'contentTemplate' => array(
                    'name' => 'content_template/Catalog/products_tree.phtml',
                ),
            );
        }
        return $result;
    }    
}