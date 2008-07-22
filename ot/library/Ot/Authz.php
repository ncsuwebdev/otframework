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
 * @package    Ot_Authz
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 * @license    BSD License
 * @version    SVN: $Id: $
 */

/**
 * Ot_Authz is patterned after Zend Framework's Zend_Auth.  While Zend_Auth handles
 * all aspects of Authentication, Ot_Authz does the same thing from Authorization.
 *
 * Built on the basis that an authentication source and an authorization source can
 * come from a different place, this module allows application writers to create
 * custom modules to do authorization specific to their application.
 *
 * @package    OT_Authz
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Information Technology Division
 */
class Ot_Authz
{
    /**
     * Singleton instance
     *
     * @var Itdcs_Authz
     */
    protected static $_instance = null;

    /**
     * Storage for the adapter
     *
     * @var unknown_type
     */
    protected $_storage = null;

    protected $_sessionName = 'Ot_Authz';
    
    /**
     * Singleton pattern implementation makes "new" unavailable
     *
     * @return void
     */
    private function __construct()
    {}

    /**
     * Singleton pattern implementation makes "clone" unavailable
     *
     * @return void
     */
    private function __clone()
    {}

    /**
     * Returns an instance of Zend_Auth
     *
     * Singleton pattern implementation
     *
     * @return Zend_Auth Provides a fluent interface
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Authenticates against the supplied adapter
     *
     * @param  Itdcs_Authz_Adapter_Interface $adapter
     * @return Itdcs_Authz_Result
     */
    public function authorize(Ot_Authz_Interface $adapter)
    {
        $result = $adapter->authorize();
        
        $this->getStorage()->write($result->getRole());

        return $result;

    }

    /**
     * Returns the persistent storage handler
     *
     * Session storage is used by default unless a different storage adapter has been set.
     *
     * @return Zend_Auth_Storage_Interface
     */
    public function getStorage()
    {
        if (null === $this->_storage) {
            /**
             * @see Zend_Auth_Storage_Session
             */
            require_once 'Ot/Auth/Storage/Session.php';
            
            $sessionSpace = 'authz';
                
            $this->setStorage(new Ot_Auth_Storage_Session($sessionSpace));
        }

        return $this->_storage;
    }

    /**
     * Sets the persistent storage handler
     *
     * @param  Zend_Auth_Storage_Interface $storage
     * @return Zend_Auth Provides a fluent interface
     */
    public function setStorage(Zend_Auth_Storage_Interface $storage)
    {
        $this->_storage = $storage;
        return $this;
    }

    /**
     * Returns true if and only if an identity is available from storage
     *
     * @return boolean
     */
    public function hasRole()
    {
        return !$this->getStorage()->isEmpty();
    }

    /**
     * Returns the identity from storage or null if no identity is available
     *
     * @return mixed|null
     */
    public function getRole()
    {
        $storage = $this->getStorage();

        if ($storage->isEmpty()) {
            return null;
        }

        return $storage->read();
    }

    /**
     * Clears the identity from persistent storage
     *
     * @return void
     */
    public function clearRole()
    {
        $this->getStorage()->clear();
    }
    
    public function overrideRole($role)
    {
    	$this->getStorage()->write($role);
    }
}