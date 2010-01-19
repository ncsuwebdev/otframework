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
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Allows the application to automatically load CSS and Javascript files that are
 * associated with the dispatched module, controller and action.
 *
 * @package    Ot_FrontController_Plugin_Htmlheader
 * @category   Front Controller Plugin
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */
class Ot_FrontController_Plugin_Htmlheader extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $view = Zend_Layout::getMvcInstance()->getView();

        $baseUrl = $view->baseUrl();
        
        $themePath = $view->applicationThemePath;
                
        $themeConfig = new Zend_Config_Xml($themePath . '/config.xml', 'production', true);
        
        $view->minifyHeadLink()->appendStylesheet($baseUrl . '/public/css/ot/common.css');
        $view->minifyHeadLink()->appendStylesheet($baseUrl . '/public/' . $themePath . '/public/jQueryUI/ui.all.css');
        
        if (isset($themeConfig->css->file)) {
            foreach ($themeConfig->css->file as $c) {
                $path = $c->path;
                
                if (!preg_match('/^http/i', $path)) {
                    $path = $baseUrl . '/public/' . $themePath . '/public/css/' . $path;
                }
                
                if ($c->order == 'append') {
                    $view->minifyHeadLink()->appendStylesheet($path);
                } elseif ($c->order == 'prepend') {
                    $view->minifyHeadLink()->prependStylesheet($path);
                }
            }
        }
        
        $view->minifyHeadScript()->appendFile($baseUrl . '/public/scripts/ot/jquery.min.js');
        $view->minifyHeadScript()->appendFile($baseUrl . '/public/scripts/ot/jquery-ui.min.js');
        $view->minifyHeadScript()->appendFile($baseUrl . '/public/scripts/ot/global.js');
                
        if (isset($themeConfig->scripts->file)) {
            foreach ($themeConfig->scripts->file as $s) {
                $path = $s->path;
                
                if (!preg_match('/^http/i', $path)) {
                    $path = $baseUrl . '/public/' . $themePath . '/public/scripts/' . $path;
                }
                
                if ($s->order == 'append') {
                    $view->minifyHeadScript()->appendFile($path);
                } elseif ($s->order == 'prepend') {
                    $view->minifyHeadScript()->prependFile($path);
                }
            }
        }
    }
    
    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        $view = Zend_Layout::getMvcInstance()->getView();
            	
        $baseUrl = $view->baseUrl();
        
        $css        = array();
        $javascript = array();
        
        // check application directories and append to existing array
        $javascript = $this->_autoload($baseUrl, '/public/scripts', 'js', $request, $javascript);   
        $css        = $this->_autoload($baseUrl, '/public/css', 'css', $request, $css);
        
        foreach ($css as $c) {
            $view->headLink()->appendStylesheet($c);
        }
        
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
        
	        if (is_file(APPLICATION_PATH . '/../' . $directory . '/' . $autoload)) {
	            
	             $file = $baseUrl . str_replace('./', $baseUrl . '/', $directory . '/' . $autoload);	             
	        	
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