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
 * @package    Internal_Controller_Action
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Main controller to extend for all other controllers
 *
 * @package    Internal_Controller_Action
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 */
class Internal_Controller_Action extends Zend_Controller_Action 
{
	protected $_acl = null;
	
	protected $_role = null;
	
	protected $_resource = null;
	
	protected $_logger = null;
	
	public function init()
	{
        $zcf = Zend_Controller_Front::getInstance();
        
        $filterOptions = array(
            '*' => array(
                'StringTrim',
                'StripTags',
            ),
        );
        
        $getFilter = new Zend_Filter_Input($filterOptions, array(), $_GET);
        Zend_Registry::set('getFilter', $getFilter);
        
        // To easily access the user config options, we break the array down to 
        // simpler terms and register it in the view
        $uc = Zend_Registry::get('userConfig')->toArray();
        $config = array();
        foreach ($uc as $key => $value) {
        	$config[$key] = $value['value'];
        }
        $this->view->config = $config;
        
        $this->view->copyrightDate = date('Y');
        
        $layout = Zend_Layout::getMvcInstance();
        $layout->nav    = $this->view->render('nav.tpl');
        $layout->footer = $this->view->render('footer.tpl');
        
        $config = Zend_Registry::get('appConfig');
        
        $this->_acl      = Zend_Registry::get('acl');
        $this->_role     = (is_null(Ot_Authz::getInstance()->getRole()) ? (string)$config->loginOptions->defaultRole : Ot_Authz::getInstance()->getRole());
        $this->_resource = strtolower($zcf->getRequest()->module . '_' . $zcf->getRequest()->controller);

        $this->_logger = Zend_Registry::get('logger');		
        
	    if (Zend_Auth::getInstance()->hasIdentity()) {
	    	$user = explode('@', Zend_Auth::getInstance()->getIdentity());
	    		    	
	    	$this->view->loggedInUserId = Zend_Auth::getInstance()->getIdentity();
            $this->view->loggedInUser = $user[0];
            $this->view->loggedInRealm = $config->authentication->{$user[1]}->name; 
            $this->view->authManageLocally = call_user_func(array($config->authentication->{$user[1]}->class, 'manageLocally'));
            
            if ((bool)$config->loginOptions->generateAccountOnFirstLogin) {
            	$this->view->myAccount = true;
            }
        }   

        $layout->auth = $this->view->render('auth.tpl');        
	}
}