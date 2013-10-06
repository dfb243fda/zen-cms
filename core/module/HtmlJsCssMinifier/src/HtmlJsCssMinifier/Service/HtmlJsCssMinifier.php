<?php

namespace HtmlJsCssMinifier\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use App\Utility\GeneralUtility;

class HtmlJsCssMinifier implements ServiceManagerAwareInterface
{
    /**
     * @var type Zend\ServiceManager\ServiceManager
     */
    protected $serviceManager;
    
    protected $minifyDir = 'minify';
    
    protected $ignoreJsFiles = array();
    
    protected $ignoreCssFiles = array();
    
    protected $minifyCss = true;
    
    protected $minifyJs = true;
    
    /**
     * Set service manager
     *
     * @param ServiceManager $serviceManager
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function setIgnoreJsFiles($files)
    {
        $this->ignoreJsFiles = $files;
        return $this;
    }
    
    public function setIgnoreCssFiles($files)
    {
        $this->ignoreCssFiles = $files;
        return $this;
    }
    
    public function setMinifyCss($minifyCss)
    {
        $this->minifyCss = $minifyCss;
        return $this;
    }
    
    public function setMinifyJs($minifyJs)
    {
        $this->minifyJs = $minifyJs;
        return $this;
    }
    
    /**
     * return bool
     */
    protected function shouldIgnoreLink($link)
    {                
        if (isset($link->href)) {
            if (GeneralUtility::isValidUrl($link->href)) {
                if (0 !== stripos($link->href, ROOT_URL)) {
                    return true;
                }
            } elseif (0 === strpos($link->href, '//')) {
                $request = $this->serviceManager->get('request');
                $uri = $request->getUri();
                
                if (0 !== stripos($uri->getScheme() . ':' .  $link->href, ROOT_URL)) {
                    return true;
                } 
            }
            
            foreach ($this->ignoreCssFiles as $pattern) {
                if (preg_match($pattern, $link->href)) {
                    return true;
                }
            }
        }
        
        return (!isset($link->rel) || $link->rel != 'stylesheet');
    }
    
    /**
     * $todo не работает с внешними url, надо исправить
     * @param type $files
     * @return \Minify_Source
     * @throws \Exception
     */
    protected function getMinifySources($files)
    {                 
        $request = $this->serviceManager->get('request');
        $uri = $request->getUri();
        
        $minifySources = array();
        foreach ($files as $k=>$file) {            
            if (GeneralUtility::isValidUrl($file)) {
                if (0 === stripos($file, ROOT_URL)) {
                    $filePath = PUBLIC_PATH . substr($file, strlen(ROOT_URL));
                } else {
       //             $filePath = $file;
                }
            } elseif (0 === strpos($file, '//')) {
                $file = $uri->getScheme() . ':' . $file;
                if (0 === stripos($file, ROOT_URL)) {
                    $filePath = PUBLIC_PATH . substr($file, strlen(ROOT_URL));
                } else {
      //              $filePath = $file;
                }
            } elseif (0 === strpos($file, '/')) {
                $filePath = $_SERVER['DOCUMENT_ROOT'] . $file;
            } else {
                $uriPath = $uri->getPath();
                if (false !== ($pos = strrpos($uriPath, '/'))) {
                    $uriPath = substr($uriPath, 0, $pos);
                }
                $file = $uriPath . '/' . $file;
                $filePath = $_SERVER['DOCUMENT_ROOT'] . $file;
            }
            
            if (null === $filePath) {
                throw new \Exception('this file is in output hosting ' . $file);
            }
            
            $spec = array(
                'filepath' => $filePath,
            );
            $minifySources[] = new \Minify_Source($spec);
        }
        
        return $minifySources;
    }
    
    protected function isSimilarLinks($script1, $script2 = null)
    {
        if (null === $script2) {
            return false;
        }
        
        $hasDiff = false;                    
        foreach ($script1 as $propKey=>$propVal) {
            if (is_array($propVal)) {
                foreach ($propVal as $subPropKey=>$subPropVal) {
                    if (!array_key_exists($subPropKey, $script2->$propKey) || $script2->$propKey[$subPropKey] != $subPropVal) {                                        
                        $hasDiff = true;
                    }                      
                }
            } else {
                if ($propKey != 'href') {
                    if (!property_exists($script2, $propKey) || $script2->$propKey != $propVal) {
                        $hasDiff = true;
                    }
                }                            
            }
        }
        
        return !$hasDiff;
    }
    
