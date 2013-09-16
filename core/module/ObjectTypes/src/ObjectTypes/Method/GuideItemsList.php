<?php

namespace ObjectTypes\Method;

use App\Method\AbstractMethod;

class GuideItemsList extends AbstractMethod
{        
    public function main()
    {
        $objectTypesCollection = $this->serviceLocator->get('objectTypesCollection');
        
        $guideId = (int)$this->params()->fromRoute('id');
        if (!$guideId) {
            throw new \Exception('parameter id does not transferred');
        }
        
        if (!$objectType = $objectTypesCollection->getType($guideId)) {
            $result['errMsg'] = 'Не найден тип данных ' . $guideId;
            return $result;
        }
        
        if (!$objectType->getIsGuidable()) {
            $result['errMsg'] = 'Тип данных ' . $objectType->getName() . ' не является справочником';
            return $result;
        }
        
        $guideItemsList = $this->serviceLocator->get('ObjectTypes\Service\GuideItemsList');
        
        $guideItemsList->setGuideId($guideId);
        
        if ($this->params()->fromRoute('task') && 'get_data' == $this->params()->fromRoute('task')) {    
            $result = $guideItemsList->getGuideItems();
        } else {
            $translator = $this->serviceLocator->get('translator');
            
            $result = array(
                'tabs' => array(
                    array(
                        'title' => $translator->translate('ObjectTypes:Object types'),
                        'link' => $this->url()->fromRoute('admin/method', array(
                            'module' => 'ObjectTypes',
                            'method' => 'ObjectTypesList',                    
                        )),                
                    ),
                    array(
                        'title' => $translator->translate('ObjectTypes:Guides'),
                        'link' => $this->url()->fromRoute('admin/method', array(
                            'module' => 'ObjectTypes',
                            'method' => 'GuidesList',
                        )),
                        'active' => true,
                    ),
                ),
                'contentTemplate' => array(
                    'name' => 'content_template/ObjectTypes/guide_items_list.phtml',
                    'data' => array(
                        'guideId' => $guideId,
                    ),
                ),
            );
        }
        
        return $result;
    }    
}