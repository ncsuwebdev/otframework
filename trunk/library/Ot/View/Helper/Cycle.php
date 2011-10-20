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
 * @package    Ot_View_Helper_Cycle
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt BSD License
 * @version    SVN: $Id: $
 */

/**
 * This view helper takes any number of args and echos the next one in the order
 * they are given each time the function is called.
 *
 * @package    Ot_View_Helper_Cycle
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */

class Ot_View_Helper_Cycle extends Zend_View_Helper_Abstract
{

    /**
     * The values to cycle through
     *
     * @var array
     */
    protected $_values = array();
    
    /**
     * The current index in the array to display
     *
     * @var int
     */
    protected $_currentIndex = 0;
    
    /**
     * Takes any number of arguments and echoes the value in the array of values
     * at the current index. The values are set the first time the function
     * is called.
     *
     * @param mixed Any number of arguments to use as cycle values
     */
    public function cycle()
    {
        if (empty($this->_values)) {
            $this->_values = func_get_args();
        }
        
        echo $this->_values[$this->_currentIndex];
        
        $this->_currentIndex++;
        
        if ($this->_currentIndex == count($this->_values)) {
            $this->_currentIndex = 0;
        }
    }
}