<?php

namespace ObjectTypes\Method;

use App\Method\AbstractMethod;
use ObjectTypes\Model\Guides;

class DelGuideItem extends AbstractMethod
{
    protected $rootServiceLocator;
    
    protected $translator;
    
    protected $guidesModel;
    
    public function init()
    {
        $this->rootServiceLocator = $this->serviceLocator->getServiceLocator();
        $this->translator = $this->rootServiceLocator->get('translator');
        $this->guidesModel = new Guides($this->rootServiceLocator);
        $this->request = $this->rootServiceLocator->get('request');
    }

    public function main()
    {
        $result = array(
            'success' => false,
        );
        
        if (null === $this->request->getPost('id')) {
            $result['errMsg'] = 'Не переданы все необходимые параметры';
            return $result;
        }
        
        $guideItemId = (int)$this->request->getPost('id');
        
        if ($this->guidesModel->deleteGuideItem($guideItemId)) {
            $result['success'] = true;
            $result['msg'] = 'Термин удален';
        } else {
            $result['errMsg'] = 'Не удалось удалить термин';
        }
        
        return $result;
    }
}