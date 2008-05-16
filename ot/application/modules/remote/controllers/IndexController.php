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
 * @package    Remote_IndexController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Information Technology Division
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @see        http://itdapps.ncsu.edu
 * @version    SVN: $Id: IndexController.php 197 2007-08-01 15:17:51Z gplocke@EOS.NCSU.EDU $
 */

/**
 * remote access controller
 *
 * @package    
 * @subpackage Remote_IndexController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Information Technology Division
 */
class Remote_IndexController extends Internal_Controller_Action  
{
    /**
     *
     */
    public function soapAction()
    {
    	Zend_Loader::loadClass('Api');
    	
        $server = new SoapServer(null, array('uri' => "soapservice"));
        $server->setClass('Api');
        $server->setPersistence(SOAP_PERSISTENCE_SESSION);
        $server->handle();
        
        $this->_helper->viewRenderer->setNeverRender();
    }
}
