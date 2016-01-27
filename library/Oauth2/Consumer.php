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
 * @package    Oauth2_Consumer
 * @category   Library
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 * @package    Oauth2_Consumer
 * @category   Oauth2_
 * @copyright  Copyright (c) 2014 NC State University Office of      
 *             Information Technology
 */

class Oauth2_Consumer
{
    private $client_id;
    private $client_secret;
    private $redirect_uri;
    private $grant_type = 'authorization_code';
    
    public function __construct($client_id, $client_secret, $redirect_uri)
    {
        
    }
}