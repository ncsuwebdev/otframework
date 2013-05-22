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
 * @package    Ot_CronController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Controller to show the status of all cron jobs running in the system
 *
 * @package    Ot_CronController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */
class Ot_CronController extends Zend_Controller_Action
{
    /**
     * shows all the cron jobs
     *
     */
    public function indexAction()
    {
        $this->view->acl = array(
            'add'    => false,
            'edit'   => false,
            'toggle' => $this->_helper->hasAccess('toggle'),
            'acl'    => $this->_helper->hasAccess('index', 'ot_acl')
        );
            
        
        $role = new Ot_Model_DbTable_Role();
                
        $statusModel = new Ot_Model_DbTable_CronStatus();
        $statusMarkers = $statusModel->fetchAll();

        $cjStatus = array();
        foreach ($statusMarkers as $s) {
            $cjStatus[$s->jobKey] = array(
                'status'    => $s->status,
                'lastRunDt' => $s->lastRunDt,
            );
        }
        
        $jobs = array();        
        
        $cjr = new Ot_Cron_JobRegister();
        
        $registeredJobs = $cjr->getJobs();
        
        foreach ($registeredJobs as $j) {
                        
            $cschedule = $j->getSchedule();
            
            if (count(explode(' ', $cschedule)) == 5) {
                $cschedule .= ' *';
            }
            
            $parts = explode(' ', $cschedule);
            
            $parts[0] = preg_replace('/\*\//', '0-59/', $parts[0]);
            $parts[1] = preg_replace('/\*\//', '0-23/', $parts[1]);
            $parts[2] = preg_replace('/\*\//', '1-31/', $parts[2]);
            $parts[3] = preg_replace('/\*\//', '1-12/', $parts[3]);
            $parts[4] = preg_replace('/\*\//', '0-6/', $parts[4]);
            
            $cschedule = implode($parts, ' ');
            try {
                $schedule = Ot_Cron_Schedule::fromCronString($cschedule);
            } catch (Exception $e) {
                $schedule = null;
            }
            
            $jobs[] = array(
                'job'       => $j,
                'isEnabled' => (isset($cjStatus[$j->getKey()]) && $cjStatus[$j->getKey()]['status'] == 'enabled'),
                'lastRunDt' => (isset($cjStatus[$j->getKey()])) ? $cjStatus[$j->getKey()]['lastRunDt'] : 0,
                'schedule'  => (is_null($schedule)) ? $cschedule : $schedule->asNaturalLanguage(),
            );
        }
        
        $this->view->assign(array(
            'defaultRole'    => $role->find($this->_helper->configVar('defaultRole')),
            'guestHasAccess' => $this->_helper->hasAccess('index', 'ot_cronjob', $this->_helper->configVar('defaultRole')),
            'cronjobs'       => $jobs,
        ));
        
        $this->_helper->pageTitle('ot-cron-index:title');
    }

    /**
     * Toggles the status of the selected cron job
     *
     */
    public function toggleAction()
    {
        $jobKey = $this->_getParam('jobKey', null);
        
        if (is_null($jobKey)) {
            throw new Ot_Exception_Input('msg-error-nameNotSet');
        }
        
        $status = $this->_getParam('status', null);
        
        if (is_null($status) || !in_array($status, array('enabled', 'disabled'))) {
            throw new Ot_Exception_Input('Status not set in the query string');
        }                
        
        if ($this->_request->isPost()) {
            
            $cs = new Ot_Model_DbTable_CronStatus();
            $cs->setCronStatus($jobKey, $status);

            $logOptions = array('attributeName' => 'cronName', 'attributeId' => $jobKey);
                    
            $this->_helper->log(Zend_Log::INFO, 'Cronjob ' . $jobKey . ' was set to ' . $status . '.', $logOptions);
                        
            $this->_helper->redirector->gotoRoute(array('controller' => 'cron'), 'ot', true);
        } else {
            throw new Ot_Exception_Access('You are not allowed to access this method directly');
        }
    }
    
    public function jobAction()
    {
        set_time_limit(0);

        $jobKey = $this->_getParam('jobKey', null);
        
        if (is_null($jobKey)) {
            throw new Ot_Exception_Input('msg-error-nameNotSet');
        }
        
        $dispatcher = new Ot_Cron_Dispatcher();

        $dispatcher->dispatch($jobKey);

        $this->_helper->messenger->addSuccess('Job executed successfully');
        $this->_helper->redirector->gotoRoute(array('controller' => 'cron', 'action' => 'index'), 'ot', true);
    }
}
