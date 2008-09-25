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
    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        $vr = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
               
        if (empty($vr->view) || $vr->getNeverRender()) {
            return;
        }

        $view     = $vr->view;
            	
        $sitePrefix = Zend_Registry::get('sitePrefix');
        
        $existingJavascript = (isset($view->javascript)) ? ((is_array($view->javascript)) ? $view->javascript : array($view->javascript)) : array();
        $existingCss        = (isset($view->css)) ? ((is_array($view->css)) ? $view->css : array($view->css)) : array();
        
        foreach ($existingJavascript as &$j) {
        	if (is_file('./public/ot/scripts/' . $j)) {
        		$j = $sitePrefix . '/public/ot/scripts/' . $j;
        	} elseif (is_file('./public/scripts/' . $j)) {
        		$j = $sitePrefix . '/public/scripts/' . $j;
        	}
        }
        
        foreach ($existingCss as &$c) {
            if (is_file('./public/ot/css/' . $c)) {
                $c = $sitePrefix . '/public/ot/css/' . $c;
            } elseif (is_file('./public/css/' . $c)) {
                $c = $sitePrefix . '/public/css/' . $c;
            }
        }        
        
        $existingJavascript = $this->_autoload('./public/scripts', 'js', $request, $existingJavascript);
        $existingCss        = $this->_autoload('./public/css', 'css', $request, $existingCss);
        
        $view->javascript = $this->_autoload('./public/ot/scripts', 'js', $request, $existingJavascript);
        $view->css        = $this->_autoload('./public/ot/css', 'css', $request, $existingCss);
                              
    }
    
    protected function _autoload($directory, $extension, $request, $existing)
    {
        $req = array(
            'module' => $request->getModuleName(),
            'controller' => $request->getControllerName(),
            'action'     => strtolower($request->getActionName()),
        );

        $path = '';
        
        $sitePrefix = Zend_Registry::get('sitePrefix');
        
        foreach ($req as $r) {
            $path .= (($path == '') ? '' : '/') . $r; 
            
            $autoload = $path . '.' . $extension;
        
	        if (is_file($directory . '/' . $autoload)) {
	        	
	        	$file = str_replace('./', $sitePrefix . '/', $directory . '/' . $autoload);
	        	
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