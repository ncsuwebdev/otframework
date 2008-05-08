<?php
/**
 * Cyclone
 *
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
 * @package    Cyclone
 * @subpackage Internal_Validate_Mac
 * @category   vaidator
 * @copyright  Copyright (c) 2007 NC State University Information Technology Division
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @author     Jason Austin <jason_austin@ncsu.edu>
 * @author     Garrison Locke <garrison_locke@ncsu.edu>
 * @see        http://itdapps.ncsu.edu
 * @version    SVN: $Id: Mac.php 158 2007-07-20 13:44:00Z jfaustin@EOS.NCSU.EDU $
 */

/**
 * validates a mac address
 *
 * @package    Cyclone
 * @subpackage Internal_Validate_Mac
 * @category   vaidator
 * @copyright  Copyright (c) 2007 NC State University Information Technology Division
 */
class Ot_Validate_Mac implements Zend_Validate_Interface
{
    /**
     * error messages
     *
     * @var unknown_type
     */
    protected $_messages = array();

    /**
     * checks if value is valid
     *
     * @param mixed $value
     * @return boolean
     */
    public function isValid($value)
    {
        $requirement = preg_match('/^([0-9A-F]{2})([^0-9A-F]*[0-9A-F]{2}){5}$/i', $value);

        if (!$requirement) {
            $this->_messages[] = "Invalid MAC Address.";
            return false;
        }

        return true;
    }

    /**
     * gets error messages
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->_messages;
    }
    /**
     * gets error messages
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->_messages;
    }
}

?>