    protected function getCombinedLink($combinedScripts)
    {
        $result = null;
        
        if (!empty($combinedScripts)) {
            $files = array();
            
            $result = null;
            foreach ($combinedScripts as $script) {
                if (null === $result) {
                    $result = clone $script;
                }
                unset($result->href);
                
                $files[] = $script->href;
            }
            
            $result->href = $this->getCssFileUrl($files);
        }

        return $result;
    }
    
    public function prepareHeadLink()
    {
        $viewHelperManager = $this->serviceManager->get('viewHelperManager');
        
        $linkContainer = $viewHelperManager->get('headLink')->getContainer();
        
        $links = $linkContainer->getValue();        
        if (is_object($links)) {
            $links = array($links);
        }
        
        $newLinks = array();
        $combinedLinks = array();
        $lastLink = null;
        
        foreach ($links as $link) {
            if ($this->shouldIgnoreLink($link)) {
                if ($combinedLinkItem = $this->getCombinedLink($combinedLinks)) {
                    $newLinks[] = $combinedLinkItem;
                    $combinedLinks = array();
                }
                $newLinks[] = $link;
                $lastLink = null;
            } else {
                if (!$this->isSimilarLinks($link, $lastLink)) {
                    if ($combinedLinkItem = $this->getCombinedLink($combinedLinks)) {
                        $newLinks[] = $combinedLinkItem;
                        $combinedLinks = array();
                    }                    
                }  
                $combinedLinks[] = $link;                
                $lastLink = $link;
            }
        }
        
        if ($combinedLinkItem = $this->getCombinedLink($combinedLinks)) {
            $newLinks[] = $combinedLinkItem;
        }
        
        $linkContainer->exchangeArray($newLinks);
    }
    
    protected function getCssFileUrl($files)
    {
        $fileManager = $this->serviceManager->get('fileManager');
        
        $filesStr = implode(',', $files);
        
        $md5 = md5($filesStr);
        
        $dirPath = PUBLIC_PATH . '/' . $this->minifyDir . '/css/' . substr($md5, 0, 6);  
        $dirUrl = ROOT_URL_SEGMENT . '/' . $this->minifyDir . '/css/' . substr($md5, 0, 6);
        
        $fileName = substr($md5, 6) . '.css';
        $filePath = $dirPath . '/' . $fileName;
        $fileUrl = $dirUrl . '/' . $fileName;
        
        if (file_exists($filePath)) {
            return $fileUrl;
        }
        if (!is_dir($dirPath)) {
            $fileManager->mkDir($dirPath, true);
        }
        
        $args = array();
        if (!$this->minifyCss) {
            $args['minifiers'] = array(
                \Minify::TYPE_CSS => '',
            );
        }
        
        $minifySources = $this->getMinifySources($files);
        $fileContent = \Minify::combine($minifySources, $args);
        
        file_put_contents($filePath, $fileContent);
        $fileManager->fixPermissions($filePath);
                
        return $fileUrl;
    }
    
    protected function getJsFileUrl($files)
    {
        $fileManager = $this->serviceManager->get('fileManager');
        
        $filesStr = implode(',', $files);
        
        $md5 = md5($filesStr);
                
        $dirPath = PUBLIC_PATH . '/' . $this->minifyDir . '/js/' . substr($md5, 0, 6);  
        $dirUrl = ROOT_URL_SEGMENT . '/' . $this->minifyDir . '/js/' . substr($md5, 0, 6);
        
        $fileName = substr($md5, 6) . '.js';
        $filePath = $dirPath . '/' . $fileName;
        $fileUrl = $dirUrl . '/' . $fileName;
        
        if (file_exists($filePath)) {
            return $fileUrl;
        }
        if (!is_dir($dirPath)) {
            $fileManager->mkDir($dirPath, true);
        }
                    
        $args = array();
        if (!$this->minifyJs) {
            $args['minifiers'] = array(
                \Minify::TYPE_JS => '',
            );
        }
        
        $minifySources = $this->getMinifySources($files);
        $fileContent = \Minify::combine($minifySources, $args);
        
        file_put_contents($filePath, $fileContent);
        $fileManager->fixPermissions($filePath);
                
        return $fileUrl;
    }
    
