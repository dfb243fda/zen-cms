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
        $guideId = (int)$this->params()->fromRoute('id', 0);
        if (!$guideId) {
            throw new \Exception('parameter id does not transferred');
        }
        
        if (!$this->guidesModel->isGuidable($guideId)) {
            return array(
                'errMsg' => 'Не найден справочник ' . $guideId,
            );
        }
        
        if ($this->params()->fromRoute('task') && 'get_data' == $this->params()->fromRoute('task')) {    
            $result = $this->getData($guideId);
        } else {
            $result = $this->getWrapper($guideId);
        }
        
        return $result;
    }
    
    protected function getWrapper($guideId)
    {
        $result = array();
        
        $this->serviceLocator->get('viewHelperManager')->get('HeadScript')->appendFile(ROOT_URL_SEGMENT . '/js/ObjectTypes/guides.js');
        
        $result['tabs'] = array(
            array(
                'title' => $this->translator->translate('ObjectTypes:Object types'),
                'link' => $this->url()->fromRoute('admin/method', array(
                    'module' => 'ObjectTypes',
                    'method' => 'ObjectTypesList',                    
                )),                
            ),
            array(
                'title' => $this->translator->translate('ObjectTypes:Guides'),
                'link' => $this->url()->fromRoute('admin/method', array(
                    'module' => 'ObjectTypes',
                    'method' => 'GuidesList',
                )),
                'active' => true,
            ),
        );
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/' . CURRENT_THEME . '/tree_grid.phtml',
            'data' => array(
                'createBtn' => array(
                    'link' => $this->url()->fromRoute('admin/method', array(
                        'module' => 'ObjectTypes',
                        'method' => 'AddGuideItem',
                        'id'     => $guideId,
                    )),
                    'text' => 'Добавить термин в справочник',
                ),
                'url' => $this->url()->fromRoute('admin/GuideItemsList', array(
                    'task' => 'get_data',
                    'id'   => $guideId,
                )),  
                'columns' => array(
                    array(
                        array(                        
                            'title' => $this->translator->translate('ObjectTypes:Guide item name field'),
                            'field' => 'name',
                            'width' => '200',
                        ),
                        array(                        
                            'title' => '',
                            'field' => 'icons',
                            'width' => '200',
                        )
                    )                    
                ),
            ),            
        );
        
        return $result;
    }
    
    protected function getData($guideId)
    {
        $result = array();
        
        $result['items'] = array();        
        
        $guideItems = $this->guidesModel->getGuideItems($guideId);
        
        foreach ($guideItems as $row) {
            $row['state'] = 'open';
            
            $row['icons'] = array();
                        
            $row['icons']['editLink'] = $this->url()->fromRoute('admin/method', array(
                'module' => 'ObjectTypes',   
                'method' => 'EditGuideItem',
                'id' => $row['id']
            ));
            
            $row['icons']['delLink'] = 'zen.guides.delGuideItem(\'' . $this->url()->fromRoute('admin/method', array(
                'module' => 'ObjectTypes',   
                'method' => 'DelGuideItem',
            )) . '\', ' . $row['id'] . ')';
                
            $row['name'] = $this->translator->translateI18n($row['name']);
            
            $result['items'][] = $row;
        }
                
        return $result;
    }
}