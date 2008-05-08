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
 * @package    Admin_EmailqueueController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Information Technology Division
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @see        http://itdapps.ncsu.edu
 * @version    SVN: $Id: $
 */

/**
 * Controller to manage the email queue in the admin section
 *
 * @package    Admin_EmailqueueController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Information Technology Division
 */
class Admin_EmailqueueController extends Internal_Controller_Action 
{
    /**
     * displays emails based on the search criteria
     */
    public function indexAction()
    {
        $eq = new Ot_Email_Queue();

        $get = Zend_Registry::get('getFilter');
        
        $where = '';
        $dba = $eq->getAdapter();

        $attr = array('status', 'attributeName', 'attributeId');

        foreach ($attr as $a) {
            if (isset($get->$a) && $get->$a != '') {
                if ($where != '') {
                    $where .= ' AND ';
                }

                $where .= $dba->quoteInto($a . ' = ?', $get->$a);

                $this->view->$a = $get->$a;
            }
        }

        if (isset($get->queueBeginDt) && isset($get->queueEndDt) && $get->queueBeginDt != '' && $get->queueEndDt != '') {
            if ($where != '') {
                $where .= ' AND ';
            }

            $where .= '(' .
                $dba->quoteInto('queueDt >= ?', strtotime($get->queueBeginDt . ' 00:00:00')) .
                ' AND ' .
                $dba->quoteInto('queueDt <= ?', strtotime($get->queueEndDt . ' 00:00:00')) .
                ')';

            $this->view->queueBeginDt = $get->queueBeginDt;
            $this->view->queueEndDt = $get->queueEndDt;
        }

        if (isset($get->sentBeginDt) && isset($get->sentEndDt) && $get->sentEndDt != '' && $get->sentBeginDt != '') {
            if ($where != '') {
                $where .= ' AND ';
            }

            $where .= '(' .
                $dba->quoteInto('sentDt >= ?', strtotime($get->sentBeginDt . ' 00:00:00')) .
                ' AND ' .
                $dba->quoteInto('sentDt <= ?', strtotime($get->sentEndDt . ' 00:00:00')) .
                ')';

            $this->view->sentBeginDt = $get->sentBeginDt;
            $this->view->sentEndDt = $get->sentEndDt;
        }

        $result = array();
        if ($where != '') {
            $result = $eq->fetchAll($where, 'queueDt DESC');
        }

        for ($i = 0; $i < count($result); $i++) {
            $result[$i]['msg'] = array(
                'to' => implode(', ', $result[$i]['zendMailObject']->getRecipients()),
                'from' => $result[$i]['zendMailObject']->getFrom(),
                'subject' => $result[$i]['zendMailObject']->getSubject(),
                );
        }

        $javascript = array(
            'calendar.js'
        );

        if (count($result) != 0) {
             $javascript[] = 'sortable.js';
        }
        
        $this->view->javascript = $javascript;
        $this->view->css = array('calendar.css');

        $this->view->emails = $result;

        $this->view->statusTypes = array(
            ''        => '',
            'waiting' => 'Waiting',
            'sent'    => 'Sent',
            'error'   => 'Error',
            );

        $this->view->title          = "Email Queue";
    }

    /**
     * Shows the details of an email that is in the queue
     *
     */
    public function detailsAction()
    {
        $eq = new Ot_Email_Queue();

        $get    = Zend_Registry::get('getFilter');

        if (!isset($get->queueId)) {
            throw new Ot_Exception_Input('Queue ID not set');
        }

        $email = $eq->find($get->queueId);

        if (is_null($email)) {
            throw new Ot_Exception_Data('Queued email could not be found');
        }

        $email['msg'] = array(
            'to'      => implode(', ', $email['zendMailObject']->getRecipients()),
            'from'    => $email['zendMailObject']->getFrom(),
            'subject' => $email['zendMailObject']->getSubject(),
            'body'    => nl2br(quoted_printable_decode($email['zendMailObject']->getBodyText(true))),
            'header'  => $email['zendMailObject']->getHeaders(),
            );

        $this->view->email          = $email;
        $this->view->title          = "Queued Email Details";
    }
}