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
 * @package    Ot_View_Helper_ElapsedTime
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt BSD License
 * @version    SVN: $Id: $
 */

/**
 * This returns the elapsed time since a timestamp in nice human relatable string 
 * like 4 days ago, 2 minutes ago, 37 seconds ago, etc.
 *
 * @package    Ot_View_Helper_ElapsedTime
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */

class Ot_View_Helper_ElapsedTime extends Zend_View_Helper_Abstract
{
    
    /**
     * Calculates the elapsed time since the passed in timestamp value.  The string
     * that is returned is like 4 days ago, 2 minutes ago, etc.
     *
     * @param int|string The timestamp from which to get the elapsed time
     */
    public function elapsedTime($timestamp)
    {
        if (empty($timestamp)) {
            return '';
        }
        
        $names = "day, hour, minute, seconds";
    
        $n = explode(",", $names);
        
        if (count($n) < 4) {
            $n = array ("day", "hour", "minute", "seconds");
        }
    
        $difference = time() - intval($timestamp);
    
        $days = floor($difference / (60 * 60 * 24));
        $hours = floor($difference / (60 * 60));
        $minutes = floor($difference / 60);
    
        $s = "";
        $val = 0;
        
        if ($minutes > 0) {
            
            $val = $minutes;
            $s = $n[2];
            
            if ($hours > 0) {
                $val = $hours;
                $s = $n[1];
    
                if ($days > 0) {
                    $val = $days;
                    $s = $n[0];
                }
            }
            
        } else {
            return $difference . " " . $n[3] . " ago";
        }
    
        if ($s == $n[0]) {
            $s = "day";
            
            if ($val > 1) {
                $s .= "s";
            }
        } else if ($s == $n[1]) {
            $s = "hour";
        
            if ($val > 1) {
                $s .= "s";
            }
            
        } else if ($s == $n[2]) {
            $s = "minute";
            
            if ($val > 1) {
                $s .= "s";
            }
        }
    
        return "{$val} {$s} ago";
    }
}