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
 * @package    Ot_View_Helper_RegexReplace
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt BSD License
 * @version    SVN: $Id: $
 */

/**
 * This view helper search and replace on strings given a regular expression
 *
 * @package    Ot_View_Helper_RegexReplace
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */

class Ot_View_Helper_RegexReplace extends Zend_View_Helper_Abstract
{

    /**
     * This mirrors the Smarty regexReplace function that allows text replacement
     * in a string based on a perl style regular expression
     *
     * @param mixed $string The string or array of strings to search within
     * @param string $search The regualar expression to match on
     * @param string $replace The string that will replace the matched strings
     * @return The new text-replaced string
     */
    public function regexReplace($string, $search, $replace)
    {
        if (is_array($search)) {
            foreach ($search as $idx => $s) {
                $search[$idx] = $this->_check($s);
            }
        } else {
          $search = $this->_check($search);
        }       
    
        return preg_replace($search, $replace, $string);
    }
    
    /**
     * Used by the regexReplace function to clean up the passed in regular expression
     *
     * @param string $search The regular expression to check
     * @return string The newly cleaned up regular expression
     */
    protected function _check($search)
    {
        if (($pos = strpos($search, "\0")) !== false) {
            $search = substr($search, 0, $pos);
        }
        
        if (preg_match('!([a-zA-Z\s]+)$!s', $search, $match) && (strpos($match[1], 'e') !== false)) {
            /* remove eval-modifier from $search */
            $search = substr($search, 0, -strlen($match[1])) . preg_replace('![e\s]+!', '', $match[1]);
        }
        
        return $search;
    }
}