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
 * @package    Ot_Plugin_Interface
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Generic plugin interface to extend functionality of certain applications
 *
 * @package    Ot_Plugin_Interface
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */
interface Ot_Plugin_Interface
{
    
    /**
     * Subform to add a new trigger
     *
     * @return Zend_Form element
     */
    public function addSubForm();
    
    /**
     * Action called when the addForm is processed
     *
     * @param array $data
     */
    public function addProcess($data);
    
    /**
     * Subform to edit an existing trigger
     *
     * @param mixed $id
     * @return Zend_Form element
     */
    public function editSubForm($id);
    
    /**
     * Action called when the editForm is processed
     *
     * @param array $data
     */
    public function editProcess($data);
    
    /**
     * Action called when a request is processed to delete a trigger
     *
     * @param mixed $id
     * @return boolean
     */
    public function deleteProcess($id);
    
    /**
     * retrieves trigger with a specific ID
     *
     * @param mixed $id
     * @return Zend_Db_Table_Rowset or null
     */
    public function get($id);
    
    /**
     * Action called when a trigger is executed.
     *
     * @param array $data
     */
    public function dispatch($data);
    
}