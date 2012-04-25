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
 * @package    Internal_Account_Plugin_Attributes
 * @category   Account Plugin
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Provides a plugin to accounts to allow additional information to be set for an application
 * within a users profile.
 *
 * @package    Internal_Account_Plugin_Attributes
 * @category   Account Plugin
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */
class Internal_Account_Plugin_Attributes implements Ot_Plugin_Interface
{
    protected $_name = 'tbl_account_attributes';
    
    public function __construct()
    {
        global $application;

        $prefix = $application->getOption('tablePrefix');

        if (!empty($prefix)) {
            $this->_name = $prefix . $this->_name;
        }
    }
    
    public function addSubForm()
    {
        return $this->_getForm();
    }
    
    public function addProcess($data)
    {
        $dba = Zend_Db_Table::getDefaultAdapter();
        $dba->insert($this->_name, $data);
    }
    
    public function editSubForm($id)
    {
        $data = $this->get($id);
        
        return $this->_getForm($data);
    }
    
    public function editProcess($data)
    {
        $dba = Zend_Db_Table::getDefaultAdapter();
        
        $where = $dba->quoteInto('accountId = ?', $data['accountId']);
        
        $select = $dba->select();

        $select->from($this->_name)->where('accountId = ?', $data['accountId']);

        $result = $dba->fetchAll($select);

        if (count($result) == 1) {
            $dba->update($this->_name, $data, $where);   
        } else {
            $dba->insert($this->_name, $data);
        }
    }
    
    public function deleteProcess($id)
    {
        $dba = Zend_Db_Table::getDefaultAdapter();
        
        $where = $dba->quoteInto('accountId = ?', $id);

        return $dba->delete($this->_name, $where);
    }
    
    public function get($id)
    {
        $dba = Zend_Db_Table::getDefaultAdapter();
        
        $select = $dba->select();

        $select->from($this->_name)->where('accountId = ?', $id);

        $result = $dba->fetchAll($select);

        if (count($result) == 1) {
            $result = $result[0];
        } else {
            $result = array();
        }
        
        $form = $this->_getForm($result);
        
        $data = array();
        foreach ($form->getElements() as $e) {
            $data[$e->getName()] = $e->getValue();
        }
        
        return $data;
    }
    
    public function dispatch($data)
    {
    }
    
    protected function _getForm($data = array())
    {        
        $form = new Zend_Form_SubForm();
        
        // create zend form elements here
        
        return $form;       
    }
}