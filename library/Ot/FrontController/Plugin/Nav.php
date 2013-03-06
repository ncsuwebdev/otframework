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
 * @package    Ot_FrontController_Plugin_Nav
 * @category   Front Controller Plugin
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Populates a navigation view variable based on the users credentials and the
 * nav layout as determined by the nav.xml config file
 *
 * @package    Ot_FrontController_Plugin_Nav
 * @category   Front Controller Plugin
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */
class Ot_FrontController_Plugin_Nav extends Zend_Controller_Plugin_Abstract
{
    
    protected $_treeNodes = array();

    /**
     * Pre-dispatch code that builds the navigation array based on the access
     * level of the current user.
     *
     * @param Zend_Controller_Request_Abstract $request
     */
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        $acl     = Zend_Registry::get('acl');
         
        $register = new Ot_Config_Register();

        $identity = Zend_Auth::getInstance()->getIdentity();
        $role = (empty($identity->role)) ? $register->defaultRole->getValue() : $identity->role;
        
        $nav = new Ot_Model_DbTable_Nav();
        
        $navigation = $nav->getNav();
        
        $view = Zend_Layout::getMvcInstance()->getView();
        $view->navigation()->setContainer($navigation);
        $view->navigation()->setAcl($acl);
        $view->navigation()->setRole($role);
        $view->navigation()->setUseAcl(true);        
    } 
}