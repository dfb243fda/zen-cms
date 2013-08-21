<?php

namespace Search\Method;

use App\Method\AbstractMethod;
use Search\Model\Search as SearchModel;


class Search extends AbstractMethod
{
    public function init()
    {
        $this->rootServiceLocator = $this->serviceLocator->getServiceLocator();
        $this->translator = $this->rootServiceLocator->get('translator');
        $this->request = $this->rootServiceLocator->get('request');
        $this->searchModel = new SearchModel($this->rootServiceLocator);    
    }
    
    public function main()
    {
        if ($this->request->getPost('task') == 'refresh_search_index') {
            $this->searchModel->refreshIndex();
            $this->flashMessenger()->addSuccessMessage('Индекс успешно обновлен');
            return $this->redirect()->refresh();
        }
        
        $result = array(
            'contentTemplate' => array(
                'name' => 'content_template/Search/search_info.phtml',
                'data' => array(
                    'indexedPagesCount' => $this->searchModel->getIndexedPagesCount(),
                ),
            ),
        );
        
        if ($this->flashMessenger()->hasSuccessMessages()) {
            $result['msg'] = $this->flashMessenger()->getSuccessMessages();
        } 
        
        return $result;
    }
}