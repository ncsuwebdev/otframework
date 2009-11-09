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
 * @package    Ot_Plugin_Auth
 * @category   Front Controller Plugin
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Auth plugin for the Front Controller of the applicaiton.  This plugin looks at
 * the requested module, controller, and action and determines if the logged-in
 * user has access to the action based on the ACL.
 *
 * @package    Ot_Plugin_Auth
 * @category   Front Controller Plugin
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 */
class Ot_FrontController_Plugin_Auth extends Zend_Controller_Plugin_Abstract
{
    /**
     * Arguments for the controller if the user hasn't Authed.
     *
     * @var unknown_type
     */
    private $_noAuth = array('module'     => 'ot',
                             'controller' => 'login',
                             'action'     => 'index');
    
    /**
     * Arguments for the controller if the user doesnt have an account.
     *
     * @var unknown_type
     */
    private $_noAccount = array('module'     => 'ot',
                                'controller' => 'account',
                                'action'     => 'edit');    

    /**
     * Pre-dispatch code that checks with the ACL to see if the loggedin user has
     * access to view the page that is being requested.  If they don't we rewrite
     * the requests controller, module and action to go to a standard "no access"
     * page.
     *
     * @param Zend_Controller_Request_Abstract $request
     */
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        $auth = Zend_Auth::getInstance();
        $acl  = new Ot_Acl();
        
        $view = Zend_Layout::getMvcInstance()->getView();
        $baseUrl = $view->baseUrl();
        
        Zend_Registry::set('acl', $acl);
        
        // Get the requested module, controller, and action
        $module     = $request->module;
        $controller = $request->controller;
        $action     = $request->action;

        $resource = strtolower($module . '_' . $controller);

        if (!$acl->has($resource)) {
            $resource = null;
        }
        
        $config  = Zend_Registry::get('config');
        $role    = (string)$config->user->defaultRole->val;
        $defaultRole = $role;
        
        $account = new Ot_Account();
        $thisAccount = null;
        
        if ($auth->hasIdentity() && $auth->getIdentity() != '' && !is_null($auth->getIdentity())) {
        	
            // We check to see if the adapter allows auto logging in, if it does we do it
            if (call_user_func(array($config->app->authentication->{$auth->getIdentity()->realm}->class, 'autoLogin'))) {

                // Set up the authentication adapter
                $authAdapter = new $config->app->authentication->{$auth->getIdentity()->realm}->class;
            
                // Attempt authentication, saving the result
                $result = $auth->authenticate($authAdapter);
            
                if (!$result->isValid()) {
                    throw new Exception('Error getting login credentials');
                }
            }     
            
            $thisAccount = $account->getAccount($auth->getIdentity()->username, $auth->getIdentity()->realm);
        	
        	if (is_null($thisAccount)) {
        		$auth->clearIdentity();
        		
        		$request->setModuleName($this->_noAuth['module']);
        		$request->setControllerName($this->_noAuth['controller']);
       			$request->setActionName($this->_noAuth['action']); 
       			
       			return;
        	}               	
        	
        	if (!$acl->hasRole($thisAccount->role)) {
        		$thisAccount->role = (string)$config->user->defaultRole->val;
        	}
       			
        	$auth->getStorage()->write($thisAccount);
        	
        	date_default_timezone_set((isset($account->timezone) && $account->timezone != '') ? $account->timezone : date_default_timezone_get());
        		
        	$role = $thisAccount->role;
        }
        
        if ($role == '' || !$acl->hasRole($role)) {
            $role = (string)$config->user->defaultRole->val;
        }
        
        $req = new Zend_Session_Namespace(Zend_Registry::get('siteUrl') . '_request');
        
        if (!$acl->isAllowed($role, $resource, $action) && !is_null($resource) && !$acl->isAllowed($defaultRole, $resource, $action)) {
            if (!$auth->hasIdentity()) {
                $module     = $this->_noAuth['module'];
                $controller = $this->_noAuth['controller'];
                $action     = $this->_noAuth['action'];
                
                $req->uri = str_replace($baseUrl, '', $_SERVER['REQUEST_URI']);
            } else {
                throw new Ot_Exception_Access('You do not have the proper credentials to access this page.');
            }
        }
        
        if ($auth->hasIdentity() && $config->user->requiredAccountFields->val != '') {
        	
        	if (!($request->getModuleName() == 'login' && $request->getControllerName() == 'index' && $request->getActionName() == 'logout')) {
	            
        		$required = explode(',', $config->user->requiredAccountFields->val);
        		
        		$valid = true;
        		foreach ($required as $r) {
        			if (isset($thisAccount->$r) && empty($thisAccount->$r)) {
        				$valid = false;
        				break;
        			}
        		}
        		
	            if (!$valid) {
	            	            	
	                $module     = $this->_noAccount['module'];
	                $controller = $this->_noAccount['controller'];
	                $action     = $this->_noAccount['action'];
	                
	                $req->uri = str_replace($baseUrl, '', $_SERVER['REQUEST_URI']);             
	            }
        	}
        }
        
        $request->setModuleName($module);
        $request->setControllerName($controller);
        $request->setActionName($action);  
    }
}