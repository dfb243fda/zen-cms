<?php

namespace Installer\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Session\Container as SessionContainer;

class ConfigFileCreator implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    
    /**
     * Set service manager
     *
     * @param ServiceManager $serviceManager
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function createConfigFile()
    {
        $fileManager = $this->serviceManager->get('fileManager');
        
        if (!is_dir(APPLICATION_PATH . '/config/autoload')) {
            $fileManager->mkdir(APPLICATION_PATH . '/config/autoload', true);
        }        
        
        $configFile = APPLICATION_PATH . '/config/application.config.php';
        
        $installSession = new SessionContainer('installer');
        
        $dsn = 'mysql:dbname=' . $installSession->step2['dbname'] . ';host=' . $installSession->step2['dbaddr'];
        $userName = $installSession->step2['dbuser'];
        $pass = $installSession->step2['dbpass'];
        $dbPref = $installSession->step2['dbpref'];
        
        $phpCode = <<<PHP
<?php

return function(&\$config) {
    \$config['isInstalled'] = true;
    \$config['dbPref'] = '{$dbPref}';
    \$config['db'] = array(
        'driver' => 'Pdo',
        'dsn' => '{$dsn}',
        'driver_options' => array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'',
        ),
        'username' => '{$userName}',
        'password' => '{$pass}',
        'profiler' => true,    
    );
};

PHP;
        
        file_put_contents($configFile, $phpCode);
        
        $configFile = APPLICATION_PATH . '/config/autoload/global.php';
        
        $phpCode = <<<PHP
<?php

return array(    

);
   
PHP;
        
        file_put_contents($configFile, $phpCode);
        
    }
    
}