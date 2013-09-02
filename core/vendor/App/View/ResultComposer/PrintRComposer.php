<?php

namespace App\View\ResultComposer;

use App\View\Model\PrintRModel;

class PrintRComposer extends ComposerAbstract
{    
    public function getResult(array $resultArray)
    {
        if (!empty($resultArray['errors'])) {
            foreach ($resultArray['errors'] as $k => $v) {
                unset($resultArray['errors'][$k]['debug_backtrace']);
                unset($resultArray['errors'][$k]['err_context']);
            }
        }

        $eventManager = $this->serviceManager->get('application')->getEventManager();
        $eventManager->trigger('prepare_public_resources', $this, array($resultArray));

        $resultArray = array_merge($resultArray, $this->getViewResources($this->serviceManager->get('viewHelperManager')));

        $this->removeObjectsFromArray($resultArray);
        
        return new PrintRModel($resultArray);
    }
}