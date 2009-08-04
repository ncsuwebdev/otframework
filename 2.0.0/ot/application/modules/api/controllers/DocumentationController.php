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
 * @package    Api_DocumentationController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Documentation for the API
 *
 * @package    
 * @subpackage Api_DocumentationController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 */
class Api_DocumentationController extends Zend_Controller_Action  
{
    public function indexAction()
    {
        $api = new Ot_Api();
        $allMethods = $api->describe();

        $this->view->api = $allMethods;
        
        $this->_helper->pageTitle('api-documentation-index:title');
    }
}