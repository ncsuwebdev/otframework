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
 * @package    Ot_FrontController_Plugin_DebugMode
 * @category   Front Controller Plugin
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Allows the application to be run in debug mode, which displays useful information
 * to the user on all requests, like what database is being used currently.
 *
 * @package    Ot_FrontController_Plugin_DebugMode
 * @category   Front Controller Plugin
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 */
class Ot_FrontController_Plugin_DebugMode extends Zend_Controller_Plugin_Abstract
{
    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        // the name "debugMode" is also referred to in the Admin_DebugController,
        // so if you change the cookie name, it needs to be changed there too
        $debugModeCookieName = 'debugMode';
 
        $layout = Zend_Layout::getMvcInstance();
        $view = $layout->getView();
        $viewRenderer = Zend_Controller_Action_HelperBroker::getExistingHelper('ViewRenderer');
        
        if (isset($_COOKIE[$debugModeCookieName]) && (!$request->isXmlHttpRequest() && !$viewRenderer->getNeverRender())) {
            
            $view->headScript()->appendFile($view->baseUrl() . '/public/scripts/ot/jquery.cookie.js');
            $view->headScript()->appendFile($view->baseUrl() . '/public/scripts/ot/debug.js');
           
            $db = Zend_Db_Table::getDefaultAdapter()->getConfig();
            
            $debugInfo = array();
            
            $debugInfo['database'] = array(
                                        'host' => $db['host'],
                                        'dbname' => $db['dbname'],
                                        'username' => $db['username']
                                     );
            
            $view->debugInfo = $debugInfo;
            
            $response = $this->getResponse();
            
            try {
                $result = $view->render('debugHeader.phtml');
            } catch (Exception $e) {
                $result = 'Debug Mode On';
            }
            
            $response->setBody($result . $response->getBody());
            
        }
    }
}