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
 * @package    Ot_FrontController_Plugin_TextSubstitution
 * @category   Front Controller Plugin
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Allows for text substitution to be done preDispatch and assigned vars to
 * the view for inclusion in the view script.
 *
 * @package    Ot_FrontController_Plugin_TextSubstitution
 * @category   Front Controller Plugin
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 */
class Ot_FrontController_Plugin_TextSubstitution extends Zend_Controller_Plugin_Abstract
{

    /**
     * Pre-dispatch code that builds the navigation array based on the access
     * level of the current user.
     *
     * @param Zend_Controller_Request_Abstract $request
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    { 
        $configFiles = Zend_Registry::get('configFiles');
        $textSubFile = $configFiles['textSubstitution'];
        $textSub = new Zend_Config_Xml($textSubFile, 'production');

        $module     = $request->module;
        $controller = $request->controller;
        $action     = $request->action;

        $resource = strtolower($module . '_' . $controller);    
        
        $vr = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        $view = $vr->view;
        
        $subs = array();        
        if (isset($textSub->$resource->$action) && $textSub->$resource->$action instanceof Zend_Config) {
            
        	$subs = $textSub->$resource->$action->toArray();
        	
        	foreach ($subs as &$s) {
        	    $tmp = html_entity_decode($s);
        	    $s = $tmp;
        	}
        	
        }
        
        $rootSubs = array();
        if (isset($textSub->root->footer) && $textSub->root->footer instanceof Zend_Config) {
                
            $root = $textSub->root;

            foreach ($root as $section) {
                if (isset($section) && $section instanceof Zend_Config) {
                    $s = $section->toArray();    
                    foreach ($s as $key => $value) {
                        $rootSubs[$key] = html_entity_decode($value);
                    }
                }
            }
        }
        
        $view->textSub = array_merge($subs, $rootSubs);
    }
}