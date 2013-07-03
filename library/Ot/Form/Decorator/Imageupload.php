<?php
/**
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file _LICENSE.txt.
 *
 * This license is also available via the world-wide-web at
 * http://itdapps.ncsu.edu/bsd.txt
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to itappdev@ncsu.edu so we can send you a copy immediately.
 *
 * @package    Ot_Form_Tempate
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    BSD License
 * @version    SVN: $Id: $
 */

/**
 * Create default form templates from commonly used forms
 *
 * @package   Ot_Form_Template
 * @category  Library
 * @copyright Copyright (c) 2007 NC State University Office of Information Technology
 *
 */
class Ot_Form_Decorator_Imageupload extends Zend_Form_Decorator_Abstract
{
    public function render($content)
    {        
        $output = '';
        
        if ($this->getOption('src') != '') {
            $output = '<img id="' 
                    . $this->getOption('id') 
                    . '" src="' 
                    . $this->getOption('src') 
                    . '" alt="'
                    . $this->getOption('alt')
                    . '" style="display:block'
                    . '" /><br /><br />Change To:<br />';
        }
        
        $content = explode('<input', $content);
        
        $firstElement = array_shift($content);
        
        $ret = $firstElement . $output . '<input ' . implode('<input ', $content);
        
        return $ret;
    }
}