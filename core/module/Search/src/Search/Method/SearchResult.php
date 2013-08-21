<?php

namespace Search\Method;

use Pages\Entity\FeContentMethod;
use Search\Model\Search as SearchModel;

class SearchResult extends FeContentMethod
{
    protected $searchModel;
    
    public function init()
    {
        $rootServiceLocator = $this->serviceLocator->getServiceLocator();
        $this->searchModel = new SearchModel($rootServiceLocator);
    }
    
    public function main()
    {
        $searchQuery = $this->params()->fromQuery('search');
        if (!is_array($searchQuery)) {
            $searchQuery = array(
                'all' => $searchQuery,
            );
        }
        $pageNum = (int)$this->params()->fromQuery('pn', 1);
        if ($pageNum < 1) {
            $pageNum = 1;
        }
        
        $result = $this->searchModel->find($searchQuery, $pageNum);
        
        return $result;        
    }
    
}