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
 * @package    Ot_FrontController_Plugin_Input
 * @category   Front Controller Plugin
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Does a base filtering of all parameters passed through HTTP headers
 *
 * @package    Ot_FrontController_Plugin_Input
 * @category   Front Controller Plugin
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */
class Ot_FrontController_Plugin_Input extends Zend_Controller_Plugin_Abstract
{
    /**
     * Processes the input then sets a registry variable for get and 
     * post filtered data.
     *
     * @param Zend_Controller_Request_Abstract $request
     */
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        $filterOptions = array(
            '*' => array(
                'StringTrim',
                'StripTags',
            ),
        );
        
        $getFilter = new Zend_Filter_Input($filterOptions, array(), array_merge($_GET, $request->getParams()));
        $postFilter = new Zend_Filter_Input($filterOptions, array(), $_POST);
        
        Zend_Registry::set('getFilter', $getFilter);
        Zend_Registry::set('postFilter', $postFilter);        
    }
}