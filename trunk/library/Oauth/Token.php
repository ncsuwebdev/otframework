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
 * @package    OAuth_Token
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * 
 *
 * @package    Oauth_Token
 * @category   OAuth
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */
class Oauth_Token
{
    // Access tokens and request tokens
    public $key;
    public $secret;
    
    /**
     * key = the token
     * secret = the token secret
     */
    public function __construct($key, $secret)
    {
        $this->key = $key;
        $this->secret = $secret;
    }
    
    /**
     * Generates the basic string serialization of a token that a server
     * would respond to request_token and access_token calls with
     */
    public function __toString()
    {
        return "oauth_token="
               . Oauth_Util::urlencodeRfc3986($this->key)
               . "&oauth_token_secret="
               . Oauth_Util::urlencodeRfc3986($this->secret);
    }
}