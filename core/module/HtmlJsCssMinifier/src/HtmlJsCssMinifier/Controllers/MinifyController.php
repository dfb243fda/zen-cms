<?php
/** 
 * @link http://github.com/dkcwd/dkcwd-zf2-munee for the canonical source repository
 * @author Dave Clark dave@dkcwd.com.au 
 * @copyright Dave Clark 2012
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace HtmlJsCssMinifier\Controllers;

use Zend\Mvc\Controller\AbstractActionController;
use Munee\Dispatcher;
use Munee\Request;

class MinifyController extends AbstractActionController
{
    /**
     * Directly outputs the return value of the munee Dispatcher object
     * run() method to emulate the approach originally used by Cody Lundquist.
     * 
     * @return void     
     */
    public function minifyAction()
    {         
        require_once CORE_PATH . '/vendor/meenie/javascript-packer/class.JavaScriptPacker.php';
        require_once CORE_PATH . '/vendor/meenie/munee/config/bootstrap.php';
                 
        $headerController = $this->serviceLocator->get('HtmlJsCssMinifier\Service\HeaderSetter');
      
        $result = Dispatcher::run(new Request(), array(
            'headerController' => $headerController,
        ));        
        
        $this->response->setContent($result); 
        return $this->response;   
    }
}
