<?php
/**
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file _LICENSE.txt.
 *
 * This license is also available via the world-wide-web at
 * http://itdapps.ncsu.edu/bsd.txt
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to itappdev@ncsu.edu so we can send you a copy immediately.
 *
 * @package    Ot_Application_Resource_Remedy
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    BSD License
 * @version    SVN: $Id: $
 */

/**
 *
 *
 * @package   Ot_Application_Resource_Remedy
 * @category  Library
 * @copyright Copyright (c) 2007 NC State University Office of Information Technology
 *
 */

class Ot_Application_Resource_Remedy extends Zend_Application_Resource_ResourceAbstract
{
    protected $_hostname = '';

    protected $_username = '';
    
    protected $_password = '';
    
    public function setHostname($val)
    {
        $this->_hostname = $val;
    }
    
    public function setUsername($val)
    {
        $this->_username = $val;
    }
    
    public function setPassword($val)
    {
        $this->_password = $val;
    }
    
    public function init()
    {
        $remedyConfig = array(
            'hostname' => $this->_hostname,
            'username' => $this->_username,
            'password' => $this->_password,
        );
        
        Zend_Registry::set('remedyConfig', $remedyConfig);
    }
}