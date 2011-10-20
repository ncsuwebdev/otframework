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
 * @package    Oauth_Datastore
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * 
 *
 * @package    Oauth_Datastore
 * @category   OAuth
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */
interface Oauth_Datastore_Interface
{
    public function lookupToken($consumer, $tokenType, $token);
    
    public function lookupConsumer($consumerKey);
    
    public function lookupNonce($consumer, $token, $nonce, $timestamp);
    
    public function newToken($consumer, $type = "request");
    
    public function newRequestToken($consumer);
    
    public function newAccessToken($token, $consumer);
}