<?php
/**
 * Ot
 *
 * LICENSE
 *
 * This license is governed by United States copyright law, and with respect to matters
 * of tort, contract, and other causes of action it is governed by North Carolina law,
 * without regard to North Carolina choice of law provisions.  The forum for any dispute
 * resolution shall be in Wake County, North Carolina.
 *
 * Redistribution and use in source and binary forms, with or without modification, are
 * permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice, this list
 *    of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice, this
 *    list of conditions and the following disclaimer in the documentation and/or other
 *    materials provided with the distribution.
 *
 * 3. The name of the author may not be used to endorse or promote products derived from
 *    this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR "AS IS" AND ANY EXPRESS OR IMPLIED
 * WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
 * AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE AUTHOR BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF
 * ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    Ot
 * @subpackage Ot_Authz_Result
 * @category   Authorization Interface
 * @copyright  Copyright (c) 2007 NC State University Information Technology Division
 * @license    BSD License
 * @author     Jason Austin <jason_austin@ncsu.edu>
 * @author     Garrison Locke <garrison_locke@ncsu.edu>
 * @see        http://itdapps.ncsu.edu
 * @version    SVN: $Id: Result.php 17 2007-04-18 13:56:28Z jfaustin@EOS.NCSU.EDU $
 */


/**
 * Ot_Authz_Result is the result that is returned from an application writers
 * custom module.  A new instance of Ot_Authz_Result is returned from any
 * authorization attempt.
 *
 * @package    Ot
 * @subpackage Ot_Authz_Result
 * @category   Authorization Interface
 * @copyright  Copyright (c) 2007 NC State University Information Technology Division
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
