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
 * @package    Ot_Role
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Handles ACL Roles
 *
 * @package    Ot_Role
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 * 
 */
class Ot_Role extends Ot_Db_Table
{
    /**
     * Database table name
     *
     * @var string
     */
    public $_name = 'tbl_ot_role';

    /**
     * Primary key for the database
     *
     * @var string
     */
    protected $_primary = 'roleId';
    
    /**
     * Cache key to store ACL results
     *
     */
    const CACHE_KEY = 'Ot_Acl';
    
    /**
     * Gets all roles in the db
     *
     * @return array
     */
    public function getRoles()
    {
    	$cache = Zend_Registry::get('cache');
        
        if (!$aclData = $cache->load(self::CACHE_KEY)) {
            
        	$rule = new Ot_Role_Rule();
    	
    		$roles = $this->fetchAll(null)->toArray();
    		$rules = $rule->fetchAll(null, 'roleId')->toArray();
    	
    		$aclData = array();
    	
    		foreach ($roles as $role) {
    			$role['rules'] = array();
    			foreach ($rules as $key => $rule) {
	    			if ($rule['roleId'] == $role['roleId']) {
	    				$role['rules'][] = $rule;
	    				unset($rules[$key]);
	    			}
	    		}
	    		
	    		$aclData[$role['roleId']] = $role;
    		}
            
            $cache->save($aclData, self::CACHE_KEY);
        }
        
        return $aclData;
    }
    
    /**
     * Inserts a new role into the database and creates the rules for the role.
     *
     * @param array $data
     * @return unknown
     */
    public function insert(array $data)
    {
    	$rules = $data['rules'];
    	unset($data['rules']);
    	
    	$dba = $this->getAdapter();
    	
     	$inTransaction = false;
        
	    try {
            $dba->beginTransaction();
        } catch (Exception $e) {
            $inTransaction = true;
        }
        
        try {
    		$roleId = parent::insert($data);
        } catch (Exception $e) {
			if (!$inTransaction) {
            	$dba->rollBack();
            }
            throw $e;        	
        }
    	
    	$roleRule = new Ot_Role_Rule();
    	
    	foreach ($rules as $rule) {
    		$rule['roleId'] = $roleId;
    		
    		try {
    			$roleRule->insert($rule);
	        } catch (Exception $e) {
				if (!$inTransaction) {
	            	$dba->rollBack();
	            }
	            throw $e;        	
	        }
    	}
    	
    	if (!$inTransaction) {
            $dba->commit();
        }    
        	
        $this->_clearCache();
        
    	return $roleId;
    }
    
    /**
     * Updates an existing role in the database and creates the rules for the role.
     *
     * @param array $data
     * @param string $where
     * @return unknown
     */
    public function update(array $data, $where)
    {
    	$rules = $data['rules'];
    	unset($data['rules']);
    	
    	$dba = $this->getAdapter();
    	
     	$inTransaction = false;
        
	    try {
            $dba->beginTransaction();
        } catch (Exception $e) {
            $inTransaction = true;
        }
        
        try {
    		parent::update($data, $where);
        } catch (Exception $e) {
			if (!$inTransaction) {
            	$dba->rollBack();
            }
            throw $e;        	
        }
    	
    	$roleRule = new Ot_Role_Rule();
    	
    	$where = $dba->quoteInto('roleId = ?', $data['roleId']);
    	try {
    		$roleRule->delete($where);
    	} catch (Exception $e) {
    		if (!$inTransaction) {
    			$dba->rollback();
    		}
    	}
    	
    	foreach ($rules as $rule) {
    		$rule['roleId'] = $data['roleId'];
    		
    		try {
    			$roleRule->insert($rule);
	        } catch (Exception $e) {
				if (!$inTransaction) {
	            	$dba->rollBack();
	            }
	            throw $e;        	
	        }
    	}
    	
    	if (!$inTransaction) {
            $dba->commit();
        }
        
        $this->_clearCache();
    }    
    
    public function delete($where) 
    {
    	parent::delete($where);
    	$this->_clearCache();
    }
    
    /**
     * Updates an existing role in the database and creates the rules for the role.
     *
     * @param array $data
     * @param string $where
     * @return unknown
     */
    public function deleteRole($roleId)
    {
    	$dba = $this->getAdapter();
    	
     	$inTransaction = false;
        
	    try {
            $dba->beginTransaction();
        } catch (Exception $e) {
            $inTransaction = true;
        }
        
        $where = $dba->quoteInto('roleId = ?', $roleId);
        
        try {
    		parent::delete($where);
        } catch (Exception $e) {
			if (!$inTransaction) {
            	$dba->rollBack();
            }
            throw $e;        	
        }
    	
    	$roleRule = new Ot_Role_Rule();
    	
    	try {
    		$roleRule->delete($where);
    	} catch (Exception $e) {
    		if (!$inTransaction) {
    			$dba->rollback();
    		}
    	}
    	
    	if (!$inTransaction) {
            $dba->commit();
        }
        
        $this->_clearCache();
    }    

    /**
     * Clears the cached ACL file
     *
     */
    protected function _clearCache()
    {
    	$cache = Zend_Registry::get('cache');
    	$cache->remove(self::CACHE_KEY);
    }
}