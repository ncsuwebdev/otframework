<?php
/**
 *
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
 * @package    Cron
 * @subpackage Cron_IndexController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Information Technology Division
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @see        http://itdapps.ncsu.edu
 * @version    SVN: $Id: $
 */

/**
 * Main cron controller
 *
 * @package    Cron_IndexController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Information Technology Division
 */
class Cron_IndexController extends Zend_Controller_Action 
{    
    /**
     * Unix timestamp of the date the cron job was last run.
     *
     * @var int
     */
    protected $_lastRunDt = 0;
    
    /**
     * Initialization function
     *
     */
    public function init()
    {
        set_time_limit(0);
        
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNeverRender();
        
        $action = $this->_request->getActionName();
        
        $cs = new Ot_Cron_Status();
        
        if (!$cs->isEnabled($action)) {
            die();
        }
        
        $this->_lastRunDt = $cs->getLastRunDt($action);
        
        $cs->executed($action, time());
        
        parent::init();
    }

    
    /**
     * Cron job to process the email queue
     *
     */
    public function emailQueueAction()
    {           
        $eq = new Ot_Email_Queue();
        
        $messages = $eq->getWaitingEmails(20);
        
        foreach ($messages as $m) {
            try {
                $m['zendMailObject']->send();
        
                $m['status'] = 'sent';
                $m['sentDt'] = time();
        
            } catch (Exception $e) {
                $m['status'] = 'error';
            }
        
            $where = $eq->getAdapter()->quoteInto('queueId = ?', $m['queueId']);
        
            $eq->update($m, $where);
            
            $logOptions = array(
                    'attributeName' => 'queueId',
                    'attributeId'   => $m['queueId'],
            );
                
            $this->_helper->log(Zend_Log::INFO, 'Mail Sent', $logOptions);
        }       
    }

}
