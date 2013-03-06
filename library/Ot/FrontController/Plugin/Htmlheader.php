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
        
        $themeConfig = new Zend_Config_Xml(realpath(APPLICATION_PATH . '/../public/' . $view->applicationThemePath) . '/config.xml', 'production', true);
        
        $registry = new Ot_Config_Register();
        
        // $useMinify decides whether to minify css, js, etc. if you don't want to minify, it creates new
        //   <link> tags for each item instead of grouping them into a single file
        $useMinify = $registry->useMinify->getValue();
        
        if ($useMinify) {
            $view->minifyHeadLink()->appendStylesheet($baseUrl . '/' . $themePath . '/public/jQueryUI/ui.all.css');
            $view->headLink()->appendStylesheet('//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.0/css/bootstrap.min.css');
            $view->minifyHeadLink()->prependStylesheet($baseUrl . '/css/ot/common.css');
        } else {
            $view->headLink()->appendStylesheet($baseUrl . '/' . $themePath . '/public/jQueryUI/ui.all.css');
            $view->headLink()->appendStylesheet('//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.0/css/bootstrap.min.css');
            $view->headLink()->prependStylesheet($baseUrl . '/css/ot/common.css');
        }                 
        
        if (isset($themeConfig->css->file)) {
            foreach ($themeConfig->css->file as $c) {
                $path = $c->path;
                
                if (!preg_match('/^http/i', $path)) {
                    $path = $baseUrl . '/public/' . $themePath . '/public/css/' . $path;
                }
                
                if ($c->order == 'append') {
                    if($useMinify) {
                        $view->minifyHeadLink()->appendStylesheet($path);
                    } else {
                        $view->headLink()->appendStylesheet($path);
                    }
                } elseif ($c->order == 'prepend') {
                    if ($useMinify) {
                        $view->minifyHeadLink()->prependStylesheet($path);
                    } else {
                        $view->headLink()->prependStylesheet($path);
                    }
                }
            }
        }
        
        if($useMinify) {
            $view->minifyHeadScript()->appendFile('//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js');
            $view->minifyHeadScript()->appendFile('//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.0/js/bootstrap.min.js');
            $view->minifyHeadScript()->appendFile('//ajax.googleapis.com/ajax/libs/jqueryui/1.10.1/jquery-ui.min.js');
            $view->minifyHeadScript()->appendFile($baseUrl . '/public/scripts/ot/global.js');
        } else {
            $view->headScript()->appendFile('//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js');
            $view->headScript()->appendFile('//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.0/js/bootstrap.min.js');
            $view->headScript()->appendFile('//ajax.googleapis.com/ajax/libs/jqueryui/1.10.1/jquery-ui.min.js');
            $view->headScript()->appendFile($baseUrl . '/public/scripts/ot/global.js');
        }
        
        if (isset($themeConfig->scripts->file)) {
            foreach ($themeConfig->scripts->file as $s) {
                $path = $s->path;
                
                if (!preg_match('/^http/i', $path)) {
                    $path = $baseUrl . '/public/' . $themePath . '/public/scripts/' . $path;
                }
                
                if ($s->order == 'append') {
                    if($useMinify) {
                        $view->minifyHeadScript()->appendFile($path);
                    } else {
                        $view->headScript()->appendFile($path);
                    }
                } elseif ($s->order == 'prepend') {
                    if($useMinify) {
                        $view->minifyHeadScript()->prependFile($path);
                    } else {
                        $view->headScript()->prependFile($path);
                    }
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
        
        // Check application directories and append to existing array
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