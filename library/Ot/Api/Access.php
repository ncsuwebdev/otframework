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
 * @package    Ot_Api_Access
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    BSD License
 * @version    SVN: $Id: $
 */

/**
 * Provides an interface for remote procedure calls
 *
 * @package   Ot_Api_Access
 * @category  Library
 * @copyright Copyright (c) 2007 NC State University Office of Information Technology
 *
 */
class Ot_Api_Access
{
    const API_SOAP = 1;
    const API_JSON = 2;
    const API_XML  = 3;
    const API_REST = 4;
    
    protected $_message = '';
    
    public function validate(Oauth_Request $request, $method)
    {
        $server = new Ot_Model_DbTable_OauthServer();
                        
        $remoteAcl = new Ot_Acl('remote');

        $registry = new Ot_Config_Register();
        $publicRole = $registry->defaultRole->getValue();
        
        if ($request->getParameter('oauth_token') != ''
            || ($remoteAcl->has($method) && !$remoteAcl->isAllowed($publicRole, $method))) {
            try {
                $server->verifyRequest($request);
            } catch (Exception $e) {
                
                if ($request->getParameter('oauth_token') != '') {
                    $this->_message = "OAuth Verification Failed - " . $e->getMessage();
                    return false;
                }
                
                $this->_message = "You do not have the proper signed credentials to remotely access this method.";
                return false;
            }           
            
            $account = new Ot_Model_DbTable_Account();
            $token = new Ot_Model_DbTable_OauthServerToken();
            
            $thisToken = $token->getToken($request->getParameter('oauth_token'));
            if (is_null($thisToken)) {
                $this->_message = 'Token not found.';
                return false;
            }
            
            $thisAccount = $account->find($thisToken->accountId);
            if (is_null($thisAccount)) {
                $this->_message = "User with this token not found.";
                return false;
            }
            
            foreach($thisAccount->role as $r) {
                if(!$remoteAcl->isAllowed($r, $method)) {
                    $this->_message = 'You do not have the proper credentials to remotely access this method.';
                    return false;
                }
            }
            
            /*
            if (!$remoteAcl->isAllowed($thisAccount->role, $method)) {
                $this->_message = 'You do not have the proper credentials to remotely access this method.';
                return false;
            }
            */
            
            Zend_Auth::getInstance()->getStorage()->write($thisAccount);
        }

        return true;
    }
    
    public function getMessage()
    {
        return $this->_message;
    }
    
    /* An error occured. Output the error formatted based on the page type (html, json, xml, ...) and dies right after */
    public function raiseError($message, $type = self::API_REST, $header = 'HTTP/1.1 401 Unauthorized')
    {
        
        if ($type == self::API_SOAP) {
            return new SoapFault('Error', $message);
        } elseif($type == self::API_JSON) {
        	if (!headers_sent()) {
        	   header($header, true, 401);
        	}
        	$server = new Zend_Json_Server();
        	echo $server->fault($message, 401);
        	return;
        } elseif($type == self::API_XML) { // output in xml
            $rest = new Zend_Rest_Server();
            $result = $rest->fault(
                new Zend_Rest_Server_Exception($message),
                401
            );
            if (!headers_sent()) {
            	header('Content-Type: text/xml', true);
            	$headers = $rest->getHeaders();
                foreach ($headers as $header) {
                    header($header);
                }
            }
            echo $result->saveXML();
            return;
        } else {
            header($header);
            echo $message;
            return;
        }
    }
}