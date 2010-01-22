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
 * @package    Ot_Validate_UserId
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Validates an NCSU unity ID.
 *
 * @package    Ot_Validate_UserId
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */
class Ot_Validate_UserId implements Zend_Validate_Interface
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
    public function isValid($value)
    {
        $unityId = preg_match('/^[a-z]{1}[a-z0-9]{2,7}$/', $value);

        if (!$unityId) {
            $this->_messages[] = "User ID is not a valid unity ID.";
            return false;
        }

        return true;
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