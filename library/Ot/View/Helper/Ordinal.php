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
 * @package    Ot_View_Helper_Ordinal
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt BSD License
 * @version    SVN: $Id: $
 */

/**
 * This view helper adds the appropriate ordinal suffix to the number or 
 * numeric string passed to the method and returns a string with said ordinal
 * suffix appended.
 *
 * @package    Ot_View_Helper_Ordinal
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */

class Ot_View_Helper_Ordinal extends Zend_View_Helper_Abstract
{

    /**
     * This takes an input value and returns it with it's appropriate ordinal
     * suffix appended (i.e. st, nd, rd, th)
     *
     * @param int $value The number to be ordinalized
     * @return string The value with the ordinal suffix appended
     */
    public function ordinal($number)
    {
        $suffix = "";
        
        if (!is_numeric($number)) {
            return $number;
        }

        // handles three or more digit numbers ending in 11, 12 or 13
        if ($number > 99) {
            $intEndNum = substr($number, -2);
    
            if ($intEndNum >= 11 && $intEndNum <= 13) {
    
                switch ($intEndNum) {
                    case (11 or 12 or 13):
                        $suffix = "th";
                        break;
                }
            }
        }
    
        if ($number >= 21) {
            
            // Handles 21st, 22nd, 23rd, et al
            switch (substr($number, -1)) {
                case 0:
                    $suffix = "th";
                    break;
                case 1:
                    $suffix = "st";
                    break;
                case 2:
                    $suffix = "nd";
                    break;
                case 3:
                    $suffix = "rd";
                    break;
                case (4 || 5 || 6 || 7 || 8 || 9):
                    $suffix = "th";
                    break;
            }
        } else {
            // handles 0th to 20th
            switch ($number) {
                case 1:
                    $suffix = "st";
                    break;
                case 2:
                    $suffix = "nd";
                    break;
                case 3:
                    $suffix = "rd";
                    break;
                case 0:
                case 4:
                case 5:
                case 6:
                case 7:
                case 8:
                case 9:
                case 10:
                case 11:
                case 12:
                case 13:
                case 14:
                case 15:
                case 16:
                case 17:
                case 18:
                case 19:
                case 20:
                    $suffix = "th";
                    break;
            }
        }
    
        $ret  =  $number + "";
        $ret .= $suffix;
    
        return $ret;
    }
}