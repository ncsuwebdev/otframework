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
class Ot_Cron_JobRegister
{
    const REGISTRY_KEY = 'Ot_Cron_JobRegister';

    public function __construct()
    {
        if (!Zend_Registry::isRegistered(self::REGISTRY_KEY)) {
            Zend_Registry::set(self::REGISTRY_KEY, array());
        }
    }

    public function registerJob(Ot_Cron_Job $cron)
    {
        $registered = $this->getJobs();
        $registered[$cron->getKey()] = $cron;

        Zend_Registry::set(self::REGISTRY_KEY, $registered);
    }

    public function registerJobs(array $jobs)
    {
        foreach ($jobs as $job) {
            $this->registerJob($job);
        }
    }

    public function getJob($key)
    {
        $registered = $this->getJobs();

        return (isset($registered[$key])) ? $registered[$key] : null;
    }
    
    /**
     * returns all cron jobs regardless of whether it's enabled or disabled
     **/
    public function getJobs()
    {
        return Zend_Registry::get(self::REGISTRY_KEY);
    }
}

