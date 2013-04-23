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
 * @package    Ot_Db_Table
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Adds additional functionality to Zend_Db_Table
 *
 * @package    Ot_Db_Table
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */
class Ot_Db_Table extends Zend_Db_Table
{
    public function __construct()
    {
        global $application;

        $prefix = $application->getOption('tablePrefix');

        if (!empty($prefix)) {
            $this->_name = $prefix . $this->_name;
        }
        
        // cache the meta data for tables so it doesn't have to get it ever time a table is instantiated
        if (Zend_Registry::isRegistered('cache')) {
            $cache = Zend_Registry::get('cache');
            Zend_Db_Table_Abstract::setDefaultMetadataCache($cache);
        }
        
        parent::__construct();
    }
    
    /**
     * Overwrites the update method to allow the second param ($where clause)
     * to be null, which would then generate the where clause with the values 
     * of the primary keys
     *
     * @param array $data
     * @param mixed $where
     * @return result from update
     */
    public function update(array $data, $where)
    {
        if (is_null($where)) {    
            $primary = (is_array($this->_primary)) ? $this->_primary : array($this->_primary);
              
            foreach ($primary as $key) {
                if (!is_null($where)) {
                    $where .= ' AND ';
                }
                
                if (!isset($data[$key])) {
                    throw new Ot_Exception_Input("Primary key $key not set");
                }
                
                $where .= $this->getAdapter()->quoteInto($key . ' = ?', $data[$key]);
            }
        }

        return parent::update($data, $where);
    }
    
    public function find()
    {
        $args = func_get_args();
        
        $result = call_user_func_array(array('parent', 'find'), $args);
        
        if (count($this->_primary) == 1) {
            if ($result instanceof Zend_Db_Table_Rowset && $result->count() == 1) {
                return $result->current();
            } elseif (is_array($result) && count($result) == 1) {
                return $result[0];
            } elseif (!is_array(func_get_arg(0))) {
                return null;
            }
        }
        return $result;
    }
}