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
class Oauth_IndexController extends Zend_Controller_Action 
{    
	
	/**
	 * Displays a list of all a user's registered consumers
	 *
	 */
	public function indexAction()
	{
        $consumer = new Ot_Oauth_Server_Consumer();
        
        $owned = $consumer->getConsumersForRegisteredAccounnt(Zend_Auth::getInstance()->getIdentity()->accountId);
        
        $this->view->ownedConsumers = $owned->toArray();
        
        $config = Zend_Registry::get('config');
        
       	$this->_helper->pageTitle('oauth-index-index:title', $config->user->appTitle->val);
		$this->view->messages = $this->_helper->flashMessenger->getMessages();
	}
	
	/**
	 * Displays a list of all the consumers registered with application regardless
	 * of the user who registered the consumer
	 */
	public function allConsumersAction()
	{
	    $consumer = new Ot_Oauth_Server_Consumer();
        
        $allConsumers = $consumer->fetchAll(null, 'name ASC');
        
        $this->view->allConsumers = $allConsumers->toArray();
        
        $config = Zend_Registry::get('config');
        
        $this->_helper->pageTitle('oauth-index-allConsumers:title', $config->user->appTitle->val);
	}
	
	/**
	 * Displays the details about a registered consumer
	 *
	 */
	public function detailsAction()
	{		
		$get = Zend_Registry::get('getFilter');
		
		if (!isset($get->consumerId)) {
			throw new Ot_Exception_Input('consumerId not set in query string.');
		}
		
		if (isset($get->all)) {
		    $this->view->all = true;
		}
		
		$consumer = new Ot_Oauth_Server_Consumer();
		
		$thisConsumer = $consumer->find($get->consumerId);
		if (is_null($thisConsumer)) {
			throw new Ot_Exception_Data('consumer not found.');
		}
		
		if ($thisConsumer->registeredAccountId != Zend_Auth::getInstance()->getIdentity()->accountId && !$this->_helper->hasAccess('allConsumers')) {
			throw new Ot_Exception_Access('You are not allowed to edit other users applications.');
		}
		
		$this->view->consumer = $thisConsumer;
		
		$st = new Ot_Oauth_Server_Token();
		
		$tokens = $st->getTokensForConsumerId($thisConsumer->consumerId, 'access');
		
		$this->view->usage = $tokens->count();
		$this->_helper->pageTitle('oauth-index-details:title', $thisConsumer->name);
		$this->view->messages = $this->_helper->flashMessenger->getMessages();
		
	}
	
	/**
	 * Add a new registered consumer
	 *
	 */
	public function addAction()
	{
		$this->_helper->pageTitle('oauth-index-add:title');
		
		$consumer = new Ot_Oauth_Server_Consumer();
		
		$form = $consumer->form(array('imagePath' => $this->_getImage(0)));
		
		$messages = array();
		if ($this->_request->isPost()) {
			if ($form->isValid($_POST)) {
				$data = array(
					'name'                => $form->getValue('name'),
					'description'         => $form->getValue('description'),
					'website'             => $form->getValue('website'),
					'registeredAccountId' => Zend_Auth::getInstance()->getIdentity()->accountId,
					'callbackUrl'         => $form->getValue('callbackUrl'),
					'consumerKey'         => '',
					'consumerSecret'      => '',
				);
				
				if ($form->getValue('image') != '/tmp/' && $form->getValue('image') != '') {

	                $image = new Ot_Image();
	
	                $image->resizeImage($form->image->getFileName(), 64, 64);
	
	                $iData = array(
	                   'source' => file_get_contents(trim($form->image->getFileName())),
	                 );
	
	                 $data['imageId'] = $image->insert($iData);
	            }				
				
				$consumerId = $consumer->insert($data);
				
				$this->_helper->flashMessenger->addMessage('Your application was successfully registered.');
				
				$this->_helper->redirector->gotoUrl('/oauth/index/details/?consumerId=' . $consumerId);
			} else {
				$messages[] = 'There was a problem submitting your form.';
			}
		}
		
		$this->view->messages = $messages;
		$this->view->form     = $form;
	}
	
