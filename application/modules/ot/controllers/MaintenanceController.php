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
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    BSD License
 * @version    SVN: $Id: $
 */

/**
 * Allows the user to manage all application-wide configuration variables.
 *
 * @package    Ot_MaintenanceController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
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
    
    protected $_overridesPath = '';
        
    protected $_inMaintenanceMode = false;
    
    public function init()
    {
        $this->_overridesPath = realpath(APPLICATION_PATH . '/../overrides');
        
        $this->_inMaintenanceMode = (is_file($this->_overridesPath . '/' . $this->_maintenanceModeFileName));
                
        if (!is_writable($this->_overridesPath)) {
            throw new Ot_Exception_Data($this->view->translate('msg-error-configDirNotWritable', $this->_overridesPath));
        }
    }
    /**
     * Shows the maintenance mode index page
     */
    public function indexAction()
    {
        $this->_helper->pageTitle('ot-maintenance-index:title');
        
        $form = new Ot_Form_MaintenanceMode(array('currentMaintenanceModeStatus' => $this->_inMaintenanceMode));
        $form->setMethod(Zend_Form::METHOD_GET);
        $form->setAction($this->view->url(array('controller' => 'maintenance', 'action' => 'toggle'), 'ot', true));
        
        $this->view->assign(array(
            'inMaintenanceMode' => $this->_inMaintenanceMode,
            'form'              => $form,
        ));
    }

    /**
     * Enables or disables maintenance mode
     *
     */
    public function toggleAction()
    { 
        $status = $this->_getParam('status', null);
        
        if (is_null($status)) {
            throw new Ot_Exception_Input('msg-error-statusNotFound');
        }
        
        if ($status == '1') {
            file_put_contents($this->_overridesPath . '/' . $this->_maintenanceModeFileName, '');
        } else {
            unlink($this->_overridesPath . '/' . $this->_maintenanceModeFileName); 
        }
        
        $logOptions = array(
            'attributeName' => 'appConfig',
            'attributeId'   => '0',
        );
        
        if ($status == '1') {
            $logMsg = "Application was put into maintenance mode";
            $this->_helper->messenger->addInfo('msg-info-maintenanceOn');
        } else {
            $logMsg = "Application was taken out of maintenance mode";
            $this->_helper->messenger->addInfo('msg-info-maintenanceOff');
        }
        
        $this->_helper->log(Zend_Log::INFO, $logMsg, $logOptions);

        $this->_helper->redirector->gotoRoute(array('controller' => 'maintenance'), 'ot', true);
    }
}