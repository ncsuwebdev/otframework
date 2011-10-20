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
 * @package    Ot_View_Helper_DateFormat
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt BSD License
 * @version    SVN: $Id: $
 */

/**
 * This view helper aids in formatting date strings and timestamps for nice output
 * using the strftime formatting guidelines
 *
 * @package    Ot_View_Helper_DateFormat
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */

class Ot_View_Helper_DateFormat extends Zend_View_Helper_Abstract
{
    
    /**
     * Converts a timestamp or a date string to the specified format or creates
     * a string of the speicified format with the current timestamp.
     *
     * @param string $string The date string or timestamp to convert to a formatted date
     * @param string $format The strftime formatting string to use for formatting
     * @param string $defaultDate The default date to use if string is blank (defaults to '' which is now())
     * @return string the formatted time or false if a timestamp couldn't be created
     */
    public function dateFormat($string, $format = '%b %e, %Y', $defaultDate = '')
    {
        if ($string != '') {
            $timestamp = $this->_makeTimestamp($string);
        } elseif ($defaultDate != '') {
            $timestamp = $this->_makeTimestamp($defaultDate);
        } else {
            return false;
        }
        
        if (DIRECTORY_SEPARATOR == '\\') {
            
            $winFrom = array('%D',       '%h', '%n', '%r',          '%R',    '%t', '%T');
            $winTo   = array('%m/%d/%y', '%b', "\n", '%I:%M:%S %p', '%H:%M', "\t", '%H:%M:%S');
            
            if (strpos($format, '%e') !== false) {
                $winFrom[] = '%e';
                $winTo[]   = sprintf('%\' 2d', date('j', $timestamp));
            }
            
            if (strpos($format, '%l') !== false) {
                $winFrom[] = '%l';
                $winTo[]   = sprintf('%\' 2d', date('h', $timestamp));
            }
            
            $format = str_replace($winFrom, $winTo, $format);
        }
        
        return strftime($format, $timestamp);
    }
    
    /**
     * Used to make a timestamp from a string regardless of it's format
     *
     * @param string $string The string to convert to a timestamp
     * @return int The converted timestamp
     */
    protected function _makeTimestamp($string)
    {
        if (empty($string)) {
            // use "now":
            $time = time();
        } elseif (preg_match('/^\d{14}$/', $string)) {
            // it is mysql timestamp format of YYYYMMDDHHMMSS?            
            $time = mktime(
                substr($string, 8, 2),
                substr($string, 10, 2),
                substr($string, 12, 2),
                substr($string, 4, 2),
                substr($string, 6, 2),
                substr($string, 0, 4)
            );
            
        } elseif (is_numeric($string)) {
            // it is a numeric string, we handle it as timestamp
            $time = (int)$string;
            
        } else {
            // strtotime should handle it
            $time = strtotime($string);
            if ($time == -1 || $time === false) {
                // strtotime() was not able to parse $string, use "now":
                $time = time();
            }
        }
        
        return $time;
    }
}