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
class Ot_Cron_Status extends Ot_Db_Table
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
    protected $_primary = 'name';

    /**
     * Checks to see if a certain cron job is enabled
     *
     * @param string $name
     * @return boolean
     */
    public function isEnabled($name)
    {
        $result = $this->find($name);

        if (is_null($result)) {
            return false;
        }

        return ($result->status == 'enabled');
    }
    
    public function executed($name, $ts)
    {
        $data = array(
           'name'      => $name,
           'lastRunDt' => $ts,
        );
        
        $this->update($data, null);
    }
    
    public function getLastRunDt($name)
    {
        $result = $this->find($name);

        if (is_null($result)) {
            return 0;
        }

        return $result->lastRunDt;      
    }    

    public function setCronStatus($name, $status)
    {
        $dba = $this->getAdapter();

        if ($name == 'all') {

            $register = new Ot_Cron_Register();

            $jobs = $register->getCronjobs();

            foreach ($jobs as $j) {
                $data = array('status' => $status);
                $job = $this->find($j->getName());

                if (!is_null($job)) {
                    $where = $dba->quoteInto('name = ?', $j->getName());

                    $this->update($data, $where);
                } else {

                    $data['name'] = $j->getName();

                    $this->insert($data);
                }
            }
        } else {

            $data = array('status' => $status);
            $job = $this->find($name);

            if (!is_null($job)) {
                $where = $dba->quoteInto('name = ?', $name);

                $this->update($data, $where);
            } else {
                $data['name'] = $name;

                $this->insert($data);
            }
        }

        return true;
    }

    public function getAvailableCronJobs()
    {
        $filter = new Zend_Filter();
        $filter->addFilter(new Zend_Filter_Word_CamelCaseToDash());
        $filter->addFilter(new Zend_Filter_StringToLower());
        
        require_once APPLICATION_PATH . '/modules/cron/controllers/IndexController.php';
        
        $class = new ReflectionClass('Cron_IndexController');
        $methods = $class->getMethods();
        
        $jobs = array(); 
        
        foreach ($methods as $m) {
                        
            if (preg_match('/action/i', $m->name)) {
                
                $temp = array(); 
                $temp['name'] = $filter->filter(preg_replace('/action/i', '', $m->name));
                
                $data = $this->find($temp['name']);

                if (!is_null($data)) {
                    $temp = $data->toArray();
                } else {
                    $temp['status']    = 'disabled';
                    $temp['lastRunDt'] = 0;
                }
                   
                $jobs[] = $temp;
            }
        }
        
        return $jobs;
    }
}