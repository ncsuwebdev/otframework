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
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Allows the application to be run in maintenance mode, where only admins are 
 * allowed to view the site.
 *
 * @package    Ot_FrontController_Plugin_MaintenanceMode
 * @category   Front Controller Plugin
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 */
class Ot_FrontController_Plugin_MaintenanceMode extends Zend_Controller_Plugin_Abstract
{
    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        $vr     = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        $layout = Zend_Controller_Action_HelperBroker::getStaticHelper('layout');
               
        $config = Zend_Registry::get('appConfig');
        
        $zcf = Zend_Controller_Front::getInstance();
        
        $role  = (is_null(Ot_Authz::getInstance()->getRole()) ? (string)$config->loginOptions->defaultRole : Ot_Authz::getInstance()->getRole());
        $acl = Zend_Registry::get('acl');

        if ($config->maintenanceMode) {
            if (!$acl->isAllowed($role, 'admin_maintenance', 'index')) {
                if (!($request->getModuleName() == 'login' && $request->getControllerName() == 'index' && $request->getActionName() == 'index')) {
                    $layout->setLayoutPath('./ot/application/views/layouts');
                    $layout->setLayout('maintenance');
                }
            } else {        
        	    $view = $vr->view;
        	
                $response = $this->getResponse();

        	    $response->setBody($view->render('maintenanceHeader.tpl') . $response->getBody());
            }
        }
    }
}