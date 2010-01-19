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
 * @package    Ot_Oauth_Server
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Our Oauth server implementation
 *
 * @package    Ot_Oauth_Server
 * @category   Remote OAuth Authentication
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */
class Ot_Oauth_Server extends Oauth_Server
{
	public function __construct($dataStore = null)
	{
		if (is_null($dataStore)) {
			$dataStore = new Ot_Oauth_Datastore();
		}
		
		parent::__construct($dataStore);
		
		$this->addSignatureMethod(new Oauth_Signature_Method_HMACSHA1());
	}
}