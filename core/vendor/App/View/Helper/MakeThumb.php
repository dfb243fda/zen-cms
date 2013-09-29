<?php

namespace App\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use App\Resize\Resize;

class MakeThumb extends AbstractHelper implements ServiceLocatorAwareInterface
{
    protected $serviceLocator;
    
    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
    
    public function __invoke($imgPath, $width, $height, $resizeMethod = 'auto')
    {
        if (!is_file($imgPath)) {
            return $imgPath;
        }
        
        $baseName = basename($imgPath);
        
        $newImgPath = PUBLIC_PATH . '/img/thumb/' . md5($imgPath) . '/' . $baseName;
        $newImgUrl =  ROOT_URL_SEGMENT . '/img/thumb/' . md5($imgPath) . '/' . $baseName;
        
        if (!file_exists($newImgPath)) {
            $dir = PUBLIC_PATH . '/img/thumb/' . md5($imgPath);
            if (!is_dir($dir)) {
                $fileManager = $this->serviceLocator->getServiceLocator()->get('FileManager');            
                $fileManager->mkdir($dir, true);
            }            
            
            $resize = new Resize($imgPath);
            $resize->resizeImage($width, $height, $resizeMethod);
   
            $resize->saveImage($newImgPath);
        }
        
        return $newImgUrl;
    }
}
