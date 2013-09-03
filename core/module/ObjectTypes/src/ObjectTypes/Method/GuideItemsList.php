<?php

namespace ObjectTypes\Method;

use App\Method\AbstractMethod;
use ObjectTypes\Model\Guides;

class GuideItemsList extends AbstractMethod
{        
    protected $translator;
    
    protected $guidesModel;
    
    public function init()
    {
        $this->translator = $this->serviceLocator->get('translator');
        $this->guidesModel = new Guides($this->serviceLocator);
    }


    public function main()
    {
        $guideId = (int)$this->params()->fromRoute('id');
        if (!$guideId) {
            throw new \Exception('parameter id does not transferred');
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