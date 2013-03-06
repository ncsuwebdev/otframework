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
 * @package    Ot_View_Helper_FormatPhone
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt BSD License
 * @version    SVN: $Id: $
 */

/**
 * Grabs config vars from the registry
 *
 * @package    Ot_View_Helper_VarReg
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */

class Ot_View_Helper_ConfigVar extends Zend_View_Helper_Abstract
{
    
    /**
     * @param var variable to get
     */
    public function configVar($var)
    {
        $vr = new Ot_Config_Register();
        
        $thisVar = $vr->getVar($var);
        
        if (is_null($thisVar)) {
            return '';
        }
        
        return $thisVar->getValue();
    }
}