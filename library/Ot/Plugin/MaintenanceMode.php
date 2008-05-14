<?php
/**
 * 
 *
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
 * @package
 * @subpackage Internal_Plugin_Javascript
 * @category   Front Controller Plugin
 * @copyright  Copyright (c) 2007 NC State University Information Technology Division
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @author     Jason Austin <jason_austin@ncsu.edu>
 * @author     Garrison Locke <garrison_locke@ncsu.edu>
 * @see        http://itdapps.ncsu.edu
 * @version    SVN: $Id: Auth.php 189 2007-07-31 19:27:49Z jfaustin@EOS.NCSU.EDU $
 */

class Ot_Plugin_MaintenanceMode extends Zend_Controller_Plugin_Abstract
{
    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        $vr     = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        $layout = Zend_Controller_Action_HelperBroker::getStaticHelper('layout');
               
        $config = Zend_Registry::get('appConfig');
        
        $zcf = Zend_Controller_Front::getInstance();
        
        $role  = (is_null(Ot_Authz::getInstance()->getRole()) ? (string)$config->loginOptions->defaultRole : Ot_Authz::getInstance()->getRole());
        $acl = Zend_Registry::get('acl');

        if ($config->maintenanceMode && !$acl->isAllowed($role, 'admin_maintenance', 'index')) {
            $layout->setLayout('maintenance');
        }
    }
}