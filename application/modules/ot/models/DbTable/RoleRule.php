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
 * @package    Ot_Role_Rule
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Handles ACL Roles
 *
 * @package    Ot_Role_Rule
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * 
 */
class Ot_Model_DbTable_RoleRule extends Ot_Db_Table
{
    /**
     * Database table name
     *
     * @var string
     */
    protected $_name = 'tbl_ot_role_rule';

    /**
     * Primary key for the database
     *
     * @var string
     */
    protected $_primary = 'ruleId';
    
    public function getRulesForRole($roleId)
    {
        $where = $this->getAdapter()->quoteInto('roleId = ?', $roleId);
        
        return $this->fetchAll($where, 'type');
    }
}