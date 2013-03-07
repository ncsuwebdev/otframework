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
 * @package    Ot_Auth_Adapter_Local
 * @category   Authentication Adapter
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * This adapter uses a local database to authenticate a user.  This version of DbAuth
 * requires that "dbAdapter" be set with the PDO database adapter in the Zend
 * registry, however if a different database is required, a new adapter can be setup
 * in the constructor and assigned to the $_db class variable.
 *
 * @package    Ot_Auth_Adapter_Local
 * @category   Authentication Adapter
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */
class Ot_Auth_Adapter_Local implements Zend_Auth_Adapter_Interface, Ot_Auth_Adapter_Interface
{

    /**
     * Username of the user to authenticate
     *
     * @var string
     */
    protected $_username = '';

    /**
     * Password of the user to authenticate
     *
     * @var string
     */
    protected $_password = '';

    /**
     * Database adapter
     *
     * @var object
     */
    protected $_db = null;
    
    protected $_name = 'tbl_ot_account';

    /**
     * Constructor to create new object
     *
     * @param string $username
     * @param string $password
     */
    public function __construct($username = '', $password = '')
    {
        global $application;

        $prefix = $application->getOption('tablePrefix');

        if (!empty($prefix)) {
            $this->_name = $prefix . $this->_name;
        }
        
        $this->_username = $username;
        $this->_password = $password;
    }

    /**
     * Authenticates the user passed by the constructor.
     *
     * @return new Zend_Auth_Result object
     */
    public function authenticate()
    {
        $account = new Ot_Model_DbTable_Account();

        $result = $account->getByUsername($this->_username, 'local');

        if (is_null($result)) {
            return new Zend_Auth_Result(false, null, array('User "' . $this->_username . '" account was not found.'));
        }
        
        if (md5($this->_password) != $result->password) {
            return new Zend_Auth_Result(false, null, array('The password you entered was invalid.'));
        }

        $class = new stdClass();
        $class->username = $this->_username;
        $class->realm    = 'local';
        
        return new Zend_Auth_Result(true, $class, array());
    }

    /**
     * Sets the autologin to false so that it uses native login mechanism
     *
     * @return boolean
     */
    public static function autoLogin()
    {
        return false;
    }

    /**
     * Does nothing on logout since Zend_Auth handles it all
     *
     */
    public static function autoLogout()
    {
        Zend_Auth::getInstance()->clearIdentity();
    }

    /**
     * Flag to tell the app where the authenticaiton is managed
     *
     * @return boolean
     */
    public static function manageLocally()
    {
        return true;
    }

    /**
     * flag to tell the app whether a user can sign up or not
     *
     * @return boolean
     */    
    public static function allowUserSignUp()
    {
        return true;
    }    

}