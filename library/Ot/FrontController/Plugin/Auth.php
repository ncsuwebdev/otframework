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
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
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
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
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
        
        $apiRequest = false;
        
        if ($resource == 'ot_api' && $action == 'index') {
            $apiRequest = true;
        }

        $registry = new Ot_Config_Register();

        $role = $registry->defaultRole->getValue();
        $defaultRole = $role;
        
        $account = new Ot_Model_DbTable_Account();
        $thisAccount = null;
        
        if (!$apiRequest && $auth->hasIdentity() && $auth->getIdentity() != '' && !is_null($auth->getIdentity())) {

            $identity = $auth->getIdentity();

            if (!isset($identity->accountId) || is_null($identity->accountId)) {
                $auth->clearIdentity();

                $request->setModuleName($this->_noAuth['module']);
                $request->setControllerName($this->_noAuth['controller']);
                $request->setActionName($this->_noAuth['action']);

                return;
            }

            if (!isset($identity->username) || is_null($identity->username)) {
                $auth->clearIdentity();

                $request->setModuleName($this->_noAuth['module']);
                $request->setControllerName($this->_noAuth['controller']);
                $request->setActionName($this->_noAuth['action']);

                return;
            }

            if (!isset($identity->realm) || is_null($identity->realm)) {
                $auth->clearIdentity();

                $request->setModuleName($this->_noAuth['module']);
                $request->setControllerName($this->_noAuth['controller']);
                $request->setActionName($this->_noAuth['action']);

                return;
            }

            $authAdapter = new Ot_Model_DbTable_AuthAdapter();
            $adapter = $authAdapter->find($auth->getIdentity()->realm);
            $className = (string)$adapter->class;
            
            // We check to see if the adapter allows auto logging in, if it does we do it
            if (call_user_func(array($className, 'autoLogin'))) {

                // Set up the authentication adapter
                $authAdapter = new $className;
            
                // Attempt authentication, saving the result
                $result = $auth->authenticate($authAdapter);
            
                if (!$result->isValid()) {
                    throw new Exception('Error getting login credentials');
                }
            }     
            
            $thisAccount = $account->getByUsername($auth->getIdentity()->username, $auth->getIdentity()->realm);

            $thisAccount->masquerading = false;
            if (isset($identity->masquerading) && $identity->masquerading == true && isset($identity->realAccount) && !is_null($identity->realAccount)) {

                $thisAccount->masquerading = true;
                $thisAccount->realAccount = $identity->realAccount;
                
            }

            if (is_null($thisAccount)) {
                $auth->clearIdentity();
                
                $request->setModuleName($this->_noAuth['module']);
                $request->setControllerName($this->_noAuth['controller']);
                $request->setActionName($this->_noAuth['action']); 
                   
                return;
            }

            if (count($thisAccount->role) > 1) {
                $roles = array();

                // Get role names from the list of role Ids
                foreach ($thisAccount->role as $r) {
                    $roles[] = $acl->getRole($r);
                }

                // Create a new role that inherits from all the returned roles
                $roleName = implode(',', $roles);

                $thisAccount->role = $roleName;

                $acl->addRole(new Zend_Acl_Role($roleName), $roles);


            } else if (count($thisAccount->role) == 1) {
                $thisAccount->role = array_pop($thisAccount->role);
            }
            
            if (!$acl->hasRole($thisAccount->role)) {
                $thisAccount->role = $registry->defaultRole->getValue();
            }
                   
            $auth->getStorage()->write($thisAccount);
            
            date_default_timezone_set(
                (isset($account->timezone) && $account->timezone != '')
                ? $account->timezone : date_default_timezone_get()
            );
                
            $role = $thisAccount->role;            
        }
        
        if ($role == '' || !$acl->hasRole($role)) {
            $role = $registry->defaultRole->getValue();
        }
        
        $requestUri = null;
        
        if (!$acl->isAllowed($role, $resource, $action)
            && !is_null($resource)
            && !$acl->isAllowed($defaultRole, $resource, $action)) {
                
            if (!$auth->hasIdentity()) {
                $module     = $this->_noAuth['module'];
                $controller = $this->_noAuth['controller'];
                $action     = $this->_noAuth['action'];
                
                if (!$request->isXmlHttpRequest()) {
                    $requestUri = str_replace($baseUrl, '', $_SERVER['REQUEST_URI']);
                }
            } else {
                throw new Ot_Exception_Access('You do not have the proper credentials to access this page.');
            }
        }
        
        if ($apiRequest || $request->isXmlHttpRequest()) {
            return;
        }        
        
        if ($auth->hasIdentity() && $registry->requiredAccountFields->getValue() != '') {
            
            if (!($request->getModuleName() == 'ot'
                && $request->getControllerName() == 'login'
                && $request->getActionName() == 'logout')) {
                
                $required = $registry->requiredAccountFields->getValue();
                                
                $valid = true;
                
                if (is_array($required)) {
                    foreach ($required as $r) {
                        if (isset($thisAccount->$r) && empty($thisAccount->$r)) {
                            $valid = false;
                            break;
                        }
                    }
                }
                
                if (!$valid) {
                                    
                    $module     = $this->_noAccount['module'];
                    $controller = $this->_noAccount['controller'];
                    $action     = $this->_noAccount['action'];
                    
                    if (!$request->isXmlHttpRequest()) {
                       $requestUri = str_replace($baseUrl, '', $_SERVER['REQUEST_URI']);
                    }         
                }
            }
        }
        
        if ($auth->hasIdentity() && Zend_Registry::isRegistered('logger')) {
            $logger = Zend_Registry::get('logger');
            $logger->setEventItem('accountId', $auth->getIdentity()->accountId);
            $logger->setEventItem('role', $auth->getIdentity()->role);
        }

        if (!$apiRequest && !is_null($requestUri)) {
            $req = new Zend_Session_Namespace(Zend_Registry::get('siteUrl') . '_request');
            $req->uri = $requestUri;
        }
        
        $request->setModuleName($module);
        $request->setControllerName($controller);
        $request->setActionName($action);  
    }
}