    /**
     * return bool
     */
    protected function shouldIgnoreScript($script)
    {
        if (isset($script->attributes['src'])) {
            if (GeneralUtility::isValidUrl($script->attributes['src'])) {
                if (0 !== stripos($script->attributes['src'], ROOT_URL)) {
                    return true;
                }
            } elseif (0 === strpos($script->attributes['src'], '//')) {
                $request = $this->serviceManager->get('request');
                $uri = $request->getUri();
                
                if (0 !== stripos($uri->getScheme() . ':' . $script->attributes['src'], ROOT_URL)) {
                    return true;
                } 
            }
            
            foreach ($this->ignoreJsFiles as $pattern) {
                if (preg_match($pattern, $script->attributes['src'])) {
                    return true;
                }
            }
        }
        
        return (!array_key_exists('src', $script->attributes));
    }
    
    protected function isSimilarScripts($script1, $script2 = null)
    {
        if (null === $script2) {
            return false;
        }
        
        $hasDiff = false;                    
        foreach ($script1 as $propKey=>$propVal) {
            if (is_array($propVal)) {
                foreach ($propVal as $subPropKey=>$subPropVal) {
                    if ($propKey != 'attributes' && $subPropKey != 'src') {
                        if (!array_key_exists($subPropKey, $script2->$propKey) || $script2->$propKey[$subPropKey] != $subPropVal) {                                        
                            $hasDiff = true;
                        }
                    }                                
                }
            } else {
                if (!property_exists($script2, $propKey) || $script2->$propKey != $propVal) {
                    $hasDiff = true;
                }
            }
        }
        
        return !$hasDiff;
    }
    
    protected function getCombinedScript($combinedScripts)
    {
        $result = null;
        
        if (!empty($combinedScripts)) {
            $files = array();
            
            $result = null;
            foreach ($combinedScripts as $script) {
                if (null === $result) {
                    $result = clone $script;
                }
                unset($result->attributes['src']);
                
                $files[] = $script->attributes['src'];
            }
            
            $result->attributes['src'] = $this->getJsFileUrl($files);
        }

        return $result;
    }
    
    protected function prepareScript($scriptContainer)
    {
        $scripts = $scriptContainer->getValue();        
        if (is_object($scripts)) {
            $scripts = array($scripts);
        }
        
        $newScripts = array();
        $combinedScripts = array();
        $lastScript = null;
        
        foreach ($scripts as $script) {
            if ($this->shouldIgnoreScript($script)) {
                if ($combinedScriptItem = $this->getCombinedScript($combinedScripts)) {
                    $newScripts[] = $combinedScriptItem;
                    $combinedScripts = array();
                }
                $newScripts[] = $script;
                $lastScript = null;
            } else {
                if (!$this->isSimilarScripts($script, $lastScript)) {
                    if ($combinedScriptItem = $this->getCombinedScript($combinedScripts)) {
                        $newScripts[] = $combinedScriptItem;
                        $combinedScripts = array();
                    }                    
                }  
                $combinedScripts[] = $script;                
                $lastScript = $script;
            }
        }
        
        if ($combinedScriptItem = $this->getCombinedScript($combinedScripts)) {
            $newScripts[] = $combinedScriptItem;
        }
        
        $scriptContainer->exchangeArray($newScripts);
    }      
    
    public function prepareInlineScript()
    {
        $viewHelperManager = $this->serviceManager->get('viewHelperManager');
        
        $scriptContainer = $viewHelperManager->get('inlineScript')->getContainer();
        
        $this->prepareScript($scriptContainer);
    }
    
    public function prepareHeadScript()
    {
        $viewHelperManager = $this->serviceManager->get('viewHelperManager');
        
        $scriptContainer = $viewHelperManager->get('headScript')->getContainer();
            
        $this->prepareScript($scriptContainer);
    }
    
    public function minifyHtml($html, $options = array())
    {
        $options = array_merge(array(
            'minifyCss' => true,
            'minifyJs' => true,
            'jsCleanComments' => true,
        ), $options);
        
        $viewHelperManager = $this->serviceManager->get('ViewHelperManager');
                        
        $minifierOptions = array(
            'xhtml' => $viewHelperManager->get('doctype')->isXhtml(),
            'cssMinifier' => $options['minifyCss'] ? array('Minify_CSS', 'minify') : null,
            'jsMinifier' => $options['minifyJs'] ? array('JSMin', 'minify') : null,
            'jsCleanComments' => $options['jsCleanComments'],
        );

        $html = \Minify_HTML::minify($html, $minifierOptions);
        
        return $html;
    }
}