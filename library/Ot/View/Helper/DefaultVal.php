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
 * @package    Ot_View_Helper_DefaultVal
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt BSD License
 * @version    SVN: $Id: $
 */

/**
 * This view helper returns a value, or a default value if the value is empty.
 *
 * @package    Ot_View_Helper_DefaultVal
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */

class Ot_View_Helper_DefaultVal extends Zend_View_Helper_Abstract
{
    /**
     * Checks if the passed $val is empty or not.  If it is not,
     * it returns the $val.  If it is, it returns the translation
     * of $alt;
     *
     * @param string $val
     * @param string $alt
     * @return string
     */
    public function defaultVal($val, $alt = 'msg-info-none')
    {
        $val = trim($val);
        
        if ($val != '') {
            return $val;
        }
        
        $translate = Zend_Registry::get('Zend_Translate');
        
        return '<span class="muted">' . $translate->translate($alt) . '</span>';
    }
}