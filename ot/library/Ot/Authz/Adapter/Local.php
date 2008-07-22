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
 * @package    Ot_Authz_Adapter_Local
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * This is a plugin for authorizing off of a local table(s) that is
 * located in the main database of the application.  The plugin is built off of
 * the standard Authorization plugin interface, found in the library of this
 * application.
 *
 * This version of DbAuth requires that "dbAdapter" be set with the PDO database
 * adapter in the Zend registry, however if a different database is required, a new
 * adapter can be setup in the constructor and assigned to the $_db class variable.
 *
 * @package    Ot_Authz_Adapter_Local
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 */
class Ot_Authz_Adapter_Local implements Ot_Authz_Interface, Ot_Authz_Adapter_Interface
{

    /**
     * Identity object returned from Zend_Auth
     *
     * @var mixed
     */
    protected $_identity = null;

    /**
     * Database adapter
     *
     * @var Object
     */
    protected $_db = null;

    /**
     * Authz table
     *
     * @var string
     */
    protected $_name = 'tbl_ot_authz_local';

    /**
     * Constructor
     *
     * @param mixed $identity
     */
    public function __construct($identity)
    {
        $this->_db = Zend_Registry::get('dbAdapter');
        $this->_identity = $identity;
    }
    
    /**
     * Given a userId, gets all roles associated with that userId
     *
     * @param string $userId
     * @throws exception if 'dbAdapter' is not registered
     * @return array of roles
     */
    public function authorize()
    {
        $select = $this->_db->select();

        $select->from($this->_name)
               ->where('userId = ?', Zend_Auth::getInstance()->getIdentity());

        try {
            $stmt = $this->_db->query($select);
            $result = $stmt->fetchAll();
        } catch (Exception $e) {
            return new Ot_Authz_Result(false, null, array($e->getMessage()));
        }

        if (count($result) == 0) {
            return new Ot_Authz_Result(true, null, array());
        } else {
            return new Ot_Authz_Result(true, $result[0]['role'], array());
        }
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

    public function addUser($userId, $role)
    {
        $data = array(
            'userId' => $userId,
            'role'   => $role,
            );

         return $this->_db->insert($this->_name, $data);
    }

    public function editUser($userId, $role)
    {
        $data = array(
            'role' => $role
            );

        $where = $this->_db->quoteInto('userId = ?', $userId);

        return $this->_db->update($this->_name, $data, $where);
    }

    public function deleteUser($userId)
    {
        $where = $this->_db->quoteInto('userId = ?', $userId);

        return $this->_db->delete($this->_name, $where);
    }

    public function getUsers($role = 'all')
    {
        $select = $this->_db->select();
        $select->from($this->_name, '*');

        if ($role != 'all') {
            $select->where('role = ?', $role);
        }

        $select->order('userId');

        return  $this->_db->fetchAll($select);
    }

    public function getUser($userId)
    {    	
        $select = $this->_db->select();
        $select->from($this->_name, '*');
        $select->where('userId = ?', $userId);

        $result = $this->_db->fetchAll($select);

        if (count($result) == 1) {
            return $result[0];
        }

        throw new Exception('User was not found');
    }

}