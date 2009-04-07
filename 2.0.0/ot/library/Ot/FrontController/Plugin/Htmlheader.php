<?php
/**
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * This license is also available via the world-wide-web at
 * http://itdapps.ncsu.edu/bsd.txt
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to itappdev@ncsu.edu so we can send you a copy immediately.
 *
 * @package    Ot_FrontController_Plugin_Htmlheader
 * @category   Front Controller Plugin
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Allows the application to automatically load CSS and Javascript files that are
 * associated with the dispatched module, controller and action.
 *
 * @package    Ot_FrontController_Plugin_Htmlheader
 * @category   Front Controller Plugin
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 */
class Ot_FrontController_Plugin_Htmlheader extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $view = Zend_Layout::getMvcInstance()->getView();
        $config = Zend_Registry::get('config');
                
        $baseUrl = $view->baseUrl();
        
        $commonStylesheets = array(
                                'http://ajax.googleapis.com/ajax/libs/yui/2.7.0/build/reset-fonts-grids/reset-fonts-grids.css',
                                'http://ajax.googleapis.com/ajax/libs/yui/2.7.0/build/base/base-min.css',
                                $baseUrl . '/public/css/layout.css',
                                $baseUrl . '/public/ot/css/superfish.css',
                                $baseUrl . '/public/ot/css/superfish-vertical.css',
                                $baseUrl . '/public/css/nav.css',
                                $baseUrl . '/public/ot/css/Ot/common.css'
                             );

        // check config to see if there is a custom theme specified, and if that theme exists
        if ($config->user->customTheme->val != "" && is_dir($config->app->customThemePath . '/' . $config->user->customTheme->val)) {                             
            if (substr($config->app->customThemePath, 0, 1) == ".") {
                $path = substr($config->app->customThemePath, 1, strlen($config->app->customThemePath));
            }
            $commonStylesheets[] = $baseUrl . $path . '/' . $config->user->customTheme->val . '/ui.all.css';
        } else {
            $commonStylesheets[] = $baseUrl . '/public/ot/themes/base/ui.all.css';
        }
                                
        foreach ($commonStylesheets as $s) {
            $view->headLink()->appendStylesheet($s);
        }

        $commonScriptFiles = array(
                                'http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js',
                                'http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.1/jquery-ui.min.js',
                                $baseUrl . '/public/ot/scripts/jquery.plugin.hoverIntent.js',
                                $baseUrl . '/public/ot/scripts/jquery.plugin.supersubs.js',
                                $baseUrl . '/public/ot/scripts/jquery.plugin.superfish.js',
                                $baseUrl . '/public/scripts/global.js'
                             );
        
        foreach ($commonScriptFiles as $s) {
            $view->headScript()->appendFile($s);
        }
    }
    
    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        $view = Zend_Layout::getMvcInstance()->getView();
            	
        $baseUrl = $view->baseUrl();
        
        $css        = array();
        $javascript = array();
        
        // check application directories and append to existing array
        $javascript = $this->_autoload($baseUrl, './public/scripts', 'js', $request, $javascript);   
        $css        = $this->_autoload($baseUrl, './public/css', 'css', $request, $css);
        
        // check OT directories and append to existing array
        $javascript = $this->_autoload($baseUrl, './public/ot/scripts', 'js', $request, $javascript);
        $css        = $this->_autoload($baseUrl, './public/ot/css', 'css', $request, $css);
        
        foreach ($css as $c) {
            $view->headLink()->appendStylesheet($c);
        }
        
        $view->headLink()->appendStylesheet($baseUrl . '/public/css/overrides.css');
        
        foreach ($javascript as $j) {
            $view->headScript()->appendFile($j);
        }       
    }
    
    protected function _autoload($baseUrl, $directory, $extension, $request, $existing)
    {
        $req = array(
            'module'     => $request->getModuleName(),
            'controller' => $request->getControllerName(),
            'action'     => strtolower($request->getActionName()),
        );

        $path = '';
        
        foreach ($req as $r) {
            $path .= (($path == '') ? '' : '/') . $r; 
            
            $autoload = $path . '.' . $extension;
        
	        if (is_file($directory . '/' . $autoload)) {
	        	
	        	$file = str_replace('./', $baseUrl . '/', $directory . '/' . $autoload);
	        	
	             if (is_array($existing)) {
	                array_push($existing, $file);        
	             } else {
	                if ($existing != '') {
	                    $existing = array($existing, $file);
	                } else {
	                    $existing = array($file);
	                }
	             }
	        }
        }
        
        return $existing;     	
    }
}