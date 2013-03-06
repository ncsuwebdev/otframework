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
            
        $this->view->guestHasAccess = $this->_helper->hasAccess('index', 'ot_cronjob', $this->_helper->configVar('defaultRole'));
        
        $role = new Ot_Model_DbTable_Role();
        $this->view->defaultRole = $role->find($this->_helper->configVar('defaultRole'));

        $registry = new Ot_Cron_Register();

        $status = new Ot_Model_DbTable_CronStatus();
        $statusMarkers = $status->fetchAll();

        $status = array();
        foreach ($statusMarkers as $s) {
            $status[$s->name] = array(
                'status' => $s->status,
                'lastRunDt' => $s->lastRunDt,
            );
        }

        $this->view->messages = $this->_helper->messenger->getMessages();
        $this->view->cronjobs = $registry->getCronjobs();
        $this->view->status = $status;
        $this->_helper->pageTitle('ot-cron-index:title');
    }

    /**
     * Toggles the status of the selected cron job
     *
     */
    public function toggleAction()
    {
        $cs = new Ot_Model_DbTable_CronStatus();

        $get = Zend_Registry::get('getFilter');
        
        if (!isset($get->name)) {
            throw new Ot_Exception_Input('msg-error-nameNotSet');
        }
        
        if (!isset($get->status)) {
            $cj = $cs->find($get->name);
    
            if (is_null($cj)) {
                $cj = array('status' => 'disabled', 'name' => $get->name);
    
                $status = 'disabled';
            } else {
                $cj = $cj->toArray();
    
                $status = $cj['status'];
            }
        } else {
            $status = $get->status;
        }        
        
        $form = new Zend_Form();
        $form->setAction('?name=' . $get->name)->setMethod('post')->setAttrib('id', 'toggleCronJob');
       
        $hidden = $form->createElement('hidden', 'status');
        $hidden->setValue(($status == 'enabled') ? 'disable' : 'enable');
        $hidden->clearDecorators();
        $hidden->addDecorators(array(array('ViewHelper')));
               
        $submit = $form->createElement('submit', 'submitButton', array('label' => 'form-button-yes'));
        $submit->setDecorators(array(array('ViewHelper', array('helper' => 'formSubmit'))));
                 
        $cancel = $form->createElement('button', 'cancel', array('label' => 'form-button-cancel'));
        $cancel->setAttrib('id', 'cancel');
        $cancel->setDecorators(array(array('ViewHelper', array('helper' => 'formButton'))));
                        
        $form->addElements(array($hidden))->setElementDecorators(
            array(
                'ViewHelper',
                'Errors',      
                array('HtmlTag', array('tag' => 'div', 'class' => 'elm')), 
                array('Label', array('tag' => 'span')),      
            )
        )->addElements(array($submit, $cancel));
        
        if ($this->_request->isPost() && $form->isValid($_POST)) {
            
            $status = ($form->getValue('status') == 'enable') ? 'enabled' : 'disabled';

            $cs->setCronStatus($get->name, $status);

            $logOptions = array('attributeName' => 'cronName', 'attributeId' => $get->name);
                    
            $this->_helper->log(Zend_Log::INFO, 'Cronjob ' . $get->name . ' was set to ' . $status . '.', $logOptions);
                        
            $this->_helper->redirector->gotoRoute(array('controller' => 'cron'), 'ot', true);
        }
        
        if ($get->name == 'all') {
            $this->view->displayName = 'all cron jobs';
        } else {
            $this->view->displayName = $get->name;
        }

        $this->view->status = ($status == 'enabled') ? 'disable' : 'enable';
        $this->_helper->pageTitle('ot-cron-toggle:title');
        $this->view->form = $form;
    }
    
    public function jobAction()
    {
        set_time_limit(0);

        $name = $this->_getParam('name');

        $register = new Ot_Cron_Register();
        $dispatcher = new Ot_Cron_Dispatcher();
        $cs = new Ot_Model_DbTable_CronStatus();

        $thisJob = $register->getCronjob($name);

        if (is_null($thisJob)) {
            throw new Exception('Job not found');
        }

        if (!$cs->isEnabled($name)) {
            throw new Exception('Job must be enabled for it to be run');
        }

        $dispatcher->dispatch($name);

        $this->_helper->messenger->addSuccess('Job executed successfully');
        $this->_helper->redirector->gotoRoute(array('controller' => 'cron', 'action' => 'index'), 'ot', true);
    }
}