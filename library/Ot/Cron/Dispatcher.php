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
 * @package    Ot_Cron
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    BSD License
 * @version    SVN: $Id: $
 */

/**
 * Model to interact with the cron jobs
 *
 * @package    Ot_Cron
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 *
 */
class Ot_Cron_Dispatcher
{
    /**
     * The variables to be replaced in the email
     *
     * @var unknown_type
     */
    protected $_vars = array();

    const REGISTRY_KEY = 'Ot_Cron_Registry';

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
     * Dispatches the cron jobs
     *
     * @param int $
     */
    public function dispatch($cron)
    {
    	
    	$register = new Ot_Cron_Register();
    	$jobs = $register->getCronJobs();
    	foreach ($jobs as $job) {
    	
    	}
    	
    	/**
schedule:

minutes (0-59)
hour (0-23)
dayOfMonth (1-31)
month (1-12)
dayOfWeek (0-6) (sun-sat)

(?P<minute>[0-9\*,\/\-]+)\s+
^then parse each. change x/y to comma saperated list of times "30/5" changes to "0,5,10,15,20,25,30"
A/B means go from 0 to A, stepping B each time

    	 */
    	
    }

    public function registerCron(Ot_Cron $cron)
    {
        $registered = $this->getRegisteredTriggers();
        $registered[] = $cron;

        Zend_Registry::set(self::REGISTRY_KEY, $registered);
    }

    public function registerCrons(array $cronJobs)
    {
        foreach ($cronJobs as $job) {
            $this->registerCron($job);
        }
    }

    public function getRegisteredCronJobs()
    {
        return Zend_Registry::get(self::REGISTRY_KEY);
    }
}

