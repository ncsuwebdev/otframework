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
 * @package    Ot_Authz_Interface
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Ot_Authz_Interface is an interface that an application writer can implement
 * to create a custom authorization module.
 *
 * @package    Ot_Authz_Interface
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
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