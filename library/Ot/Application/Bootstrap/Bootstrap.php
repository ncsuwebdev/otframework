<?php

class Ot_Application_Bootstrap_Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{        
    public function __construct($application)
    {
        parent::__construct($application);
        
        require_once 'Zend/Loader/Autoloader.php';
        $loader = Zend_Loader_Autoloader::getInstance();
        
        $loaders = $loader->getAutoloaders();
        
        foreach ($loaders as $l) {
            $l->addResourceType('cronjob', 'cronjobs/', 'Cronjob');
            $l->addResourceType('apiendpoint', 'apiendpoints/', 'Apiendpoint');
        }
        
        $loader->setFallbackAutoloader(true);
    }
}
