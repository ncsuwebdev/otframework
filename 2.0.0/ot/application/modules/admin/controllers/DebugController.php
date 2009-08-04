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
 * @package    Admin_DebugController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 * @license    BSD License
 * @version    SVN: $Id: $
 */

/**
 * Allows the user to turn debug mode on or off.  Debug mode shows useful debug 
 * information like what database you're connected to.
 *
 * @package    Admin_DebugController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 */
class Admin_DebugController extends Zend_Controller_Action  
{       
    /**
     * The cookie name to use for debugMode
     * 
     * The name "debugMode" is also referred to in 
     * Ot_FrontController_Plugin_MaintenanceMode, so if you change the 
     * filename, it needs to be changed there too
     */  
    protected $_debugModeCookieName = "debugMode";
        
    /**
     * Shows the debug mode index page
     */
    public function indexAction()
    {
        $this->view->debugMode = (isset($_COOKIE[$this->_debugModeCookieName])) ? true : false;
        $this->_helper->pageTitle('admin-debug-index:title');
        $this->view->messages = $this->_helper->flashMessenger->getMessages();
    }

    /**
     * Enables or disables debug mode
     *
     */
    public function toggleAction()
    { 
        $get = Zend_Registry::get('getFilter');
        if (!isset($get->status)) {
            throw new Ot_Exception_Input('msg-error-statusNotFound');
        }
        $status = $get->status;
               
        $messages = array();
        
        if ($status == 'on') {
            setcookie($this->_debugModeCookieName, '1', time()+60*60*24*30, $this->view->baseUrl());
        } else {
            setcookie($this->_debugModeCookieName, '', time()-1, $this->view->baseUrl()); 
        }
        
        $logOptions = array(
                       'attributeName' => 'appConfig',
                       'attributeId'   => '0',
        );
        
        if ($status == 'on') {
            $logMsg = "Application was put into debug mode";
            $this->_helper->flashMessenger->addMessage('msg-info-debugOn');
        } else {
            $logMsg = "Application was taken out of debug mode";
            $this->_helper->flashMessenger->addMessage('msg-info-debugOff');
        }
        
        $this->_helper->log(Zend_Log::INFO, $logMsg, $logOptions);

        $this->_helper->redirector->gotoUrl('/admin/debug/');
        
        $this->view->messages = $messages;
    }
}