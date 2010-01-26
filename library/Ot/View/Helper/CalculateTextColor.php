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
 * @package    Ot_View_Helper_CalculateTextColor
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt BSD License
 * @version    SVN: $Id: $
 */

/**
 * @package    Ot_View_Helper_CalculateTextColor
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */

class Ot_View_Helper_CalculateTextColor extends Zend_View_Helper_Abstract
{
    
    /**
     * Return the correct text display color for the given HEX background color
     *
     * @param string $color The hex value to get the text display color for
     */
    public function calculateTextColor($color)
    {
        
        if (empty($color) || strlen($color) < 6) {
            return '#000000';
        }
        
        $color =  (substr($color, 0, 1) == "#") ? substr($color, 1, 7) : $color;
    
        $r = hexdec(substr($color, 0, 2));
        $g = hexdec(substr($color, 2, 2));
        $b = hexdec(substr($color, 4, 2)); 
        
        $hsl = $this->_rgbToHsl(array($r, $g, $b));
        
        return ($hsl[2] > 0.55) ? '#000000' : '#FFFFFF';
    }
    
    /**
     * Convert an RGB value to HSL
     *
     * @param $rgb array of red, green, blue values
     * @return array of hue, saturation, luminance values
     */
    private function _rgbToHsl($rgb)
    {
        $clrR = $rgb[0];
        $clrG = $rgb[1];
        $clrB = $rgb[2];
        
        $clrMin = min($clrR, $clrG, $clrB);
        $clrMax = max($clrR, $clrG, $clrB);
        $deltaMax = $clrMax - $clrMin;
        
        $l = ($clrMax + $clrMin) / 510;
        
        if (0 == $deltaMax) {
            $h = 0;
            $s = 0;
        } else {
            
            if (0.5 > $l) {
                $s = $deltaMax / ($clrMax + $clrMin);
            } else {
                $s = $deltaMax / (510 - $clrMax - $clrMin);
            }
    
            if ($clrMax == $clrR) {
                $h = ($clrG - $clrB) / (6.0 * $deltaMax);
            } else if ($clrMax == $clrG) {
                $h = 1/3 + ($clrB - $clrR) / (6.0 * $deltaMax);
            } else {
                $h = 2 / 3 + ($clrR - $clrG) / (6.0 * $deltaMax);
            }
    
            if (0 > $h) $h += 1;
            if (1 < $h) $h -= 1;
        }
        return array($h, $s, $l);
    }
}