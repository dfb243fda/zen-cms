<?php

namespace ObjectTypes\Method;

use App\Method\AbstractMethod;
use ObjectTypes\Model\Guides;

class DelGuideItem extends AbstractMethod
{
    public function main()
    {
        $guidesModel = new Guides($this->serviceLocator);
        
        $result = array(
            'success' => false,
        );
        
        if (null === $this->params()->fromPost('id')) {
            $result['errMsg'] = 'Не переданы все необходимые параметры';
            return $result;
        }
        
        $guideItemId = (int)$this->params()->fromPost('id');
        
        if ($guidesModel->deleteGuideItem($guideItemId)) {
            $result['success'] = true;
            $result['msg'] = 'Термин удален';
        } else {
            $result['errMsg'] = 'Не удалось удалить термин';
        }
        
        return $result;
    }
}