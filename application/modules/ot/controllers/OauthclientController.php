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
 * @package    Oauth_ClientController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * remote access controller
 *
 * @package    
 * @subpackage Oauth_ClientController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */
class Ot_OauthclientController extends Zend_Controller_Action  
{  

    public function indexAction()
    {
        $req = new Zend_Session_Namespace(Zend_Registry::get('siteUrl') . '_request');
        $req->requestedFromUrl = $_SERVER['HTTP_REFERER'];
        
        $get = Zend_Registry::get('getFilter');
        
        if (!isset($get->consumerId)) {
            throw new Ot_Exception_Input('No consumer ID was given');
        }
        
        $consumerId = $get->consumerId;
        
        $config = Zend_Registry::get('config');
        
        $configData = $config->app->oauth->consumers->{$consumerId}->toArray();
        
        $options = array(
                    'requestTokenUrl' => $configData['requestTokenUrl'], 
                    'authorizeUrl'    => $configData['authorizeUrl'], 
                    'accessTokenUrl'  => $configData['accessTokenUrl'], 
                    'consumerKey'     => $configData['consumerKey'], 
                    'consumerSecret'  => $configData['consumerSecret']
                   );
                   
        $oAuthClient = new Ot_Oauth_Client($options);
                
        $token = $oAuthClient->getRequestToken();

        $accountId = Zend_Auth::getInstance()->getIdentity()->accountId;
        
        $otOauthToken = new Ot_Oauth_Client_Token();
        
        $otOauthToken->storeToken($accountId, $consumerId, $token->key, $token->secret, 'request');  
        
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNeverRender();
                
        $this->_helper->redirector->gotoUrl($oAuthClient->getAuthorizeUrl());
    }
    
    public function callbackAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNeverRender();
        
        $accountId = Zend_Auth::getInstance()->getIdentity()->accountId;
        
        $get = Zend_Registry::get('getFilter');
        
        if (!isset($get->oauth_token)) {
            throw new Ot_Exception_Input('You were not authorized');
        }
        
        $token = $get->oauth_token;
        
        $otOauthToken = new Ot_Oauth_Client_Token();
        $requestToken = $otOauthToken->getToken($token);
        
        if ($requestToken->token != $token) {
            throw new Ot_Exception_Access('Tokens do not match!');
        }
        
        $consumerId = $requestToken->consumerId;
        
        $config = Zend_Registry::get('config');
        
        $configData = $config->app->oauth->consumers->{$consumerId}->toArray();
        
        $options = array(
                    'requestTokenUrl' => $configData['requestTokenUrl'], 
                    'authorizeUrl'    => $configData['authorizeUrl'], 
                    'accessTokenUrl'  => $configData['accessTokenUrl'], 
                    'consumerKey'     => $configData['consumerKey'], 
                    'consumerSecret'  => $configData['consumerSecret']
                   );
                   
        $oAuthClient = new Ot_Oauth_Client($options);
        
        $oAuthClient->setRequestToken($requestToken->token, $requestToken->tokenSecret);
        
        $accessToken = $oAuthClient->getAccessToken();
        
        $otOauthToken->convertRequestTokenToAccessToken($accountId, $consumerId, $accessToken->key, $accessToken->secret);
        
        $req = new Zend_Session_Namespace(Zend_Registry::get('siteUrl') . '_request');
        $this->_helper->redirector->gotoUrl($req->requestedFromUrl);
    }
}