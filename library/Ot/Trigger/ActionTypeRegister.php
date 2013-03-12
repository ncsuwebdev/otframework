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
class Ot_Trigger_ActionTypeRegister
{

    const REGISTRY_KEY = 'Ot_Trigger_ActionTypeRegister';

    public function __construct()
    {
        if (!Zend_Registry::isRegistered(self::REGISTRY_KEY)) {
            Zend_Registry::set(self::REGISTRY_KEY, array());
        }
    }

    public function registerTriggerActionType(Ot_Trigger_ActionType_Abstract $actionType)
    {
        $registered = $this->getTriggerActionTypes();
        $registered[$actionType->getKey()] = $actionType;

        Zend_Registry::set(self::REGISTRY_KEY, $registered);
    }

    public function registerTriggerActionTypes(array $actionTypes)
    {
        foreach ($actionTypes as $a) {
            $this->registerTriggerActionType($a);
        }
    }

    public function getTriggerActionType($key)
    {
        $registered = $this->getTriggerActionTypes();

        foreach ($registered as $r) {
            if ($r->getKey() == $key) {
                return $r;
            }
        }

        return null;
    }
    
    public function getTriggerActionTypes()
    {
        return Zend_Registry::get(self::REGISTRY_KEY);
    }
}

