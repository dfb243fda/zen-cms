<?php

class ComposerAutoloaderInit
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        spl_autoload_register(array('ComposerAutoloaderInit', 'loadClassLoader'));
        self::$loader = $loader = new \Composer\Autoload\ClassLoader();
        spl_autoload_unregister(array('ComposerAutoloaderInit', 'loadClassLoader'));

        $vendorDir = dirname(__DIR__);
        $baseDir = dirname($vendorDir);

        $map = require __DIR__ . '/autoload_namespaces.php';
        foreach ($map as $namespace => $path) {
            $loader->add($namespace, $path);
        }

        $classMap = require __DIR__ . '/autoload_classmap.php';
        if ($classMap) {
            $loader->addClassMap($classMap);
        }

        $loader->register(true);
        
//        require $vendorDir . '/ZF2/library/Zend/Stdlib/compatibility/autoload.php';
//        require $vendorDir . '/ZF2/library/Zend/Session/compatibility/autoload.php';
//        require $vendorDir . '/meenie/javascript-packer/class.JavaScriptPacker.php';
//        require $vendorDir . '/meenie/munee/config/bootstrap.php';

        return $loader;
    }
}
