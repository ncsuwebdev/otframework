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
 * @package    Account_OauthController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 * @license    BSD License
 * @version    SVN: $Id: $
 */

/**
 * Allows the user register and grant access to OAuth enabled apps
 *
 * @package    Oauth_ServerController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 *
 */
class Ot_OauthserverController extends Zend_Controller_Action 
{    
    
    public function requestTokenAction()
    {        
        $this->_helper->viewRenderer->setNeverRender();
        $this->_helper->layout->disableLayout();
        
        $oauthServer = new Ot_Oauth_Server();
            	
    	try {
			$req = Oauth_Request::fromRequest();
			
			$result = $oauthServer->fetchRequestToken($req);
			
			header('HTTP/1.1 200 OK');
			header('Content-Length: ' . strlen($result));
			header('Content-Type: application/x-www-form-urlencoded');
			echo $result;
    	} catch (Exception $e) {
			header('HTTP/1.1 401 Unauthorized');  		
    		echo $e->getMessage();
    	}
    }
        
    public function accessTokenAction()
    {
        $this->_helper->viewRenderer->setNeverRender();
        $this->_helper->layout->disableLayout();
        
        $oauthServer = new Ot_Oauth_Server();
            	
		try {	
			$req = Oauth_Request::fromRequest();
			$result = $oauthServer->fetchAccessToken($req);
			
			header('HTTP/1.1 200 OK');
			header('Content-Length: ' . strlen($result));
			header('Content-Type: application/x-www-form-urlencoded');
			echo $result;
						
		} catch (Exception $e) {
			header('HTTP/1.1 401 Unauthorized'); 
			echo $e->getMessage();
		}        
    }    
    	
	public function authorizeAction()
	{
		$this->_helper->pageTitle('oauth-server-authorize:title');
		
		$get = Zend_Registry::get('getFilter');
		
		if (!isset($get->oauth_token)) {
			throw new Ot_Exception_Input('The oauth_token is not set in the query string.');
		}
		
		$st = new Ot_Oauth_Server_Token(); 
		
		$token = $st->getToken($get->oauth_token);
		
		if (is_null($token)) {
			throw new Ot_Exception_Data('The passed request token was not found.');
		}
		
		$consumer = new Ot_Oauth_Server_Consumer();
		
		$thisConsumer = $consumer->find($token->consumerId);
		if (is_null($thisConsumer)) {
			throw new Ot_Exception_Data('The consumer associated with your request token no longer exists');
		}
		
		$existingAccessToken = $st->getTokenByAccountAndConsumer(Zend_Auth::getInstance()->getIdentity()->accountId, $thisConsumer->consumerId, 'access');
		if (!is_null($existingAccessToken)) {
			$st->removeToken($get->oauth_token);
			$this->_helper->redirector->gotoRoute(array('controller' => 'oauthserver', 'action' => 'already-authorized', 'consumerId' => $thisConsumer->consumerId), 'ot', true);
		}
				
		$this->view->token = $token;
		$this->view->consumer = $thisConsumer;

        $form = new Zend_Form();
        $form->setAttrib('id', 'oauthAccess');
        
        $grant = $form->createElement('submit', 'grantButton', array('label' => 'Grant access to ' . $thisConsumer->name . '...'));
        $grant->setDecorators(array(
                   array('ViewHelper', array('helper' => 'formSubmit'))
                 ));
        
        $deny = $form->createElement('submit', 'denyButton', array('label' => 'No Way!  Deny access...'));
        $deny->setDecorators(array(
                   array('ViewHelper', array('helper' => 'formSubmit'))
                ));
                             
        $form->addElements(array($grant, $deny)); 		
		
		if ($this->_request->isPost() && $form->isValid($_POST)) {
			
			if ($form->getValue('grantButton') != '') {

				$result = $st->authorizeToken($get->oauth_token, Zend_Auth::getInstance()->getIdentity()->accountId);
				
				if (is_null($result)) {
					throw new Ot_Exception_Data('Token was not authorized because it was not found.');
				}
				
				$this->_helper->redirector->gotoRoute(array('controller' => 'oauthserver', 'action' => 'grant', 'oauth_token' => $get->oauth_token), 'ot', true);
			} else {
				$st->removeToken($get->oauth_token);
				
				$this->_helper->redirector->gotoRoute(array('controller' => 'oauthserver', 'action' => 'deny', 'consumerId' => $thisConsumer->consumerId), 'ot', true);
			}
		}
		
		$this->view->form = $form;
	}
	
	public function grantAction()
	{
		$this->_helper->pageTitle('oauth-server-grant:title');
		
		$get = Zend_Registry::get('getFilter');
		
		if (!isset($get->oauth_token)) {
			throw new Ot_Exception_Input('The oauth_token is not set in the query string.');
		}
		
		$st = new Ot_Oauth_Server_Token();
		
		$token = $st->getToken($get->oauth_token);
		
		if (is_null($token)) {
			throw new Ot_Exception_Data('The passed request token was not found.');
		}
		
		if ($token->authorized != 1) {
			throw new Ot_Exception_Data('The auth token passed is not authorized.');
		}
		
		if ($token->accountId != Zend_Auth::getInstance()->getIdentity()->accountId) {
			throw new Ot_Exception_Data('This token does not belong to the user selected.');
		}
		
		$consumer = new Ot_Oauth_Server_Consumer();
		
		$thisConsumer = $consumer->find($token->consumerId);
		if (is_null($thisConsumer)) {
			throw new Ot_Exception_Data('The consumer associated with your request token no longer exists');
		}
		
		$this->view->token = $token;
		$this->view->consumer = $thisConsumer;
		
		if ($thisConsumer->callbackUrl != '') {
				
			$url = $thisConsumer->callbackUrl;
			
			if (preg_match('/\?/', $url)) {
				$url .= '&';
			} else {
				$url .= '?';
			}
			
			$url .= 'oauth_token=' . $get->oauth_token;
			
			$this->view->callbackUrl = $url;
		}		
	}
	
