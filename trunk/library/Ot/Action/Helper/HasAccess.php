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
 * @package    Ot_Action_Helper_HasAccess
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Adds additional features to a title of a page
 *
 * @package    Ot_Action_Helper_HasAccess
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */
class Ot_Action_Helper_HasAccess extends Zend_Controller_Action_Helper_Abstract
{
    protected $_config;
    protected $_identity;
    
    public function init()
    {
        $this->_config = Zend_Registry::get('config');
        $this->_identity = Zend_Auth::getInstance()->getIdentity();
        
        parent::init();
    }
    
    public function hasAccess($privilege, $resource = null, $role = null)
    {
        $acl = Zend_Registry::get('acl');
        
        if (is_null($role)) {
            $role = (empty($this->_identity->role))
                  ? (string)$this->_config->user->defaultRole->val
                  : $this->_identity->role;
        }
        
        if (is_null($resource)) {
            $resource = strtolower($this->getRequest()->module . '_' . $this->getRequest()->controller);  
        }

        return $acl->isAllowed($role, $resource, $privilege);
    }
    
    public function direct($privilege, $resource = null, $role = null)
    {
        return $this->hasAccess($privilege, $resource, $role);
    }
}