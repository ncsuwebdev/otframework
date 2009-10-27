<?php

/**
 * This helper truncates a string to a certain length if necessary and appending the $etc string
 *
 */
class Zend_View_Helper_Truncate extends Zend_View_Helper_Abstract
{

	/**
	 * Returns a truncated string at the length specified
	 *
	 * @param string $string The string to truncate
	 * @param int $length The maximum number of characters the string should be
	 * @param string $etc The string to append to the end of the truncated string
	 * @return string The truncated string
	 */
	public function truncate($string, $length = 80, $etc = '...')
    {
	    if ($length == 0)
	        return '';
	
	    if (strlen($string) > $length) {
	        
	    	$length -= min($length, strlen($etc));
	        
            $string = preg_replace('/\s+?(\S+)?$/', '', substr($string, 0, $length+1));
	        
	        return substr($string, 0, $length) . $etc;
	                
	    } else {
	        return $string;
	    }
    }
}