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
 * @package    Admin_IndexController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Main Admin index controller
 *
 * @package    Admin_IndexController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 */
class Admin_IndexController extends Zend_Controller_Action 
{
    /**
     * shows the homepage
     *
     */
    public function indexAction()
    {       
        $this->_helper->pageTitle('admin-index-index:title');
        $config = Zend_Registry::get('config');
        
        if (!empty($config->app->version)) {
            $this->view->appVersion = $config->app->version;
        } else {
        	$this->view->appVersion = "Unknown";
        };
        
        $this->view->appTitle = $config->user->appTitle;
        $this->view->otVersion = Ot_Version::VERSION;
        $this->view->zfVersion = Zend_Version::VERSION;        
       
        // the jQuery and jQueryUi library versions are acquired by the javascript
        // for this controller and inserted into the page that way
    }
}
