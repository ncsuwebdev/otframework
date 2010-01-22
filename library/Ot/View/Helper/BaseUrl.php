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
 * @package    Ot_View_Helper_BaseUrl
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * This view helper returns the baseUrl of the application
 *
 * @package    Ot_View_Helper_BaseUrl
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */

class Ot_View_Helper_BaseUrl extends Zend_View_Helper_Abstract
{   
    /**
     * Takes any number of arguments and echoes the value in the array of values
     * at the current index.  The values are set the first time the function
     * is called.
     *
     * @param mixed Any number of arguments to use as cycle values
     */
    public static function baseUrl()
    {
        return Zend_Controller_Front::getInstance()->getBaseUrl();
    }
}