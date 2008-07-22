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
 * @package    Ot_Authz_Adapter_Interface
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Interface to build all Authorizatino Adapters
 *
 * @package    Ot_Authz_Adapter_Interface
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 */
interface Ot_Authz_Adapter_Interface
{

	/**
	 * Flag to tell the app where the authorization is managed.  If set to true,
	 * the application will use its own interface to interact with the adapter.
	 *
	 * @return boolean
	 */
    public static function manageLocally();

}