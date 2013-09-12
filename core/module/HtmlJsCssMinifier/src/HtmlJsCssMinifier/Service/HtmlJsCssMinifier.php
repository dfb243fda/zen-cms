<?php

namespace HtmlJsCssMinifier\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class HtmlJsCssMinifier implements ServiceManagerAwareInterface
{
    /**
     * @var type Zend\ServiceManager\ServiceManager
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
    
    public function prepareHeadLink($minify = true)
    {
        $viewHelperManager = $this->serviceManager->get('viewHelperManager');
        
        $linkContainer = $viewHelperManager->get('headLink')->getContainer();
        
        $links = $linkContainer->getValue();        
        if (is_object($links)) {
            $links = array($links);
        }
        
        $result = array();
        $prev = null;
        $tmp = array();
        
        foreach ($links as $v) {
            if (!isset($v->rel) || $v->rel != 'stylesheet') {
                if (!empty($tmp)) {
                    $tmp2 = array();
                    foreach ($tmp as $tmpVal) {
                        if (empty($tmp2)) {
                            $tmpVal2 = clone $tmpVal;
                            unset($tmpVal2->href);
                            $tmp2 = array(
                                'optimize' => true,
                                'options' => $tmpVal2,
                                'items' => array(),
                            );
                        }

                        $tmp2['items'][] = $tmpVal->href;
                    }

                    $result[] = $tmp2;
                    $tmp = array();
                }
                
                $result[] = array(
                    'optimize' => false,
                    'item' => $v,
                );
                $prev = null;
            } else {
                if (null === $prev) {
                    $tmp[] = $v;
                } else {
                    $hasDiff = false;                    
                    foreach ($v as $k2=>$v2) {
                        if (is_array($v2)) {
                            foreach ($v2 as $k3=>$v3) {
                                if (!array_key_exists($k3, $prev->$k2) || $prev->$k2[$k3] != $v3) {                                        
                                    $hasDiff = true;
                                }                      
                            }
                        } else {
                            if ($k2 != 'href') {
                                if (!property_exists($prev, $k2) || $prev->$k2 != $v2) {
                                    $hasDiff = true;
                                }
                            }                            
                        }
                    }

                    if ($hasDiff) {
                        $tmp2 = array();
                        if (!empty($tmp)) {
                            foreach ($tmp as $tmpVal) {
                                if (empty($tmp2)) {
                                    $tmpVal2 = clone $tmpVal;
                                    unset($tmpVal2->href);
                                    $tmp2 = array(
                                        'optimize' => true,
                                        'options' => $tmpVal2,
                                        'items' => array(),
                                    );
                                }

                                $tmp2['items'][] = $tmpVal->href;
                            }

                            $result[] = $tmp2;
                        }                

                        $prev = null;
                        $tmp = array($v);
                    } else {
                        $tmp[] = $v;
                    }
                }
            }
            
            $prev = $v;
        }
        
        if (!empty($tmp)) {
            $tmp2 = array();
            foreach ($tmp as $tmpVal) {
                if (empty($tmp2)) {
                    $tmpVal2 = clone $tmpVal;
                    unset($tmpVal2->href);
                    $tmp2 = array(
                        'optimize' => true,
                        'options' => $tmpVal2,
                        'items' => array(),
                    );
                }

                $tmp2['items'][] = $tmpVal->href;
            }

            $result[] = $tmp2;            
        }
        
        $newLinks = array();
        
        foreach ($result as $v) {
            if ($v['optimize']) {
                $tmp = clone $v['options'];
                
                $controllerPluginManager = $this->serviceManager->get('ControllerPluginManager');
                
                
                $tmp->href = $controllerPluginManager->get('url')->fromRoute('minify', array(), array(
                    'query' => array(
                        'files' => implode(',', $v['items']),
                        'minify' => $minify ? 'true' : 'false',
                    ),            
                ));   
                
                $newLinks[] = $tmp;
            } else {
                $newLinks[] = $v['item'];
            }
        }
        
        $linkContainer->exchangeArray($newLinks);
    }
    
    protected function prepareScript($scriptContainer, $minify)
    {
        $scripts = $scriptContainer->getValue();        
        if (is_object($scripts)) {
            $scripts = array($scripts);
        }
        
        $result = array();
        $prev = null;
        $tmp = array();
        
        foreach ($scripts as $v) {
            if (!array_key_exists('src', $v->attributes)) {
                if (!empty($tmp)) {
                    $tmp2 = array();
                    foreach ($tmp as $tmpVal) {
                        if (empty($tmp2)) {
                            $tmpVal2 = clone $tmpVal;
                            unset($tmpVal2->attributes['src']);
                            $tmp2 = array(
                                'optimize' => true,
                                'options' => $tmpVal2,
                                'items' => array(),
                            );
                        }

                        $tmp2['items'][] = $tmpVal->attributes['src'];
                    }

                    $result[] = $tmp2;
                    $tmp = array();
                }
                
                $result[] = array(
                    'optimize' => false,
                    'item' => $v,
                );
                $prev = null;
            } else {
                if (null === $prev) {
                    $tmp[] = $v;
                } else {
                    $hasDiff = false;                    
                    foreach ($v as $k2=>$v2) {
                        if (is_array($v2)) {
                            foreach ($v2 as $k3=>$v3) {
                                if ($k2 != 'attributes' && $k3 != 'src') {
                                    if (!array_key_exists($k3, $prev->$k2) || $prev->$k2[$k3] != $v3) {                                        
                                        $hasDiff = true;
                                    }
                                }                                
                            }
                        } else {
                            if (!property_exists($prev, $k2) || $prev->$k2 != $v2) {
                                $hasDiff = true;
                            }
                        }
                    }

                    if ($hasDiff) {
                        if (!empty($tmp)) {
                            $tmp2 = array();
                            foreach ($tmp as $tmpVal) {
                                if (empty($tmp2)) {
                                    $tmpVal2 = clone $tmpVal;
                                    unset($tmpVal2->attributes['src']);
                                    $tmp2 = array(
                                        'optimize' => true,
                                        'options' => $tmpVal2,
                                        'items' => array(),
                                    );
                                }

                                $tmp2['items'][] = $tmpVal->attributes['src'];
                            }

                            $result[] = $tmp2;
                        }                        

                        $prev = null;
                        $tmp = array($v);
                    } else {
                        $tmp[] = $v;
                    }
                }
            }
            
            $prev = $v;
        }
        
        if (!empty($tmp)) {
            $tmp2 = array();
            foreach ($tmp as $tmpVal) {
                if (empty($tmp2)) {
                    $tmpVal2 = clone $tmpVal;
                    unset($tmpVal2->attributes['src']);
                    $tmp2 = array(
                        'optimize' => true,
                        'options' => $tmpVal2,
                        'items' => array(),
                    );
                }

                $tmp2['items'][] = $tmpVal->attributes['src'];
            }

            $result[] = $tmp2;
        }
        
        $newScripts = array();
        
        foreach ($result as $v) {
            if ($v['optimize']) {
                $tmp = clone $v['options'];
                
                $controllerPluginManager = $this->serviceManager->get('ControllerPluginManager');
                
                
                $tmp->attributes['src'] = $controllerPluginManager->get('url')->fromRoute('minify', array(), array(
                    'query' => array(
                        'files' => implode(',', $v['items']),
                        'minify' => $minify ? 'true' : 'false',
                    ),            
                ));   
                
                $newScripts[] = $tmp;
            } else {
                $newScripts[] = $v['item'];
            }
        }
        
        $scriptContainer->exchangeArray($newScripts);
    }
    
    public function prepareInlineScript($minify = true)
    {
        $viewHelperManager = $this->serviceManager->get('viewHelperManager');
        
        $scriptContainer = $viewHelperManager->get('inlineScript')->getContainer();
            
        $this->prepareScript($scriptContainer, $minify);
    }
    
    public function prepareHeadScript($minify = true)
    {
        $viewHelperManager = $this->serviceManager->get('viewHelperManager');
        
        $scriptContainer = $viewHelperManager->get('headScript')->getContainer();
            
        $this->prepareScript($scriptContainer, $minify);
    }
    
    public function minifyHtml($html, $options)
    {
        $options = array_merge(array(
            'minifyCss' => true,
            'minifyJs' => true,
            'jsCleanComments' => true,
        ), $options);
        
        $viewHelperManager = $this->serviceManager->get('ViewHelperManager');
        
        $jSqueeze = new \JSqueeze;
                
        $minifierOptions = array(
            'xhtml' => $viewHelperManager->get('doctype')->isXhtml(),
            'cssMinifier' => $options['minifyCss'] ? array('Minify_CSS', 'minify') : null,
            'jsMinifier' => $options['minifyJs'] ? array($jSqueeze, 'squeeze') : null,
            'jsCleanComments' => $options['jsCleanComments'],
        );

        $html = \Minify_HTML::minify($html, $minifierOptions);
        
        return $html;
    }
}