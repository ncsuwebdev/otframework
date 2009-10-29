<?php
class Ot_Application_Resource_Theme extends Zend_Application_Resource_ResourceAbstract
{      
    public function init()
    {
        $bootstrap = $this->getBootstrap();
        $layout = $bootstrap->getResource('layout');
        $view = $layout->getView();
        
        $config = Zend_Registry::get('config');
        $view->config = $config;
                            
        $theme = ($config->user->customTheme->val != '') ? $config->user->customTheme->val : 'default';
        
        $appBasePath = APPLICATION_PATH . "/../public/themes";
        $otBasePath = APPLICATION_PATH . "/../public/ot/themes";
        
        if (!is_dir($appBasePath . "/" . $theme)) {
            if (!is_dir($otBasePath . '/' . $theme)) {
                $theme = 'default';
            }
            
            $themePath = $otBasePath . '/' . $theme;
            
        } else {
            $themePath = $appBasePath . '/' . $theme;
        }
        
        $layout->setLayoutPath($themePath . '/views/layouts');
              
        $view->applicationThemePath = str_replace(APPLICATION_PATH . "/../public/", "", $themePath);

        $view->addScriptPath(array(
                                $themePath . '/views/scripts/'
                            ))
             ->addHelperPath(array(
                                $themePath . '/views/helpers/'
                            ));
                                    
    }
}