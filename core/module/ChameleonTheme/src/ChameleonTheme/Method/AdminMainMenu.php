<?php

namespace ChameleonTheme\Method;

use App\Method\AbstractMethod;

class AdminMainMenu extends AbstractMethod
{        
    public function main()
    {        
        $moduleManager = $this->getServiceLocator()->get('moduleManager');
                
        $blocks = $moduleManager->getMenuGroups();
        
        foreach ($blocks as $block_key => $block_data) {
            foreach ($block_data['items'] as $k => $v) {
                if (! $this->isAllowed('be_method_access', $v['module'] . ':' . $v['method']) ) {
                    unset($blocks[$block_key]['items'][$k]);
                }
            }
        }
        
        foreach ($blocks as $block_key => $block_data) {
            foreach ($block_data['items'] as $k => $v) {                
                $params = array(
                    'module' => $v['module'],
                    'method' => $v['method'],
                );
                if (isset($v['additional_params'])) {
                    $params = array_merge($params, $v['additional_params']);
                }
                
                $blocks[$block_key]['items'][$k]['link'] = $this->url()->fromRoute('admin/method', $params);
                
   //             if ($this->request->getQuery('module') == $v['module'] && $this->request->getQuery('method') == $v['method']) {
     //               $blocks[$block_key]['items'][$k]['active'] = true;
       //         }
            }
        }
                
        return array(
            'mainMenu' => $blocks,
        );
    }
}