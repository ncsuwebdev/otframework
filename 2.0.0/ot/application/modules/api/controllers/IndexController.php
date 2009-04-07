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
 * @package    Api_IndexController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Handles calls to the API
 *
 * @package    
 * @subpackage Api_IndexController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 */
class Api_IndexController extends Zend_Controller_Action  
{
	
	protected $_class = 'Internal_Api';
	
	protected $_parameters = array();
	
	public function init()
	{
        $this->_helper->viewRenderer->setNeverRender();
        $this->_helper->layout->disableLayout();
        		
        $server = new Ot_Oauth_Server();
        
        $req = Oauth_Request::fromRequest();
                
        $remoteAcl = new Ot_Acl('remote');
        
        $config = Zend_Registry::get('config');
        $publicRole = $config->user->defaultRole->val;
		          		
		if ($req->getParameter('oauth_token') != '' || 
			($remoteAcl->has($this->_request->getParam('method')) && !$remoteAcl->isAllowed($publicRole, $this->_request->getParam('method')))) {
			try {
				$server->verifyRequest($req);
			} catch (Exception $e) {

				if ($req->getParameter('oauth_token') != '') {
					$this->_raiseError("OAuth Verification Failed - " . $e->getMessage());
				}
				
				$this->_raiseError("You do not have the proper credentials to remotely access this method.");
			}			
			
			$account = new Ot_Account();
			$token = new Ot_Oauth_Server_Token();
			
			$thisToken = $token->getToken($req->getParameter('oauth_token'));
			if (is_null($thisToken)) {
			    $this->_raiseError('Token not found.');
			}
			
			$thisAccount = $account->find($thisToken->accountId);
			if (is_null($thisAccount)) {
				$this->_raiseError("User with this token not found.");
			}
			
			if (!$remoteAcl->isAllowed($thisAccount->role, $this->_request->getParam('method'))) {
				$this->_raiseError('You do not have the proper credentials to remotely access this method.');
			}
			
			Zend_Auth::getInstance()->getStorage()->write($thisAccount);
		}
		
		$this->_parameters = $req->getParameters();
	}
	
	public function indexAction()
	{
	    $this->_helper->redirector->gotoUrl('/api/documentation');
	}
	
    public function soapAction()
    {
        $server = new SoapServer(null, array('uri' => "soapservice"));
        $server->setClass($this->_class);
        $server->setPersistence(SOAP_PERSISTENCE_SESSION);
        $server->handle();
        
    }
    
    public function xmlAction()
    {
    	$server = new Zend_Rest_Server();
    	$server->setClass($this->_class);
    	$server->handle($this->_parameters); 
    }
    
    public function jsonAction()
    {
    	$server = new Zend_Rest_Server();

    	$server->setClass($this->_class);
    	$server->returnResponse(true);
    	$response = $server->handle($this->_parameters);

    	echo Zend_Json::fromXml($response);
    }
    
    protected function _raiseError($message, $header = 'HTTP/1.1 401 Unauthorized')
    {
    	header($header);
    	die($message);
    }
}
