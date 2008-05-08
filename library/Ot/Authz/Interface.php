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
 * @subpackage Ot_Authz_Interface
 * @category   Authorization Interface
 * @copyright  Copyright (c) 2007 NC State University Information Technology Division
 * @license    BSD License
 * @author     Jason Austin <jason_austin@ncsu.edu>
 * @author     Garrison Locke <garrison_locke@ncsu.edu>
 * @see        http://itdapps.ncsu.edu
 * @version    SVN: $Id: Interface.php 17 2007-04-18 13:56:28Z jfaustin@EOS.NCSU.EDU $
 */

/**
 * Ot_Authz_Interface is an interface that an application writer can implement
 * to create a custom authorization module.
 *
 * @package    Ot
 * @subpackage Ot_Authz_Interface
 * @category   Authorization Interface
 * @see        Ot_Authz
 * @copyright  Copyright (c) 2007 NC State University Information Technology Division
 */
interface Ot_Authz_Interface
{
    /**
     * Performs an authorization attempt
     *
     * @return Ot_Authz_Result
     */
    public function authorize();
}
?>