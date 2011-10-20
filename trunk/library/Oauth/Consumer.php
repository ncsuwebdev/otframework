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
 * @package    OAuth_Consumer
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * 
 *
 * @package    Oauth_Consumer
 * @category   OAuth
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */
class Oauth_Consumer
{
    public $key;
    public $secret;
    public $callbackUrl;
    
    function __construct($key, $secret, $callbackUrl=NULL)
    {
        $this->key         = $key;
        $this->secret      = $secret;
        $this->callbackUrl = $callbackUrl;
    }
    
    function __toString()
    {
        return "OAuth_Consumer[key=$this->key,secret=$this->secret]";
    }
}