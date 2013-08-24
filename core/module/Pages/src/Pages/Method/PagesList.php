<?php

namespace Pages\Method;

use App\Method\AbstractMethod;

class PagesList extends AbstractMethod
{    
    public function main()
    {
        $pagesTree = $this->getServiceLocator()->get('Pages\Service\PagesTree');
                
        if ('get_data' == $this->params()->fromRoute('task')) {            
            if (null !== $this->params()->fromPost('id')) {
                $result = $pagesTree->getPages((int)$this->params()->fromPost('id'));
            } else {
                $result = $pagesTree->getDomains();
            }
        } else {
            $result = $pagesTree->getWrapper();
        }
        return $result;
    }
}