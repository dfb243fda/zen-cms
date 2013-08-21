<?php

namespace Bootstrapper;

use Zend\Mvc\MvcEvent;
use Zend\Log\Logger;

class Module
{

    public function onBootstrap(MvcEvent $e)
    {
        $app = $e->getTarget();
        $locator = $app->getServiceManager();

        $config = $locator->get('config');

        $appConfig = $locator->get('ApplicationConfig');

        $request = $e->getRequest();
        $uri = $request->getUri();
        define('ROOT_URL_SEGMENT', $request->getBasePath());
        define('ROOT_URL', $uri->getScheme() . '://' . $uri->getHost() . ROOT_URL_SEGMENT);
        define('REQUEST_URL_SEGMENT', $request->getRequestUri());
        define('REQUEST_URL', $uri->getScheme() . '://' . $uri->getHost() . $request->getRequestUri());

        define('DB_PREF', $appConfig['dbPref']);
        
        /* Munee options */
        define('MUNEE_CACHE', $appConfig['module_listener_options']['cache_dir'] . '/munee');
        
        $configManager = $locator->get('configManager');

        $locator->get('translator')->setLocale($configManager->get('system', 'language'));

        $phpSettings = $appConfig['phpSettings'];
        foreach ($phpSettings as $key => $value) {
            ini_set($key, $value);
        }
        
        if ($locator->has('Rbac\Service\Authorize')) {
            $authService = $locator->get('Rbac\Service\Authorize');
            if ($authService->isAllowed('get_errors') || true == $appConfig['show_errors_to_everybody']) {
                ini_set('display_errors', 1);
                ini_set('display_startup_errors', 1);         
                
                $locator->get('viewManager')->getRouteNotFoundStrategy()->setDisplayExceptions(true);
                $locator->get('viewManager')->getRouteNotFoundStrategy()->setDisplayNotFoundReason(true);
                
                $locator->get('viewManager')->getExceptionStrategy()->setDisplayExceptions(true);
                
            } else {
                ini_set('display_errors', 0);
                ini_set('display_startup_errors', 0);     
                
                $locator->get('viewManager')->getRouteNotFoundStrategy()->setDisplayExceptions(false);
                $locator->get('viewManager')->getRouteNotFoundStrategy()->setDisplayNotFoundReason(false);
                
                $locator->get('viewManager')->getExceptionStrategy()->setDisplayExceptions(false);
            }
        }
        
        
        if ($configManager->has('system', 'timezone')) {
            date_default_timezone_set($configManager->get('system', 'timezone'));
        }   
        
        $logger = $locator->get('logger');
        
        $logger->addProcessor('App\Log\Processor\FullBacktrace');
        $logger->addProcessor('Zend\Log\Processor\Backtrace');
        $logger->addProcessor('App\Log\Processor\Request', 1, array(
            'request' => $e->getRequest(),
        ));
        if ($locator->has('users_auth_service')) {
            $logger->addProcessor('App\Log\Processor\User', 1, array(
                'userData' => $locator->get('users_auth_service')->getIdentity(),
            ));
        }
        
        foreach ($config['log_writers'] as $writer => $value) {
            switch ($writer) {
                case 'db':
                    $logDbWriterOptions = $value['options'];
                    $logDbWriterOptions['db'] = $locator->get('db');
                    $logDbWriterOptions['table'] = DB_PREF . $logDbWriterOptions['table'];
                    
                    $filter = new \Zend\Log\Filter\Priority($value['priority']);
                    
                    $logDbWriter = new \App\Log\Writer\Db($logDbWriterOptions);
                    $logDbWriter->addFilter($filter);
                    
                    $logger->addWriter($logDbWriter);
                    
                    break;
                
                case 'bugHunter':
                    $filter = new \Zend\Log\Filter\Priority($value['priority']);
                    $logBugHunterWriter = $locator->get('bugHunter');
                    $logBugHunterWriter->addFilter($filter);
                    
                    $logger->addWriter($logBugHunterWriter);
                    
                    break;
            }
        }
        
        register_shutdown_function(array($this, 'fatalErrorShutdownHandler'), $logger);
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getDynamicConfig($sm)
    {
        $db = $sm->get('db');

        $sqlRes = $db->query('select prefix, title from ' . DB_PREF . 'langs', array())->toArray();

        $languages = array();
        foreach ($sqlRes as $row) {
            $languages[$row['prefix']] = $row['title'];
        }

        $translator = $sm->get('translator');
        
        $configManager = $sm->get('configManager');
        
        $continents = array( 'Africa', 'America', 'Antarctica', 'Arctic', 'Asia', 'Atlantic', 'Australia', 'Europe', 'Indian', 'Pacific');

        $zonen = array();
        foreach (timezone_identifiers_list() as $zone) {
            $zone = explode('/', $zone);
            if (!in_array($zone[0], $continents)) {
                continue;
            }

            // This determines what gets set and translated - we don't translate Etc/* strings here, they are done later
            $exists = array(
                0 => ( isset($zone[0]) && $zone[0] ),
                1 => ( isset($zone[1]) && $zone[1] ),
                2 => ( isset($zone[2]) && $zone[2] ),
            );
            $exists[3] = ( $exists[0] && 'Etc' !== $zone[0] );
            $exists[4] = ( $exists[1] && $exists[3] );
            $exists[5] = ( $exists[2] && $exists[3] );

            $zonen[] = array(
                'continent' => ( $exists[0] ? $zone[0] : '' ),
                'city' => ( $exists[1] ? $zone[1] : '' ),
                'subcity' => ( $exists[2] ? $zone[2] : '' ),
                't_continent' => ( $exists[3] ? $translator->translate(str_replace('_', ' ', $zone[0])) : '' ),
                't_city' => ( $exists[4] ? $translator->translate(str_replace('_', ' ', $zone[1])) : '' ),
                't_subcity' => ( $exists[5] ? $translator->translate(str_replace('_', ' ', $zone[2])) : '' )
            );
        }
                
        $timeZones = array();
        $i = 0;
        foreach ( $zonen as $key => $zone ) {
            // Build value in an array to join later
            $value = array( $zone['continent'] );

            if ( empty( $zone['city'] ) ) {
                // It's at the continent level (generally won't happen)
                $display = $zone['t_continent'];
            } else {
                // It's inside a continent group

                // Continent optgroup
                if ( !isset( $zonen[$key - 1] ) || $zonen[$key - 1]['continent'] !== $zone['continent'] ) {
                    $label = $zone['t_continent'];
                    $timeZones[$i] = array(
                        'label' => $label
                    );
                }

                // Add the city to the value
                $value[] = $zone['city'];

                $display = $zone['t_city'];
                if ( !empty( $zone['subcity'] ) ) {
                    // Add the subcity to the value
                    $value[] = $zone['subcity'];
                    $display .= ' - ' . $zone['t_subcity'];
                }
            }

            // Build the value
            $value = join( '/', $value );
            $timeZones[$i]['options'][$value] = $display;

            // Close continent optgroup
            if ( !empty( $zone['city'] ) && ( !isset($zonen[$key + 1]) || (isset( $zonen[$key + 1] ) && $zonen[$key + 1]['continent'] !== $zone['continent']) ) ) {
                $i++;
            }
        }
        

        $format = $configManager->get('system', 'date_format') . ' ' . $configManager->get('system', 'time_format');

        $dateTime = new \DateTime();

        $dateTime->setTimezone(new \DateTimeZone('UTC'));
        $utcTime = $dateTime->format($format);

        $dateTime->setTimezone(new \DateTimeZone(date_default_timezone_get()));
        $localTime = $dateTime->format($format);

        return array(
            'form' => array(
                'general' => array(
                    'fieldsets' => array(
                        'system' => array(
                            'spec' => array(
                                'name' => 'system',
                                'elements' => array(
                                    'language' => array(
                                        'spec' => array(
                                            'type' => 'select',
                                            'name' => 'language',
                                            'options' => array(
                                                'label' => 'i18n::Dynamic config system language',
                                                'description' => 'i18n::Dynamic config system language description',
                                                'value_options' => $languages,
                                            ),
                                        ),
                                    ),
                                    'timezone' => array(
                                        'spec' => array(
                                            'type' => 'select',
                                            'name' => 'timezone',
                                            'options' => array(
                                                'label' => 'i18n::Dynamic config system timezone',
                                                'description' => sprintf($translator->translate('Dynamic config system timezone description'), $utcTime, $localTime),
                                                'value_options' => $timeZones,
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                    'input_filter' => array(
                        'system' => array(
                            'type' => 'Zend\InputFilter\InputFilter',
                            'timezone' => array(
                                'required' => true,
                            ),
                        ),
                    ),
                ),
            ),
        );
    }

    public function fatalErrorShutdownHandler($logger)
    {
        $lastError = error_get_last();
        if ($lastError['type'] === E_ERROR) {
            $logger->log(Logger::ERR, $lastError['message'], array(
                'file' => $lastError['file'],
                'line' => $lastError['line'],
            ));
        }
    }
    
    public function getTablesSql()
    {
        return file_get_contents(__DIR__ . '/tables/tables.sql');
    }

}
