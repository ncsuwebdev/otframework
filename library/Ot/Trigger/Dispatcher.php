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
class Ot_Trigger_Dispatcher
{
    /**
     * The variables to be replaced in the email
     *
     * @var unknown_type
     */
    protected $_vars = array();

    const REGISTRY_KEY = 'Ot_Trigger_Registry';

    public function __construct()
    {
        if (!Zend_Registry::isRegistered(self::REGISTRY_KEY)) {
            Zend_Registry::set(self::REGISTRY_KEY, array());
        }
    }

    /**
     * Overrides the set method so that we can wrap the variables for the email
     * in a nice package. 
     *
     * @param unknown_type $name
     * @param unknown_type $value
     */
    public function __set($name, $value)
    {
        $this->_vars[$name] = $value;        
    }
    
    /**
     * Sets an array of email variables
     *
     * @param array $data
     */
    public function setVariables(array $data)
    {
        $this->_vars = array_merge($this->_vars, $data);
    }
    

    /**
     * Dispatches the trigger specified
     *
     * @param int $key
     */
    public function dispatch($key)
    {

        $vr = new Ot_Config_Register();
        $triggerSystem = $vr->getVar('triggerSystem');
        
        if (is_null($triggerSystem)) {
            $triggerSystem = false;
        } else {
            $triggerSystem = $triggerSystem->getValue();
        }
        
        // if the trigger system is globally disabled just return
        if ($triggerSystem == false) {
            return;
        }
        
        $action = new Ot_Model_DbTable_TriggerAction();
        $actions = $action->getActionsForTrigger($key);

        foreach ($actions as $a) {
            $helper = new $a->actionKey;

            $data = $helper->getDbTable()->find($a->triggerActionId);
            
            if (is_null($data)) {
                continue;
            }            
            
            $data = $data->toArray();
            
            foreach ($data as &$d) {
                foreach ($this->_vars as $key => $value) {
                    if (is_array($value)) {
                        $value = implode(', ', $value);
                    }

                    $d = str_replace("[[$key]]", $value, $d);
                }
            }

            $helper->dispatch($data);
        }
    }

    public function registerTrigger(Ot_Trigger $trigger)
    {
        $registered = $this->getRegisteredTriggers();
        $registered[] = $trigger;

        Zend_Registry::set(self::REGISTRY_KEY, $registered);
    }

    public function registerTriggers(array $triggers)
    {
        foreach ($triggers as $t) {
            $this->registerTrigger($t);
        }
    }

    public function getRegisteredTriggers()
    {
        return Zend_Registry::get(self::REGISTRY_KEY);
    }
}

