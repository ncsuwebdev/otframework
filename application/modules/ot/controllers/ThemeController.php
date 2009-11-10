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
 * @package    Admin_ConfigController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 * @license    BSD License
 * @version    SVN: $Id: $
 */

/**
 * Allows the user to change the theme
 *
 * @package    Admin_ThemeController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 */
class Ot_ThemeController extends Zend_Controller_Action  
{   
    /**
     * Shows all available themes
     */
    public function indexAction()
    {
        $this->_helper->pageTitle('admin-theme-index:title');
        
        // Note: Must load themes from /public/themes/ot and /public/themes seperately because scandir does not include paths in array
        $themes = array();
        
        // Obtain all directories in the theme folder, add them to the theme array
        $themes = scandir(APPLICATION_PATH . '/../public/themes/ot/');
        foreach ($themes as $theme) {
            $themes[$theme]["path"] = APPLICATION_PATH . '/../public/themes/ot/' . $theme;
            $themes[$theme]["url"] = 'public/themes/ot/' . $theme;
        }
        
        // Keep only the directories that are themes (criteria being that they contain a config.xml); load name and description into the array
        foreach ($themes as $theme => $data) {
           if (!file_exists($data["path"] . '/config.xml')) {
               unset($themes[$theme]);
               continue;
           }
           
           $xml = simplexml_load_file($data["path"] . '/config.xml');
           $themes[$theme]["name"]        = trim((string)$xml->production->theme->name);
           $themes[$theme]["description"] = trim((string)$xml->production->theme->description);
        }

        $this->view->themes = $themes;
        //echo '<pre>'; print_r($themes); die();
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
    	
        // Note: Must load themes from /public/ot/themes and /public/themes seperately because scandir does not include paths in array
        $themes = array();
        
        // Obtain all directories in the theme folder, add them to the theme array
        $themes = scandir(APPLICATION_PATH . '/../public/themes/ot/');
        foreach ($themes as $theme) {
            $themes[$theme]["path"] = APPLICATION_PATH . '/../public/themes/ot/' . $theme;
            $themes[$theme]["url"] = 'public/themes/ot/' . $theme;
        }

        // Keep only the directories that are themes (criteria being that they contain a config.xml); load name and description into the array
        foreach ($themes as $theme => $data) {
           if (!file_exists($data['path'] . '/config.xml')) {
               unset($themes[$theme]);
               continue;
           }
           
           $xml = simplexml_load_file($data["path"] . '/config.xml');
           $themes[$theme]['name']        = trim((string)$xml->production->theme->name);
           $themes[$theme]['description'] = trim((string)$xml->production->theme->description);
        }
        
        if (!isset($themes[$newTheme])) {
        	$newTheme = 'default';
        }
        
        $overrideFile = APPLICATION_PATH . '/overrides/config/config.xml';
        //echo $overrideFile; die();

        if (!file_exists($overrideFile)) {
            throw new Ot_Exception_Data("msg-error-configFileNotFound");
        }
        
        if (!is_writable($overrideFile)) {
        	throw new Ot_Exception_Data($this->view->translate('msg-error-configFileNotWritable', $xml));
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
                
        $this->_helper->flashMessenger->addMessage('Theme changed successfully!');
        $this->_helper->redirector->gotoRoute(array('controller' => 'theme'), 'ot');
        
    }

}