	public function denyAction()
	{
		$this->_helper->pageTitle('oauth-server-deny:title');		
		
		$get = Zend_Registry::get('getFilter');
		
		if (!isset($get->consumerId)) {
			throw new Ot_Exception_Input('consumerId not set in query string.');
		}
		
		$consumer = new Ot_Oauth_Server_Consumer();
		
		$thisConsumer = $consumer->find($get->consumerId);
		if (is_null($thisConsumer)) {
			throw new Ot_Exception_Data('The consumer no longer exists');
		}
		
		$this->view->consumer = $thisConsumer;		
	}
	
	public function alreadyAuthorizedAction()
	{
		$this->_helper->pageTitle('oauth-server-alreadyAuthorized:title');
		
		$get = Zend_Registry::get('getFilter');
		
		if (!isset($get->consumerId)) {
			throw new Ot_Exception_Input('The consumerId is not set in the query string.');
		}

		$consumer = new Ot_Oauth_Server_Consumer();
		
		$thisConsumer = $consumer->find($get->consumerId);
		if (is_null($thisConsumer)) {
			throw new Ot_Exception_Data('The consumer associated with your request token no longer exists');
		}
		
		$st = new Ot_Oauth_Server_Token();
		
		$existingAccessToken = $st->getTokenByAccountAndConsumer(Zend_Auth::getInstance()->getIdentity()->accountId, $thisConsumer->consumerId, 'access');
		if (is_null($existingAccessToken)) {
			throw new Ot_Exception_Data('You do not have an existing access token for this consumer');
		}	
		
		$this->view->consumer = $thisConsumer;
	}
	
	public function generateTokenAction()
	{
		$get = Zend_Registry::get('getFilter');
		
		if (!isset($get->consumerId)) {
			throw new Ot_Exception_Input('consumerId not set in query string.');
		}
		
		$consumer = new Ot_Oauth_Server_Consumer();
		
		$thisConsumer = $consumer->find($get->consumerId);
		if (is_null($thisConsumer)) {
			throw new Ot_Exception_Data('consumer not found.');
		}
		
		if ($thisConsumer->registeredAccountId != Zend_Auth::getInstance()->getIdentity()->accountId) {
			throw new Ot_Exception_Access('You are not allowed to edit other users applications.');
		}
		
		$this->view->consumer = $thisConsumer;
				
		$st = new Ot_Oauth_Server_Token();
		
		$existingAccessToken = $st->getTokenByAccountAndConsumer(Zend_Auth::getInstance()->getIdentity()->accountId, $thisConsumer->consumerId, 'access');
		if (!is_null($existingAccessToken)) {
			throw new Ot_Exception_Data('You already have an existing access token for this consumer.  Remove that token to create a new one.');
		}	
				
		$this->_helper->pageTitle('oauth-server-generateToken:title');
		
		$form = Ot_Form_Template::delete('genereateToken', 'Generate Access Token/Secret');
		
		$this->view->form = $form;
		
		if ($this->_request->isPost() && $form->isValid($_POST)) {
			
			$oauthDs = new Ot_Oauth_Datastore();
			
			$requestToken = $oauthDs->newToken($thisConsumer, 'request', Zend_Auth::getInstance()->getIdentity()->accountId);
			
			$thisToken = $st->getTokenByAccountAndConsumer(Zend_Auth::getInstance()->getIdentity()->accountId, $thisConsumer->consumerId, 'request');
			$thisToken->authorized = 1;
			$thisToken->save();
			
			$accessToken = $oauthDs->newAccessToken($requestToken, $thisConsumer);
			
			$token = explode('&', $accessToken);
			$parsed = array();
			
			foreach ($token as $t) {
				$key = explode('=', $t);
				
				$parsed[$key[0]] = $key[1];
			}
			
			$this->view->accessToken = $parsed;	
		}
	}
    
    public function revokeAction()
    {
        $get = Zend_Registry::get('getFilter');
        
        if (!isset($get->consumerId)) {
            throw new Ot_Exception_Input('No consumer ID was given');
        }
        
        $consumer = new Ot_Oauth_Server_Consumer();
        
		$thisConsumer = $consumer->find($get->consumerId);
		if (is_null($thisConsumer)) {
			throw new Ot_Exception_Data('consumer not found.');
		}
		
		$this->view->consumer = $thisConsumer;
				
		$st = new Ot_Oauth_Server_Token();
		
		$existingAccessToken = $st->getTokenByAccountAndConsumer(Zend_Auth::getInstance()->getIdentity()->accountId, $thisConsumer->consumerId, 'access');
		if (is_null($existingAccessToken)) {
			throw new Ot_Exception_Data('You dont have an existing access token for this consumer.');
		}	
		
        $form = Ot_Form_Template::delete('revokeToken', 'Revoke Access');
        
        if ($this->_request->isPost() && $form->isValid($_POST)) {
        	$existingAccessToken->delete();
        	
        	$this->_helper->flashMessenger->addMessage('Access to the application has been revoked.');
        	
        	$this->_helper->redirector->gotoRoute(array(), 'account', true);
        }
        
        $this->view->form = $form;
        $this->_helper->pageTitle('oauth-server-revoke:title', $thisConsumer->name);
        
    }

	
	protected function _getImage($imageId)
	{
		if ($imageId == 0) {
			return $this->view->baseUrl() . '/ot/images/consumer.png';
		}
		
		return $this->view->url(array('imageId' => $imageId), 'image');
	}
}