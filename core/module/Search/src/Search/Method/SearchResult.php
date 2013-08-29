<?php

namespace Search\Method;

use Pages\AbstractMethod\FeContentMethod;
use Search\Model\Search as SearchModel;

class SearchResult extends FeContentMethod
{    
    public function main()
    {
        $searchModel = new SearchModel($this->serviceLocator);
        
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
        
        $result = $searchModel->find($searchQuery, $pageNum);
        
        return $result;        
    }
    
}