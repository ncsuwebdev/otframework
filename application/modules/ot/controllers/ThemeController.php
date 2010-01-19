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
 * @package    Ot_ConfigController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    BSD License
 * @version    SVN: $Id: $
 */

/**
 * Allows the user to change the theme
 *
 * @package    Ot_ThemeController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */
class Ot_ThemeController extends Zend_Controller_Action
{   
    /**
     * Shows all available themes
     */
    public function indexAction()
    {
        $this->_helper->pageTitle('ot-theme-index:title');
        
        /* Note: Must load themes from /public/themes/ot and /public/themes
         * seperately because scandir does not include paths in array
         */
        $themes = array();
        
        /* Obtain all directories in the theme folder, add them to the theme
         * array.
         */
        $dirs = array(
           'otThemes'  => 'public/themes/ot/',
           'appThemes' => 'public/themes/',
        );
        
        foreach ($dirs as $dir) {
            
            $dirPath = APPLICATION_PATH . '/../' . $dir;

            $themeDirs = scandir($dirPath);
        
            foreach ($themeDirs as $theme) {
                
                $path = $dirPath . $theme;
                
                /* Keep only the directories that are themes (criteria being
                 * that they contain a config.xml); load name and description
                 * into the array
                 */
                if (file_exists($path . '/config.xml')) {
                            
                    $themes[$theme]["path"] = $path;
                    $themes[$theme]["url"] = $dir . $theme;
    
                    $xml = simplexml_load_file($path . '/config.xml');
                    $themes[$theme]["name"]        = trim((string)$xml->production
                                                                      ->theme
                                                                      ->name);
                    $themes[$theme]["description"] = trim((string)$xml->production
                                                                      ->theme
                                                                      ->description);
                }
            }
        }  

        $this->view->messages = $this->_helper->flashMessenger->getMessages();
        $this->view->themes = $themes;
        $config = Zend_Registry::get('config');
        $this->view->currentTheme = $config->app->theme;
    }
    
    public function selectAction()
    {
        $get = Zend_Registry::get('getFilter');
                    
        $newTheme = 'default';
        if (isset($get->theme)) {
            $newTheme = strtolower($get->theme);
        }
        
        $themes = array();
            
        // Obtain all directories in the theme folder, add them to the array
        $dirs = array(
           'otThemes'  => 'public/themes/ot/',
           'appThemes' => 'public/themes/',
        );
        
        foreach ($dirs as $dir) {
            
            $dirPath = APPLICATION_PATH . '/../' . $dir;
    
            $themeDirs = scandir($dirPath);
        
            foreach ($themeDirs as $theme) {
                
                $path = $dirPath . $theme;
                
                /* Keep only the directories that are themes (criteria being
                 * that they contain a config.xml); load name and description
                 * into the array
                 */
                if (file_exists($path . '/config.xml')) {
                            
                    $themes[$theme]["path"]        = $path;
                    $themes[$theme]["url"]         = $dir . $theme;
    
                    $xml = simplexml_load_file($path . '/config.xml');
                    $themes[$theme]["name"]        = trim((string)$xml->production
                                                                      ->theme
                                                                      ->name);
                    $themes[$theme]["description"] = trim((string)$xml->production
                                                                      ->theme
                                                                      ->description);
                }
            }
        }  
        
        if (!isset($themes[$newTheme])) {
                $newTheme = 'default';
        }
        
        $overrideFile = APPLICATION_PATH . '/../overrides/config/config.xml';
    
        if (!file_exists($overrideFile)) {
            throw new Ot_Exception_Data("msg-error-configFileNotFound");
        }
        
        if (!is_writable($overrideFile)) {
            throw new Ot_Exception_Data($this->view
                                             ->translate('msg-error-configFileNotWritable', $overrideFile));
        }
        
        $xml = simplexml_load_file($overrideFile);
    
            if (!isset($xml->production->app->theme)) {
                    $xml->production->app->addChild("theme");
                    $xml->production->app->theme = $newTheme;
            } else {
                    $xml->production->app->theme = $newTheme;
            }
    
            if (!file_put_contents($overrideFile, $xml->asXml(), LOCK_EX)) {
            throw new Ot_Exception_Data("msg-error-savingConfig");
        }
    
        $this->_helper->viewRenderer->setNeverRender(true);
        $this->_helper->layout->disableLayout();
    
        $this->_helper
             ->flashMessenger
             ->addMessage($this->view
                               ->translate('ot-theme-select:changeSuccess'));
        $this->_helper
             ->redirector
             ->gotoRoute(array('controller' => 'theme'), 'ot', true);
    }

}