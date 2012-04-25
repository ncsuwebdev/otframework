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
class Ot_Trigger_PluginRegister
{

    const REGISTRY_KEY = 'Ot_Trigger_PluginRegister';

    public function __construct()
    {
        if (!Zend_Registry::isRegistered(self::REGISTRY_KEY)) {
            Zend_Registry::set(self::REGISTRY_KEY, array());
        }
    }

    public function registerTriggerPlugin(Ot_TriggerPlugin $plugin)
    {
        $registered = $this->getTriggerPlugins();
        $registered[$plugin->getPluginId()] = $plugin;

        Zend_Registry::set(self::REGISTRY_KEY, $registered);
    }

    public function registerTriggerPlugins(array $plugins)
    {
        foreach ($plugins as $p) {
            $this->registerTriggerPlugin($p);
        }
    }

    public function getTriggerPlugin($pluginId)
    {
        $registered = $this->getTriggerPlugins();

        foreach ($registered as $r) {
            if ($r->getPluginId() == $pluginId) {
                return $r;
            }
        }

        return null;

    }
    
    public function getTriggerPlugins()
    {
        return Zend_Registry::get(self::REGISTRY_KEY);
    }
}

