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
 * @package    Ot_Api
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    BSD License
 * @version    SVN: $Id: $
 */

/**
 * Model to interact with the API methods
 *
 * @package    Ot_Api
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 *
 */
class Ot_Api_Register
{
    const REGISTRY_KEY = 'Ot_Api_Registry';

    public function __construct()
    {
        if (!Zend_Registry::isRegistered(self::REGISTRY_KEY)) {
            Zend_Registry::set(self::REGISTRY_KEY, array());
        }
    }

    public function registerApiEndpoint(Ot_Api_Endpoint $apiEndpoint)
    {
        $registered = $this->getApiEndpoints();
        $registered[$apiEndpoint->getName()] = $apiEndpoint;

        Zend_Registry::set(self::REGISTRY_KEY, $registered);
    }

    public function registerApiEndpoints(array $apiEndpoints)
    {
        foreach ($apiEndpoints as $endpoint) {
            $this->registerApiEndpoint($endpoint);
        }
    }

    public function getApiEndpoint($name)
    {
        $registered = $this->getApiEndpoints();

        return (isset($registered[$name])) ? $registered[$name] : null;
    }
    
    /**
     * returns all api endpoints
     **/
    public function getApiEndpoints()
    {
        return Zend_Registry::get(self::REGISTRY_KEY);
    }
}

