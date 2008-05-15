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
 * @package    Admin_MaintenanceController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 * @license    BSD License
 * @see        http://itdapps.ncsu.edu
 * @version    SVN: $Id: $
 */

/**
 * Allows the user to manage all application-wide configuration variables.
 *
 * @package    Admin_MaintenanceController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 */
class Admin_MaintenanceController extends Internal_Controller_Action  
{   
    /**
     * Path to the config file
     *
     * @var string
     */
    protected $_configFilePath = '';
    
    /**
     * Flash messenger variable
     *
     * @var unknown_type
     */
    protected $_flashMessenger = null;
    
    /**
     * Setup flash messenger and the config file path
     *
     */
    public function init()
    {
        $configFiles = Zend_Registry::get('configFiles');
        
        $this->_configFilePath = $configFiles['app'];
        
        $this->_flashMessenger = $this->getHelper('FlashMessenger');
        $this->_flashMessenger->setNamespace('maintenance');
        
        parent::init();
    }
    
    /**
     * Shows the maintenance mode index page
     */
    public function indexAction()
    {
        $this->view->maintenanceMode = Zend_Registry::get('appConfig')->maintenanceMode;
        $this->view->title = "Maintenance Mode Admin";
    }

    /**
     * Enables or disables maintenance mode
     *
     */
    public function toggleAction()
    { 
        if (!is_writable($this->_configFilePath)) {
            throw new Ot_Exception_Data('App Config File (' . $this->_configFilePath . ') is not writable, therefore it cannot be edited');
        }
        
        $get = Zend_Registry::get('getFilter');
        if (!isset($get->status)) {
            throw new Ot_Exception_Input('No status was found in query string.');
        }
        $status = $get->status;
        
        $messages = array();
        
        if (file_exists($this->_configFilePath)) {
            $xml = simplexml_load_file($this->_configFilePath);
        } else {
            throw new Ot_Exception_Data("Error reading app configuration file");
        }
        
        if ($status == 'on') {
            $xml->production->maintenanceMode = "1";
        } else {
            $xml->production->maintenanceMode = "0"; 
        }
        
        $xmlStr = $xml->asXml();

        if (!file_put_contents($this->_configFilePath, $xmlStr, LOCK_EX)) {
            throw new Ot_Exception_Data("Error saving app configuration file to disk");
        }
        
        $this->_logger->setEventItem('attributeName', 'appConfig');
        $this->_logger->setEventItem('attributeId', '0');
        if ($status == 'on') {
            $this->_logger->info("App was put into maintenance mode");
        } else {
            $this->_logger->info("App was taken out of maintenance mode");
        }
        
        if ($status == 'on') {
            $this->_flashMessenger->addMessage('Maintenance mode has been turned on');
        } else {
            $this->_flashMessenger->addMessage('Maintenance mode has been turned off');
        }

        $this->_helper->redirector->gotoUrl('/admin/maintenance/');
        
        $this->view->messages = $messages;
    }
}