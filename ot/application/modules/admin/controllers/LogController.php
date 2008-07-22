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
 * @package    Admin_LogController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Controller to show logs gathered from the application.
 *
 * @package    Admin_LogController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 */
class Admin_LogController extends Internal_Controller_Action 
{
    /**
     * displays logs based on search criteria
     */
    public function indexAction()
    {
    	$al = new Ot_Log();

        $get = Zend_Registry::get('getFilter');

        $where = '';
        $dba = $al->getAdapter();

        $attr = array('userId', 'role', 'attributeName', 'attributeId', 'request', 'sid', 'priority');

        foreach ($attr as $a) {
            if (isset($get->$a) && $get->$a != '') {
                if ($where != '') {
                    $where .= ' AND ';
                }

                $where .= $dba->quoteInto($a . ' = ?', $get->$a);

                $this->view->$a = $get->$a;
            }
        }

        if (isset($get->beginDt) && isset($get->endDt) && $get->endDt != '' && $get->beginDt != '') {
            if ($where != '') {
                $where .= ' AND ';
            }

            $where .= '(' .
                $dba->quoteInto('timestamp >= ?', strtotime($get->beginDt . ' 00:00:00')) .
                ' AND ' .
                $dba->quoteInto('timestamp <= ?', strtotime($get->endDt . ' 00:00:00')) .
                ')';

            $this->view->beginDt = $get->beginDt;
            $this->view->endDt = $get->endDt;
        }


        $result = array();
        if ($where != '') {
            $result = $al->fetchAll($where, 'timestamp DESC');

            $result = $result->toArray();
        }
        
        $javascript = array('calendar.js');
        $css        = array('calendar.css');

        if (count($result) != 0) {
            $javascript[] = 'sortable.js';
        }

        $this->view->javascript = $javascript;
        $this->view->css        = $css;
        $this->view->logs       = $result;

        $this->view->priorityTypes = array(
            '' => '',
            'EMERG',
            'ALERT',
            'CRIT',
            'ERR',
            'WARN',
            'NOTICE',
            'INFO',
            'DEBUG',
            'LOGIN',
            );

        $this->view->title          = "Action Logs";
    }

    /**
     * shows the details of the log message
     *
     */
    public function detailsAction()
    {
        $al = new Ot_Log();

        $get    = Zend_Registry::get('getFilter');

        if (!isset($get->logId)) {
            throw new Ot_Exception_Input('Log ID not set');
        }

        $log = $al->find($get->logId);

        if (is_null($log)) {
            throw new Ot_Exception_Data('Log message could not be found');
        }

        $this->view->log            = $log->toArray();
        $this->view->title          = "Action Log Details";
    }
}