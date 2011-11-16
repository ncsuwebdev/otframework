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
 * @package    Ot_Exception
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    BSD License
 * @version    SVN: $Id: $
 */

/**
 * Custom exception handler
 *
 * @package   Ot_Exception
 * @category  Library
 * @copyright Copyright (c) 2007 NC State University Office of Information Technology
 *
 */
class Ot_Exception extends Exception
{
    /**
     * Title of the exception
     *
     * @var string
     */
    protected $_title = '';
    
    /**
     * 
     * @param $message[optional]
     * @param int $code - the response status code
     */
    public function __construct ($message, $code = 400) {
    	if(!headers_sent()) {
    	   header('Status: '. (int)$code, false, (int)$code);
    	}
        parent::__construct($message, $code);
    }
    
    /**
     * Gets the exception title
     *
     * @return unknown
     */
    public function getTitle()
    {
        return $this->_title;
    }
}