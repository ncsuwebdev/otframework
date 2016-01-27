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
 * @package    Oauth2_Request
 * @category   Library
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 * @package    Oauth2_Request
 * @category   Oauth2_
 * @copyright  Copyright (c) 2014 NC State University Office of      
 *             Information Technology
 */

class Oauth2_Request
{
    private $_params;
    private $_httpMethod;
    private $_httpUrl;
    
    public function __construct($httpMethod, $httpUrl, $params = NULL)
    {
        $this->_params      = !is_null($params) ? $params : array();
        $this->_httpMethod  = $httpMethod;
        $this->_httpUrl     = $httpUrl;
    }
}