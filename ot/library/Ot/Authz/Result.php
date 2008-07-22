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
 * @package    Ot_Authz_Result
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Ot_Authz_Result is the result that is returned from an application writers
 * custom module.  A new instance of Ot_Authz_Result is returned from any
 * authorization attempt.
 *
 * @package    Ot_Authz_Result
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 */
class Ot_Authz_Result
{
    /**
     * Whether the result represents a successful authorization attempt
     *
     * @var boolean
     */
    protected $_isValid;

    /**
     * The role(s) returned that the user is a member of
     *
     * @var mixed
     */
    protected $_role;

    /**
     * An array of string reasons why the authorization attempt was unsuccessful
     *
     * If authorization was successful, this should be an empty array.
     *
     * @var array
     */
    protected $_messages;

    /**
     * Constructor
     *
     * @param  boolean $isValid
     * @param  mixed   $role
     * @param  array   $messages
     * @return void
     */
    public function __construct($isValid, $role, array $messages = array())
    {
        $this->_isValid  = (boolean) $isValid;
        $this->_role = $role;
        $this->_messages = $messages;
    }

    /**
     * Returns whether the result represents a successful authorization attempt
     *
     * @return boolean
     */
    public function isValid()
    {
        return $this->_isValid;
    }

    /**
     * Returns the role used in the authorization attempt
     *
     * @return mixed
     */
    public function getRole()
    {
        return $this->_role;
    }

    /**
     * Returns an array of string reasons why the authorization attempt was unsuccessful
     *
     * If authorization was successful, this method returns an empty array.
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->_messages;
    }
}
