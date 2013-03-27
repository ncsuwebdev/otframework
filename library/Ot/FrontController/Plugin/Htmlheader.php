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
        
        $hr = new Ot_Layout_HeadRegister();
        
        $registry = new Ot_Config_Register();
        
        foreach ($hr->getCssFiles() as $position => $scripts) {
            foreach ($scripts as $s) {
                
                if (!preg_match('/\/\//', $s)) {
                    $s = $baseUrl . '/' . $s;
                }
                
                if ($position == 'append') {
                    $view->headLink()->appendStylesheet($s);
                } else {
                    $view->headLink()->prependStylesheet($s);
                }
            }
        }
        
        foreach ($hr->getJsFiles() as $position => $scripts) {
            foreach ($scripts as $s) {
                
                if (!preg_match('/\/\//', $s)) {
                    $s = $baseUrl . '/' . $s;
                }
                
                if ($position == 'append') {
                    $view->headScript()->appendFile($s);
                } else {
                    $view->headScript()->prependFile($s);
                }
            }
        }
        
        $acl    = Zend_Registry::get('acl');
        $auth   = Zend_Auth::getInstance();
        
        $role = (!$auth->hasIdentity()) ? $registry->defaultRole->getValue() : $auth->getIdentity()->role;
        
        if ($acl->isAllowed($role, 'ot_translate', 'index')) {
            $view->headScript()->appendFile($baseUrl . '/scripts/ot/translate.js');
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