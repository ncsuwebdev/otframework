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
 * @package    Ot_MaintenanceController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 * @license    BSD License
 * @version    SVN: $Id: $
 */

/**
 * Allows the user to manage all application-wide configuration variables.
 *
 * @package    Ot_MaintenanceController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 */
class Ot_MaintenanceController extends Zend_Controller_Action  
{       
    /**
     * The filename to use for maintenanceMode
     * 
     * The name "maintenanceMode" is also referred to in 
     * Ot_FrontController_Plugin_MaintenanceMode, so if you change the 
     * filename, it needs to be changed there too
     */  
    protected $_maintenanceModeFileName = "maintenanceMode";
        
    /**
     * Shows the maintenance mode index page
     */
    public function indexAction()
    {
        $this->view->maintenanceMode = (is_file(APPLICATION_PATH . '/../overrides/' . $this->_maintenanceModeFileName)) ? true : false;
        $this->_helper->pageTitle('ot-maintenance-index:title');
        $this->view->messages = $this->_helper->flashMessenger->getMessages();
    }

    /**
     * Enables or disables maintenance mode
     *
     */
    public function toggleAction()
    { 
        $config = Zend_Registry::get('config');
        
        $path = realpath(APPLICATION_PATH . '/../overrides');
        
        if (!is_writable($path)) {
            throw new Ot_Exception_Data($this->view->translate('msg-error-configDirNotWritable', $path));
        }
        
        $get = Zend_Registry::get('getFilter');
        if (!isset($get->status)) {
            throw new Ot_Exception_Input('msg-error-statusNotFound');
        }
        $status = $get->status;
               
        $messages = array();
        
        if ($status == 'on') {
            file_put_contents($path . '/' . $this->_maintenanceModeFileName, '');
        } else {
            unlink($path . '/' . $this->_maintenanceModeFileName); 
        }
        
        $logOptions = array(
                       'attributeName' => 'appConfig',
                       'attributeId'   => '0',
        );
        
        if ($status == 'on') {
            $logMsg = "Application was put into maintenance mode";
            $this->_helper->flashMessenger->addMessage('msg-info-maintenanceOn');
        } else {
            $logMsg = "Application was taken out of maintenance mode";
            $this->_helper->flashMessenger->addMessage('msg-info-maintenanceOff');
        }
        
        $this->_helper->log(Zend_Log::INFO, $logMsg, $logOptions);

        $this->_helper->redirector->gotoRoute(array('controller' => 'maintenance'), 'ot', true);
        
        $this->view->messages = $messages;
    }
}