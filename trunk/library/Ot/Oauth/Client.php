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
 * @package    Ot_Oauth_Client
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Model to do deal with Oauth consumers
 *
 * @package    Ot_Oauth_Client
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 *
 */
class Ot_Oauth_Client
{
    
    /**
     * The HTTP client
     *
     * @var Zend_Http_Client
     */
    protected $_client;
    
    /**
     * The consumer's key
     *
     * @var string
     */
    protected $_consumerKey;
    
    /**
     * The consumer's secret
     *
     * @var string
     */
    protected $_consumerSecret;
    
    /**
     * The consumer's token
     *
     * @var string
     */
    protected $_token = null;
    
    /**
     * The consumer's token secret
     *
     * @var string
     */
    protected $_tokenSecret = null;
    
    /**
     * Request token retrieved from OAuth provider
     *
     * @var Oauth_Token
     */
    protected $_requestToken = null;

    /**
     * Access token retrieved from OAuth provider
     *
     * @var Oauth_Token
     */
    protected $_accessToken = null;
    
    /**
     * The url to use to request an unauthorized access token
     * 
     * @var string
     */
    protected $_requestTokenUrl;
    
    /**
     * The url to use to authorize a request
     * 
     * @var string
     */
    protected $_authorizeUrl;
    
    /**
     * The url to use to get the access token
     * 
     * @var string
     */
    protected $_accessTokenUrl;
    
    /**
     * The signature method to use
     *
     * @var string
     */
    protected $_signatureMethod = 'HMACSHA1';
    
    /**
     * The HTTP method type to use (GET, POST)
     *
     * @var string
     */
    protected $_httpMethod = 'POST';
    
    /**
     * An array of parameters for the request
     *
     * @var array
     */
    protected $_params = array();
    
    
    public function __construct($options)
    {
        if (!isset($options['consumerKey'])) {
            throw new Ot_Exception('No consumer key provided.');
        }
        
        if (!isset($options['consumerSecret'])) {
            throw new Ot_Exception('No consumer secret provided.');
        }
        
        if (!isset($options['requestTokenUrl'])) {
            throw new Ot_Exception('No request token url provided.');
        }
        
        if (!isset($options['accessTokenUrl'])) {
            throw new Ot_Exception('No access token url provided.');
        }
        
        if (!isset($options['authorizeUrl'])) {
            throw new Ot_Exception('No authorize url provided.');
        }
        
        $this->_consumerKey     = $options['consumerKey'];
        $this->_consumerSecret  = $options['consumerSecret'];
        $this->_requestTokenUrl = $options['requestTokenUrl'];
        $this->_accessTokenUrl  = $options['accessTokenUrl'];
        $this->_authorizeUrl    = $options['authorizeUrl'];
        
        $this->_client = new Zend_Http_Client();
    }
    
    public function setRequestUrl($url)
    {
        $this->_requestUrl = $url;
    }
    
    public function setAuthorizeUrl($url)
    {
        $this->_authorizeUrl = $url;
    }
    
    public function setAccessUrl($url)
    {
        $this->_accessUrl = $url;
    }
    
    public function setRequestScheme($scheme)
    {
        $this->_requestScheme = $scheme;
    }
    
    public function getRequestScheme()
    {
        return $this->_requestScheme;
    }
    
    public function setRequestToken($key, $secret)
    {
        $this->_requestToken = new Oauth_Token($key, $secret);
    }
    
    public function setAccessToken($key, $secret)
    {
        $this->_accessToken = new Oauth_Token($key, $secret);
        $this->_token = $key;
        $this->_tokenSecret = $secret;
    }
    
    public function getRequestToken($params = array())
    {
        $this->_client->resetParameters();
        
        $className = 'Oauth_Signature_Method_' . $this->_signatureMethod;
        
        $sigMethod = new $className();
            
        $consumer = new Oauth_Consumer($this->_consumerKey, $this->_consumerSecret, null);
            
        $req = Oauth_Request::fromConsumerAndToken($consumer, null, 'GET', $this->_requestTokenUrl, $params);
        $req->signRequest($sigMethod, $consumer, null);
        
        $this->_client->setUri($req->toUrl());
        
        $response = $this->_client->request();
        
        if ($response->getStatus() == "200") {
            $tokenVars = array();
            parse_str($response->getBody(), $tokenVars);                        
            $this->_requestToken = new Oauth_Token($tokenVars['oauth_token'], $tokenVars['oauth_token_secret']);
            return $this->_requestToken;
        } else {
            throw new Ot_Exception('Error getting request token', $response->getBody());
        }
    }
    
    public function getAuthorizeUrl()
    {            
        $url = parse_url($this->_authorizeUrl);
        if (isset($url['query'])) {
            return $this->_authorizeUrl . "&" . $this->_requestToken;
        } else {
            return $this->_authorizeUrl . "?" . $this->_requestToken;
        }
    }
    
    public function getAccessToken($params = array())
    {
        $this->_client->resetParameters();
        
        $className = 'Oauth_Signature_Method_' . $this->_signatureMethod;
        
        $sigMethod = new $className();
            
        $consumer = new Oauth_Consumer($this->_consumerKey, $this->_consumerSecret, null);
            
        $req = Oauth_Request::fromConsumerAndToken(
            $consumer,
            $this->_requestToken,
            'GET',
            $this->_accessTokenUrl,
            $params
        );
        $req->signRequest($sigMethod, $consumer, $this->_requestToken);
        
        $this->_client->setUri($req->toUrl());
        
        $response = $this->_client->request();
        
        if ($response->getStatus() == "200") {
            $tokenVars = array();
            parse_str($response->getBody(), $tokenVars);                        
            $this->_accessToken = new Oauth_Token($tokenVars['oauth_token'], $tokenVars['oauth_token_secret']);
            return $this->_accessToken;
        } else {
            throw new Ot_Exception('Error getting access token', $response->getBody());
        }        
    }
    
    /**
     * Sends the provided API request
     *
     * @param Oauth_Request $request
     * @return response from the request
     */
    public function sendApiRequest(Oauth_Request $request)
    {                
        $this->_client->resetParameters();
        $this->_client->setMethod($request->getNormalizedHttpMethod());
        $this->_client->setUri($request->toUrl());
        
        $response = $this->_client->request();
        
        if ($response->getStatus() == "200") {
            return $response->getBody();
        } else {
            throw new Ot_Exception('Error executing remote API call: ' . $response->getBody());
        }
    }
    
    /**
     * Sends and API request to the specified uri
     *
     * @param string $uri The url to the api
     * @param string $requestMethod How to send the request
     * @param array $params The parameters to be sent along with the request 
     * @return Oauth_Request object
     */    
    public function prepareRequest($uri, $requestMethod, $params = array())
    {
        $this->_params = $params;
        
        $className = 'Oauth_Signature_Method_' . $this->_signatureMethod;
        
        $sigMethod = new $className();
            
        $consumer = new Oauth_Consumer($this->_consumerKey, $this->_consumerSecret, null);
        $token = new Oauth_Consumer($this->_token, $this->_tokenSecret);
            
        $request = Oauth_Request::fromConsumerAndToken($consumer, $token, $requestMethod, $uri, $params);
        $request->signRequest($sigMethod, $consumer, $token);  

        return $request;
    }
}