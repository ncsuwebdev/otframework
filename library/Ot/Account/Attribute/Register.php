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
 * @package    Ot_Trigger
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    BSD License
 * @version    SVN: $Id: $
 */

/**
 * Model to interact with the email triggers
 *
 * @package    Ot_Trigger
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 *
 */
class Ot_Account_Attribute_Register
{
    const REGISTRY_KEY = 'Ot_Account_Attribute_Register';

    public function __construct()
    {
        if (!Zend_Registry::isRegistered(self::REGISTRY_KEY)) {
            Zend_Registry::set(self::REGISTRY_KEY, array());
        }
    }

    public function registerVar(Ot_Var_Abstract $var)
    {
        $registered = $this->getVars();
        
        if (isset($registered[$var->getName()])) {
            throw new Ot_Exception('Account var ' . $var->getName() . ' already registered');
        }
        
        $registered[$var->getName()] = $var;

        Zend_Registry::set(self::REGISTRY_KEY, $registered);
    }

    public function registerVars(array $vars)
    {
        foreach ($vars as $v) {
            $this->registerVar($v);
        }
    }

    public function getVar($name, $accountId = null)
    {
        $registered = $this->getVars($accountId);

        return (isset($registered[$name])) ? $registered[$name] : null;        
    }
    
    public function getVars($accountId = null)
    {
        $registered = Zend_Registry::get(self::REGISTRY_KEY);
                          
        foreach ($registered as $r) {
            $r->setValue($r->getDefaultValue());
        }
        
        if (is_null($accountId)) {
            return $registered;
        }
        
        $model = new Ot_Model_DbTable_AccountAttribute();
        
        $allVars = $model->fetchAll($model->getAdapter()->quoteInto('accountId = ?', $accountId));
        
        foreach ($allVars as $v) {
            if (isset($registered[$v->varName])) {
                $registered[$v->varName]->setRawValue($v->value);
            }
        }
        
        return $registered;
    }

    public function __get($name)
    {
        return $this->getVar($name);
    }
    
    public function save(Ot_Var_Abstract $var, $accountId)
    {
        $model = new Ot_Model_DbTable_AccountAttribute();

        $where = $model->getAdapter()->quoteInto('varName = ?', $var->getName())
                . ' AND '
                . $model->getAdapter()->quoteInto('accountId = ?', $accountId);
        
        $thisVar = $model->fetchAll($where);                

        $data = array(
            'accountId' => $accountId, 
            'varName'   => $var->getName(),
            'value'     => $var->getRawValue(),
        );

        if ($thisVar->count() == 0) {
            $model->insert($data);
        } else {
            $model->update($data, $where);
        }        
    }
    
    public function delete($accountId)
    {
        $model = new Ot_Model_DbTable_AccountAttribute();

        $where = $model->getAdapter()->quoteInto('accountId = ?', $accountId);
        
        $model->delete($where);
    }
}

