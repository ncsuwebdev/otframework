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
 * @package    Admin_CronController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Information Technology Division
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @see        http://itdapps.ncsu.edu
 * @version    SVN: $Id: $
 */

/**
 * Controller to show the status of all cron jobs running in the system
 *
 * @package    Admin_CronController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Information Technology Division
 */
class Admin_CronController extends Internal_Controller_Action 
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
            'toggle' => $this->_acl->isAllowed($this->_role, $this->_resource, 'toggle'),
            );

        $cs = new Ot_Cron_Status();

        $jobs = $cs->getAvailableCronJobs();

        if (count($jobs) != 0) {
            $this->view->javascript = 'sortable.js';
        }

        $this->view->cronjobs       = $jobs;
        $this->view->title          = "Cron Job Status";
    }

    /**
     * Toggles the status of the selected cron job
     *
     */
    public function toggleAction()
    {
        $cs = new Ot_Cron_Status();

        $get = Zend_Registry::get('getFilter');
        
        if (!isset($get->path)) {
        	throw new Ot_Exception_Input('Path is not set in query string.');
        }
        
        if (!isset($get->status)) {
            $cj = $cs->find($get->path);
    
            if (is_null($cj)) {
                $cj = array(
                    'status' => 'disabled',
                    'path'   => $get->path
                    );
    
                $status = 'disabled';
            } else {
                $cj = $cj->toArray();
    
                $status = $cj['status'];
            }
        } else {
            $status = $get->status;
        }        
        
        $form = new Zend_Form();
        $form->setAction('?path=' . $get->path)
             ->setMethod('post')
             ->setAttrib('id', 'toggleCronJob')
             ;
       
        $hidden = $form->createElement('hidden', 'status');
        $hidden->setValue(($status == 'enabled') ? 'disable' : 'enable');
        $hidden->clearDecorators();
        $hidden->addDecorators(array(
            array('ViewHelper'),    // element's view helper
        ));
        
        $form->addElement($hidden)
             ->addElement('submit', 'toggleButton', array('label' => 'Yes'))
             ->addElement('button', 'cancel', array('label' => 'No, Go Back'))
             ;         
        
        if ($this->_request->isPost() && $form->isValid($_POST)) {
            $status = ($form->getValue('status') == 'enable') ? 'enabled' : 'disabled';

            $result = $cs->setCronStatus($get->path, $status);

            $this->_logger->setEventItem('attributeName', 'cronpath');
            $this->_logger->setEventItem('attributeId', $get->path);
            $this->_logger->info('cron was set to ' . $status);
                        
            $this->_helper->redirector->gotoUrl('/admin/cron/');
        }
        
        
        if ($get->path == 'all') {
            $this->view->displayPath = 'all cron jobs';
        } else {
            $this->view->displayPath = $get->path;
        }

        $this->view->status = ($status == 'enabled') ? 'disable' : 'enable';
        $this->view->title  = "Toggle Cron Job Status";
        $this->view->form   = $form;
    }
}