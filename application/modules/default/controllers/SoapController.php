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
 * @package    Cyclone (Default)
 * @subpackage IndexController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Information Technology Division
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @author     Jason Austin <jason_austin@ncsu.edu>
 * @author     Garrison Locke <garrison_locke@ncsu.edu>
 * @see        http://itdapps.ncsu.edu
 * @version    SVN: $Id: IndexController.php 197 2007-08-01 15:17:51Z gplocke@EOS.NCSU.EDU $
 */

/**
 * SOAP access controller
 *
 * @package    
 * @subpackage SoapController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Information Technology Division
 */
class SoapController extends Internal_Controller_Action  
{
    /**
     *
     */
    public function indexAction()
    {
        $this->_helper->viewRenderer->setNeverRender();
        $this->_helper->layout->disableLayout();
        
        Zend_Loader::loadClass('Ot_Api');
        
        $server = new SoapServer(null, array('uri' => "groupr"));
        $server->setClass('Ot_Api');
        $server->setPersistence(SOAP_PERSISTENCE_SESSION);
        $server->handle();   
    }
}
