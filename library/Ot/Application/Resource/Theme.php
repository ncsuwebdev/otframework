<?php
/**
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file _LICENSE.txt.
 *
 * This license is also available via the world-wide-web at
 * http://itdapps.ncsu.edu/bsd.txt
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to itappdev@ncsu.edu so we can send you a copy immediately.
 *
 * @package    Ot_Application_Resource_Theme
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    BSD License
 * @version    SVN: $Id: $
 */

/**
 * 
 *
 * @package   Ot_Application_Resource_Theme
 * @category  Library
 * @copyright Copyright (c) 2007 NC State University Office of Information Technology
 *
 */

class Ot_Application_Resource_Theme extends Zend_Application_Resource_ResourceAbstract
{
    public function init()
    {
        $bootstrap = $this->getBootstrap();
        $layout    = $bootstrap->getResource('layout');
        $view      = $layout->getView();
        
        $config = Zend_Registry::get('config');
        $view->config = $config;
                            
        $theme = ($config->app->theme != '') ? $config->app->theme : 'default';
        
        $appBasePath = APPLICATION_PATH . "/../public/themes";
        $otBasePath = APPLICATION_PATH . "/../public/themes/ot";
        
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

        $view->addScriptPath(array($themePath . '/views/scripts/'))
             ->addHelperPath(array($themePath . '/views/helpers/'));
                                    
    }
}