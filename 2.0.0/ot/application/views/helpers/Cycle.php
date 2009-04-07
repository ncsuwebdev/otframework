<?php

/**
 * This view helper takes any number of args and echos the next one in the order
 * they are given each time the function is called.
 *
 */
class Zend_View_Helper_Cycle extends Zend_View_Helper_Abstract
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
	 * at the current index.  The values are set the first time the function
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