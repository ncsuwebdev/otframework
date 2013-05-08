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
     * Dispatches the cron jobs
     *
     * @param int $
     */
    public function dispatch($jobKey = null)
    {    	
    	$register = new Ot_Cron_JobRegister();
        $cs = new Ot_Model_DbTable_CronStatus();

        if (!is_null($jobKey)) {
            
            $thisJob = $register->getJob($jobKey);

            if (is_null($jobKey)) {
                throw new Exception('Job not found');
            }

            if (!$cs->isEnabled($thisJob->getKey())) {
                throw new Ot_Exception('Job is disabled and cannot be run');
            }

            $lastRunDt = $cs->getLastRunDt($thisJob->getKey());
            
            $jobObj = $thisJob->getJobObj();
                        
            // make sure job isn't already running
            if (($pid = Ot_Cron_Lock::lock($thisJob->getKey())) == FALSE) {  
                return;
            }
            
            $jobObj->execute($lastRunDt);
            
            Ot_Cron_Lock::unlock($thisJob->getKey());

            $cs->executed($thisJob->getKey(), time());
            
            return;
        }

        $now = time();
        
        $jobs = $register->getJobs();

    	foreach ($jobs as $job) {
            
            if ($this->shouldExecute($job->getSchedule(), $now)) {                

                if (!$cs->isEnabled($job->getKey())) {
                    continue;
                }

                $lastRunDt = $cs->getLastRunDt($job->getKey());

                $jobObj = $job->getJobObj();
                
                if (($pid = Ot_Cron_Lock::lock($job->getKey())) == FALSE) {  
                    continue;
                }
                
                $jobObj->execute($lastRunDt);
                
                Ot_Cron_Lock::unlock($job->getKey());

                $cs->executed($job->getKey(), time());
            }
        }
    }

    public function shouldExecute($schedule, $timestamp)
    {
        $tab = explode(' ', $schedule);

        if (count($tab) != 5) {
            return false;
        }

        $crontab = array_combine(array('min', 'hour', 'dayOfMonth', 'month', 'dayOfWeek'), $tab);

        $currentTime = array(
            'min'        => date('i', $timestamp),
            'hour'       => date('H', $timestamp),
            'dayOfMonth' => date('d', $timestamp),
            'month'      => date('m', $timestamp),
            'dayOfWeek'  => date('w', $timestamp),
        );

        foreach ($crontab as $key => $value) {
            if ($value != '*') {
                $possibleValues = $this->_valueToArray($value);

                if (!in_array($currentTime[$key], $possibleValues)) {
                    return false;
                }
            }
        }

        return true;
    }

    protected function _valueToArray($value)
    {
        if (preg_match('/-/', $value)) {
            $range = explode('-', $value);

            if (count($range) != 2) {
                return array();
            }

            return range($range[0], $range[1]);
        }

        if (preg_match('/,/', $value)) {
            return explode(',', $value);
        }

        if (preg_match('/\*\//', $value)) {
            $frequency = str_replace('*/', '', $value);

            return range(0, 60, $frequency);
        }

        return array($value);
    }
}

