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
 * @package    Ot_FrontController_Plugin_MaintenanceMode
 * @category   Front Controller Plugin
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Allows the application to be run in maintenance mode, where only admins are 
 * allowed to view the site.
 *
 * @package    Ot_FrontController_Plugin_MaintenanceMode
 * @category   Front Controller Plugin
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */
class Ot_FrontController_Plugin_MaintenanceMode extends Zend_Controller_Plugin_Abstract
{
    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        $layout = Zend_Layout::getMvcInstance();
        
        // the name "maintenanceMode" is also referred to in the Admin_MaintenanceController,
        // so if you change the filename, it needs to be changed there too
        $maintenanceModeFileName = 'maintenanceMode';

        $register = new Ot_Config_Register();
        
        $identity = Zend_Auth::getInstance()->getIdentity();
        $role = (empty($identity->role)) ? $register->defaultRole->getValue() : $identity->role;
        
        if (isset($identity->masquerading) && $identity->masquerading == true && isset($identity->realAccount) && !is_null($identity->realAccount) && isset($identity->realAccount->role)) {
            $role = $identity->realAccount->role;
        }
        
        $acl = Zend_Registry::get('acl');
        
        $view = $layout->getView();
        $viewRenderer = Zend_Controller_Action_HelperBroker::getExistingHelper('ViewRenderer');
        
        if (is_file(APPLICATION_PATH . '/../overrides/' . $maintenanceModeFileName)
            && (!$request->isXmlHttpRequest() && !$viewRenderer->getNeverRender())
        ) {
        	
            if (!$acl->isAllowed($role, 'ot_maintenance', 'index')) {
                if (!($request->getModuleName() == 'ot'
                    && $request->getControllerName() == 'login'
                    && $request->getActionName() == 'index')
                ) {
                    $response = $this->getResponse();
                
                    $layout->disableLayout();
                    
                    $response->setBody($view->maintenanceMode()->publicLayout());
                }
            } else {
                $response = $this->getResponse();
                
                // there's no point in setting text here if it's a redirect
                if ($response->isRedirect()) {
                    $response->setBody('');
                } else {
                    $response->setBody($view->maintenanceMode()->header() . $response->getBody());
                }
            }
        }
    }
}