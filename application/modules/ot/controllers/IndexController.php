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
 * @package    Ot_IndexController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Main Admin index controller
 *
 * @package    Ot_IndexController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */
class Ot_IndexController extends Zend_Controller_Action
{
    /**
     * Shows the homepage
     *
     */
    public function indexAction()
    {       
        $this->_helper->pageTitle('ot-index-index:title');
        
        $config = Zend_Registry::get('config');
        $registry = new Ot_Var_Register();
        
        $this->view->appVersion = $config->app->version;
        $this->view->appTitle   = $registry->appTitle->getValue();
        $this->view->otVersion  = Ot_Version::VERSION;
        $this->view->zfVersion  = Zend_Version::VERSION;        
       
        /* The jQuery and jQueryUi library versions are acquired by the
         * javascript for this controller and inserted into the page that way.
         */
    }
}