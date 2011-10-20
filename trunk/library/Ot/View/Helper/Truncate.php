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
 * @package    Ot_View_Helper_Truncate
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt BSD License
 * @version    SVN: $Id: $
 */

/**
 * This helper truncates a string to a certain length if necessary and appending the $etc string
 *
 * @package    Ot_View_Helper_Truncate
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */

class Ot_View_Helper_Truncate extends Zend_View_Helper_Abstract
{

    /**
     * Returns a truncated string at the length specified
     *
     * @param string $string The string to truncate
     * @param int $length The maximum number of characters the string should be
     * @param string $etc The string to append to the end of the truncated string
     * @return string The truncated string
     */
    public function truncate($string, $length = 80, $etc = '...')
    {
        if ($length == 0)
            return '';
    
        if (strlen($string) > $length) {
            
            $length -= min($length, strlen($etc));
            
            $string = preg_replace('/\s+?(\S+)?$/', '', substr($string, 0, $length+1));
            
            return substr($string, 0, $length) . $etc;
                    
        } else {
            return $string;
        }
    }
}