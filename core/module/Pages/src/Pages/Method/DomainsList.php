<?php

namespace Pages\Method;

use App\Method\AbstractMethod;

class DomainsList extends AbstractMethod
{    
    public function main()
    {
        $domainsTree = $this->getServiceLocator()->get('Pages\Service\DomainsTree');
                
        if ('get_data' == $this->params()->fromRoute('task')) {   
            $result = $domainsTree->getDomains();
        } else {
            $result = array(
                'contentTemplate' => array(
                    'name' => 'content_template/Pages/domains_tree.phtml',
                ),
            );
        }
        return $result;
    }
}