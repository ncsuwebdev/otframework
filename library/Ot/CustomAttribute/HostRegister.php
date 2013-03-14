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
class Ot_CustomAttribute_HostRegister
{
    const REGISTRY_KEY = 'Ot_CustomAttribute_HostRegister';

    public function __construct()
    {
        if (!Zend_Registry::isRegistered(self::REGISTRY_KEY)) {
            Zend_Registry::set(self::REGISTRY_KEY, array());
        }
    }

    public function registerHost(Ot_CustomAttribute_Host $host)
    {
        $registered = $this->getHosts();
        if (isset($registered[$host->getKey()])) {
            throw new Ot_Exception('Custom Field Host ' . $host->getKey() . ' already registered');
        }
        
        $registered[$host->getKey()] = $host;

        Zend_Registry::set(self::REGISTRY_KEY, $registered);
    }

    public function registerHosts(array $hosts)
    {
        foreach ($hosts as $h) {
            $this->registerHost($h);
        }
    }

    public function getHost($key)
    {
        $registered = $this->getHosts();

        return (isset($registered[$key])) ? $registered[$key] : null;
    }
    
    public function getHosts()
    {
        return Zend_Registry::get(self::REGISTRY_KEY);
    }

    public function __get($key)
    {
        return $this->getHost($key);
    }
}

