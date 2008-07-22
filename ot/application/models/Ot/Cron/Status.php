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
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Model to allow admins to enable and disable cron jobs
 *
 * @package    Ot_Cron_Status
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 *
 */
class Ot_Cron_Status extends Ot_Db_Table {


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
    protected $_primary = 'path';

    /**
     * Checks to see if a certain cron job is enabled
     *
     * @param string $path
     * @return boolean
     */
    public function isEnabled($path)
    {
        $result = $this->find($path);

        if (is_null($result)) {
            return false;
        }

        return ($result->status == 'enabled');
    }
    
    public function executed($path, $ts)
    {
        $data = array(
           'path'      => $path,
           'lastRunDt' => $ts,
        );
        
        $this->update($data, null);
    }
    
    public function getLastRunDt($path)
    {
        $result = $this->find($path);

        if (is_null($result)) {
            return 0;
        }

        return $result->lastRunDt;      
    }    

    public function setCronStatus($path, $status)
    {
        $dba = $this->getAdapter();

        if ($path == 'all') {

            $jobs = $this->getAvailableCronJobs();

            foreach ($jobs as $j) {
                $data = array('status' => $status);
                $job = $this->find($j['path']);

                if (!is_null($job)) {
                    $where = $dba->quoteInto('path = ?', $j['path']);

                    $this->update($data, $where);
                } else {

                    $data['path'] = $j['path'];

                    $this->insert($data);
                }
            }
        } else {

            $data = array('status' => $status);
            $job = $this->find($path);

            if (!is_null($job)) {
                $where = $dba->quoteInto('path = ?', $path);

                $this->update($data, $where);
            } else {
                $data['path'] = $path;

                $this->insert($data);
            }
        }

        return true;
    }

    public function getAvailableCronJobs()
    {
        $config = Zend_Registry::get('appConfig');

        if (!is_dir($config->cronFilePath)) {
            throw new Exception('Cron directory not set correctly in config file');
        }

        $dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($config->cronFilePath));
        $crons = array();
        foreach ($dir as $file) {
            $f = str_replace($config->cronFilePath . '/', '', $file);
            if (preg_match('/\.php$/i', $f)) {
                $temp = array();
                $temp['path'] = str_replace(DIRECTORY_SEPARATOR, '_', preg_replace('/\.php$/i', '', $f));

                $data = $this->find($temp['path']);

                if (!is_null($data)) {
                    $temp = $data->toArray();
                } else {
                    $temp['status'] = 'disabled';
                }

                $crons[] = $temp;
            }
        }

        return $crons;
    }
}
?>
