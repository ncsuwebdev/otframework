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
class Ot_Config_Register
{
    const REGISTRY_KEY = 'Ot_Config_Register';

    public function __construct()
    {
        if (!Zend_Registry::isRegistered(self::REGISTRY_KEY)) {
            Zend_Registry::set(self::REGISTRY_KEY, array());
        }
    }

    public function registerVar(Ot_Var_Abstract $var, $moduleNamespace)
    {
        $registered = Zend_Registry::get(self::REGISTRY_KEY);
        
        if (isset($registered[$var->getName()])) {
            throw new Ot_Exception('Config var ' . $var->getName() . ' already registered');
        }
        
        $registered[$var->getName()] = array(
            'namespace' => $moduleNamespace,
            'object'    => $var
        );

        Zend_Registry::set(self::REGISTRY_KEY, $registered);
    }

    public function registerVars(array $vars, $moduleNamespace)
    {
        foreach ($vars as $v) {
            $this->registerVar($v, $moduleNamespace);
        }
    }

    public function getVar($name)
    {
        $registered = $this->getVars();

        return (isset($registered[$name])) ? $registered[$name]['object'] : null;        
    }
    
    public function getVars()
    {
        $registered = Zend_Registry::get(self::REGISTRY_KEY);
                          
        foreach ($registered as $r) {
            $r['object']->setValue($r['object']->getDefaultValue());
        }
        
        require_once APPLICATION_PATH . '/modules/ot/models/DbTable/Config.php';
        
        $model = new Ot_Model_DbTable_Config();
        
        $allVars = $model->fetchAll();
        
        foreach ($allVars as $v) {
            if (isset($registered[$v->varName])) {
                $registered[$v->varName]['object']->setRawValue($v->value);
            }
        }
        
        return $registered;
    }

    public function __get($name)
    {
        return $this->getVar($name);
    }
    
    public function save(Ot_Var_Abstract $var)
    {
        $model = new Ot_Model_DbTable_Config();

        $thisVar = $model->find($var->getName());

        $data = array(
            'varName' => $var->getName(),
            'value'   => $var->getRawValue(),
        );

        if (is_null($thisVar)) {
            $model->insert($data);
        } else {
            $model->update($data, null);
        }        
    }
}

