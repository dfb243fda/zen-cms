<?php

namespace ObjectTypes\Method;

use App\Method\AbstractMethod;

class DelGuideItem extends AbstractMethod
{
    public function main()
    {        
        $result = array();
        
        if (null === $this->params()->fromPost('id')) {
            $result['errMsg'] = 'Не переданы все необходимые параметры';
            return $result;
        }
        
        $guideItemId = (int)$this->params()->fromPost('id');        
        
        $guideItemsCollection = $this->serviceLocator->get('ObjectTypes\Collection\GuideItemsCollection');
        
        if ($guideItemsCollection->deleteGuideItem($guideItemId)) {
            $result['success'] = true;
            $result['msg'] = 'Термин удален';
        } else {
            $result['success'] = false;
            $result['errMsg'] = 'Не удалось удалить термин';
        }
        
        return $result;
    }
}