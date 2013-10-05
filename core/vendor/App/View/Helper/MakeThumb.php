<?php

namespace App\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

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
    
    public function __invoke($imgPath, $width, $height, $resizeMode = \Imagine\Image\ImageInterface::THUMBNAIL_INSET)
    {
        if (!is_file($imgPath)) {
            return $imgPath;
        }
        
        $baseName = basename($imgPath);
        
        $newImgPath = PUBLIC_PATH . '/thumb/' . md5($imgPath) . '/' . $baseName;
        $newImgUrl =  ROOT_URL_SEGMENT . '/thumb/' . md5($imgPath) . '/' . $baseName;
        
        if (!file_exists($newImgPath)) {
            $fileManager = $this->serviceLocator->getServiceLocator()->get('FileManager');     
            
            $dir = PUBLIC_PATH . '/thumb/' . md5($imgPath);
            if (!is_dir($dir)) {
                $fileManager->mkdir($dir, true);
            }       
            
            $imagine = new \Imagine\Gd\Imagine();
                        
            $size = new \Imagine\Image\Box($width, $height);
            
            $imagine->open($imgPath)
                ->thumbnail($size, $resizeMode)
                ->save($newImgPath);
            
       /*     
            $resize = new Resize($imgPath, $fileManager);
            $resize->resizeImage($width, $height, $resizeMethod);
   
            $resize->saveImage($newImgPath);
        * 
        */
        }
        
        return $newImgUrl;
    }
}
