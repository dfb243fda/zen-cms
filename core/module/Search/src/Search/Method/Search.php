<?php

namespace Search\Method;

use App\Method\AbstractMethod;
use Search\Model\Search as SearchModel;


class Search extends AbstractMethod
{    
    public function main()
    {
        $searchIndexer = $this->serviceLocator->get('Search\Service\SearchIndexer');     
        
        if ($this->params()->fromPost('task') == 'refresh_search_index') {                   
            $searchIndexer->refreshIndex();
            $this->flashMessenger()->addSuccessMessage('Индекс успешно обновлен');
            return $this->redirect()->refresh();
        }
        
        $result = array(
            'contentTemplate' => array(
                'name' => 'content_template/Search/search_info.phtml',
                'data' => array(
                    'indexedPagesCount' => $searchIndexer->getIndexedPagesCount(),
                ),
            ),
        );
        
        if ($this->flashMessenger()->hasSuccessMessages()) {
            $result['msg'] = $this->flashMessenger()->getSuccessMessages();
        } 
        
        return $result;
    }
}