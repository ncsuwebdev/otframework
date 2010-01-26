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
 * @package    Ot_Validate_Phone
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Validates a US phone number.
 *
 * @package    Ot_Validate_Phone
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */
class Ot_Validate_Phone implements Zend_Validate_Interface
{
    /**
     * Error messages
     *
     * @var unknown_type
     */
    protected $_messages = array();

    /**
     * Checks if value is valid
     *
     * @param mixed $value
     * @return boolean
     */
    public function isValid($value, $userAreaCode = true)
    {
        $pattern1 = "/^[ ]*[(]{0,1}[ ]*[0-9]{3,3}[ ]*[)]{0,1}[-]{0,1}[ ]*[0-9]{3,3}[ ]*[-]{0,1}[ ]*[0-9]{4,4}[ ]*$/";
        $pattern2 = "/^[ ]*[0-9]{3,3}[ ]*[-]{0,1}[ ]*[0-9]{4,4}[ ]*$/";

        if (preg_match($pattern1, $value) || (preg_match($pattern2, $value) && !$userAreaCode)) {
            return true;
        }

        $this->_messages[] = "The format of the phone number is not valid.";
        return false;
    }

    /**
     * Gets error messages
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->_messages;
    }
    /**
     * Gets error messages
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->_messages;
    }
}