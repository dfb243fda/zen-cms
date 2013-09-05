<?php

namespace Search\Method;

use Pages\AbstractMethod\FeContentMethod;

class SearchResult extends FeContentMethod
{    
    public function main()
    {
        $searchEngine = $this->serviceLocator->get('Search\Service\SearchEngine');
        
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
        
        $result = $searchEngine->find($searchQuery, $pageNum);
        
        return $result;        
    }
    
}