<?php
/**
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file _LICENSE.txt.
 *
 * This license is also available via the world-wide-web at
 * http://itdapps.ncsu.edu/bsd.txt
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to itappdev@ncsu.edu so we can send you a copy immediately.
 *
 * @package    Ot_Api_Soap
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    BSD License
 * @version    SVN: $Id: $
 */

/**
 * Provides an interface for remote procedure calls
 *
 * @package   Ot_Api_Soap
 * @category  Library
 * @copyright Copyright (c) 2007 NC State University Office of Information Technology
 *
 */
class Ot_Api_Soap
{
    protected $_authorized = false;
    
    protected $_apiClass = 'Internal_Api';
    
    public function __call($name, $arguments)
    {
        if ($this->_authorized) {
            try {
                return call_user_func_array($this->_apiClass . '::' . $name, $arguments);
            } catch (Exception $e) {
                return new SoapFault($e->getCode(), $e->getMessage());
            }
        }
        
        return new SoapFault('Access denied',
            $name . 'The SoapOauth header must be set to access this application API');
    }
    
    /**
     * Enter description here...
     *
     * @param mixed $arg
     * @return boolean
     */
    public function SoapOauth($arg)
    {
        $headerParameters = Oauth_Request::splitHeader($arg);
        
        $req = Oauth_Request::fromRequest(null, null, $headerParameters);
        
        $headers = Oauth_Request::getHeaders();
        
        $method = '';
        foreach ($headers as $key => $value) {
            if (strtolower($key) == 'soapaction') {
                $method = preg_replace('/^[^#]*#/', '', str_replace('"', '', $value));
            }
        }

        $access = new Ot_Model_DbTable_ApiAccess();
        
        if ($method == '') {
            return $access->raiseError('Remote access method not found', Ot_Api_Access::API_SOAP);
        }
        
        if (!$access->validate($req, $method)) {
            return $access->raiseError($access->getMessage(), Ot_Model_DbTable_ApiAccess::API_SOAP);
        }
        
        $this->_authorized = true;
        
        return true;

    }
}