	/**
	 * Edit a registered consumer's details
	 *
	 */
	public function editAction()
	{
		$this->_helper->pageTitle('oauth-index-edit:title');
		
		$get = Zend_Registry::get('getFilter');
		
		if (!isset($get->consumerId)) {
			throw new Ot_Exception_Input('ConsumerId not set in query string.');
		}
		
		$consumer = new Ot_Oauth_Server_Consumer();
		
		$thisConsumer = $consumer->find($get->consumerId);
		if (is_null($thisConsumer)) {
			throw new Ot_Exception_Data('Consumer not found.');
		}
		
		if ($thisConsumer->registeredAccountId != Zend_Auth::getInstance()->getIdentity()->accountId && !$this->_helper->hasAccess('allConsumers')) {
			throw new Ot_Exception_Access('You are not allowed to edit other users applications.');
		}
		
		$form = $consumer->form(array_merge($thisConsumer->toArray(), array('imagePath' => $this->_getImage($thisConsumer->imageId))));
		
		$messages = array();
		if ($this->_request->isPost()) {
			if ($form->isValid($_POST)) {
				$data = array(
					'consumerId'          => $thisConsumer->consumerId,
					'name'                => $form->getValue('name'),
					'description'         => $form->getValue('description'),
					'website'             => $form->getValue('website'),
					'callbackUrl'         => $form->getValue('callbackUrl'),
				);
				
				if ($form->getValue('image') != '/tmp/' && $form->getValue('image') != '') {

	                $image = new Ot_Image();
	
	                $image->resizeImage($form->image->getFileName(), 64, 64);
	
	                $iData = array(
	                   'source' => file_get_contents(trim($form->image->getFileName())),
	                 );
	
					 if (isset($thisConsumer->imageId) && $thisConsumer->imageId != 0) {
	                    $image->deleteImage($thisConsumer->imageId);
	                 }
	                 	               
	                 $data['imageId'] = $image->insert($iData);
	            }					
				
				$consumer->update($data, null);
				
				$this->_helper->flashMessenger->addMessage('Your application was successfully modified.');
				
				$this->_helper->redirector->gotoUrl('/oauth/index/details/?consumerId=' . $data['consumerId']);
			} else {
				$messages[] = 'There was a problem submitting your form.';
			}
		}
		
		$this->view->messages = $messages;
		$this->view->form     = $form;
	}
	
	public function deleteAction()
	{
		$this->_helper->pageTitle('oauth-index-delete:title');
		
		$get = Zend_Registry::get('getFilter');
		
		if (!isset($get->consumerId)) {
			throw new Ot_Exception_Input('consumerId not set in query string.');
		}
		
		$consumer = new Ot_Oauth_Server_Consumer();
		
		$thisConsumer = $consumer->find($get->consumerId);
		if (is_null($thisConsumer)) {
			throw new Ot_Exception_Data('consumer not found.');
		}
		
		if ($thisConsumer->registeredAccountId != Zend_Auth::getInstance()->getIdentity()->accountId && !$this->_helper->hasAccess('allConsumers')) {
			throw new Ot_Exception_Access('You are not allowed to edit other users applications.');
		}
		
		$form = Ot_Form_Template::delete('deleteConsumer', 'Delete Application');
		
		if ($this->_request->isPost() && $form->isValid($_POST)) {
			$consumer->deleteConsumer($thisConsumer->consumerId);
						
			$this->_helper->flashMessenger->addMessage('Your application was successfully removed.');
			
			$this->_helper->redirector->gotoUrl('/oauth');
		}
		
		$this->view->form = $form;
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
		
		if ($thisConsumer->registeredAccountId != Zend_Auth::getInstance()->getIdentity()->accountId && !$this->_helper->hasAccess('allConsumers')) {
			throw new Ot_Exception_Access('You are not allowed to edit other users applications.');
		}
		
		$this->view->consumer = $thisConsumer;
				
		$st = new Ot_Oauth_Server_Token();
		
		$existingAccessToken = $st->getTokenByAccountAndConsumer(Zend_Auth::getInstance()->getIdentity()->accountId, $thisConsumer->consumerId, 'access');
		if (!is_null($existingAccessToken)) {
			throw new Ot_Exception_Data('You already have an existing access token for this consumer.  Remove that token to create a new one.');
		}	
				
		$this->_helper->pageTitle('oauth-index-generateToken:title');
		
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
	
	public function regenerateConsumerKeysAction()
	{
		$this->_helper->pageTitle('oauth-index-regenerateConsumerKeys:title');
		
		$get = Zend_Registry::get('getFilter');
		
		if (!isset($get->consumerId)) {
			throw new Ot_Exception_Input('consumerId not set in query string.');
		}
		
		$consumer = new Ot_Oauth_Server_Consumer();
		
		$thisConsumer = $consumer->find($get->consumerId);
		if (is_null($thisConsumer)) {
			throw new Ot_Exception_Data('consumer not found.');
		}
		
		if ($thisConsumer->registeredAccountId != Zend_Auth::getInstance()->getIdentity()->accountId && !$this->_helper->hasAccess('allConsumers')) {
			throw new Ot_Exception_Access('You are not allowed to edit other users applications.');
		}
		
		$form = Ot_Form_Template::delete('resetKeySecret', 'Reset Consumer Key/Secret');
		
		if ($this->_request->isPost() && $form->isValid($_POST)) {
			
			$consumer->resetConsumerKeySecret($thisConsumer->consumerId);
			
			$this->_helper->flashMessenger->addMessage('The consumer key and secret was reset for your application.  Please update your application to allow access.');
			
			$this->_helper->redirector->gotoUrl('/oauth/index/details/?consumerId=' . $thisConsumer->consumerId);
		}
		
		$this->view->form = $form;
		$this->view->consumer = $thisConsumer;			
	}
	
	protected function _getImage($imageId)
	{
		if ($imageId == 0) {
			return $this->view->baseUrl() . '/ot/images/consumer.png';
		}
		
		return $this->view->baseUrl() . '/image/?imageId=' . $imageId;
	}
}