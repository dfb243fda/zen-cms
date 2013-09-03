<?php

namespace ObjectTypes\Method;

use App\Method\AbstractMethod;

class GuidesList extends AbstractMethod
{    
    public function main()
    {        
        $guidesList = $this->getServiceLocator()->get('ObjectTypes\Service\GuidesList');
        
        if ('get_data' == $this->params()->fromRoute('task')) {            
            $result = $guidesList->getGuides();
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
                    'name' => 'content_template/ObjectTypes/guides_list.phtml',
                ),
            );
        }
        
        return $result;
    }
}