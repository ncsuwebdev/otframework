<?php
/**
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * This license is also available via the world-wide-web at
 * http://itdapps.ncsu.edu/bsd.txt
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to itappdev@ncsu.edu so we can send you a copy immediately.
 *
 * @package    Ot_Cron_Status
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Model to allow admins to enable and disable cron jobs
 *
 * @package    Ot_Cron_Status
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of
 *             Information Technology
 *
 */
class Ot_Model_DbTable_CronStatus extends Ot_Db_Table
{

    /**
     * Name of the table in the database
     *
     * @var string
     */
    protected $_name = 'tbl_ot_cron_status';

    /**
     * Primary key of the table
     *
     * @var string
     */
    protected $_primary = 'jobKey';

    /**
     * Checks to see if a certain cron job is enabled
     *
     * @param string $name
     * @return boolean
     */
    public function isEnabled($jobKey)
    {
        $result = $this->find($jobKey);

        if (is_null($result)) {
            return false;
        }

        return ($result->status == 'enabled');
    }
    
    public function executed($jobKey, $ts)
    {
        $data = array(
           'jobKey'      => $jobKey,
           'lastRunDt' => $ts,
        );
        
        $this->update($data, null);
    }
    
    public function getLastRunDt($jobKey)
    {
        $result = $this->find($jobKey);

        if (is_null($result)) {
            return 0;
        }

        return $result->lastRunDt;
    }

    public function setCronStatus($jobKey, $status)
    {
        $dba = $this->getAdapter();

        if ($jobKey == 'all') {

            $register = new Ot_Cron_JobRegister();

            $jobs = $register->getJobs();

            foreach ($jobs as $j) {
                $data = array('status' => $status);
                $job = $this->find($j->getKey());

                if (!is_null($job)) {
                    $where = $dba->quoteInto('jobKey = ?', $j->getKey());

                    $this->update($data, $where);
                } else {

                    $data['jobKey'] = $j->getKey();

                    $this->insert($data);
                }
            }
        } else {

            $data = array('status' => $status);
            
            $job = $this->find($jobKey);

            if (!is_null($job)) {
                $where = $dba->quoteInto('jobKey = ?', $jobKey);
                
                $this->update($data, $where);
            } else {
                $data['jobKey'] = $jobKey;

                $this->insert($data);
            }
        }
    }
}