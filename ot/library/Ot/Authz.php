<?php
/**
 * Itdcs
 *
 * LICENSE
 *
 * This license is governed by United States copyright law, and with respect to matters
 * of tort, contract, and other causes of action it is governed by North Carolina law,
 * without regard to North Carolina choice of law provisions.  The forum for any dispute
 * resolution shall be in Wake County, North Carolina.
 *
 * Redistribution and use in source and binary forms, with or without modification, are
 * permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice, this list
 *    of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice, this
 *    list of conditions and the following disclaimer in the documentation and/or other
 *    materials provided with the distribution.
 *
 * 3. The name of the author may not be used to endorse or promote products derived from
 *    this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR "AS IS" AND ANY EXPRESS OR IMPLIED
 * WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
 * AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE AUTHOR BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF
 * ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    Ot
 * @subpackage Ot_Authz
 * @category   Authorization Interface
 * @copyright  Copyright (c) 2007 NC State University Information Technology Division
 * @license    BSD License
 * @author     Jason Austin <jason_austin@ncsu.edu>
 * @author     Garrison Locke <garrison_locke@ncsu.edu>
 * @see        http://itdapps.ncsu.edu
 * @version    SVN: $Id: Authz.php 17 2007-04-18 13:56:28Z jfaustin@EOS.NCSU.EDU $
 */


/**
 * Ot_Authz is patterned after Zend Framework's Zend_Auth.  While Zend_Auth handles
 * all aspects of Authentication, Ot_Authz does the same thing from Authorization.
 *
 * Built on the basis that an authentication source and an authorization source can
 * come from a different place, this module allows application writers to create
 * custom modules to do authorization specific to their application.
 *
 * @package    Ot
 * @subpackage Ot_Authz
 * @category   Authorization Interface
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