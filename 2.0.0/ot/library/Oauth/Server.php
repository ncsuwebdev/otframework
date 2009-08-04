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
 * @package    Oauth_Server
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * 
 *
 * @package    Oauth_Server
 * @category   OAuth
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 */
class Oauth_Server
{
    
	protected $timestampThreshold = 3000; // in seconds, five minutes
	protected $version = 1.0; // hi blaine
	protected $signatureMethods = array();
	
	protected $dataStore;
	
	function __construct($dataStore) { 
		$this->dataStore = $dataStore;
	} 
	
	public function addSignatureMethod($signatureMethod)
	{ 
		$this->signatureMethods[$signatureMethod->getName()] = $signatureMethod;
	} 
	
	// high level functions

	/**
	 * process a request_token request
	 * returns the request token on success
	 */
	public function fetchRequestToken(&$request)
	{ 
		$this->getVersion($request);
		
		$consumer = $this->getConsumer($request);
		
		// no token required for the initial token request
		$token = NULL;
		
		$this->checkSignature($request, $consumer, $token);
		
		$newToken = $this->dataStore->newRequestToken($consumer);
		
		return $newToken;
	} 
	
	/**
	 * process an access_token request
	 * returns the access token on success
	 */
	public function fetchAccessToken(&$request)
	{ 
		$this->getVersion($request);
		
		$consumer = $this->getConsumer($request);

		// requires authorized request token
		$token = $this->getToken($request, $consumer, "request");
		
		$this->checkSignature($request, $consumer, $token);
		
		$newToken = $this->dataStore->newAccessToken($token, $consumer);
		
		return $newToken;
	} 
	
	/**
	 * verify an api call, checks all the parameters
	 */
	public function verifyRequest(&$request)
	{ 
		$this->getVersion($request);
		$consumer = $this->getConsumer($request);
		$token = $this->getToken($request, $consumer, "access");
		$this->checkSignature($request, $consumer, $token);
		return array ($consumer, $token);
	} 
	
	// Internals from here
	/**
	 * version 1
	 */
	private function getVersion(&$request)
	{ 
		$version = $request->getParameter("oauth_version");
		if (!$version) {
			$version = 1.0;
		}
		if ($version && $version != $this->version) {
			throw new Oauth_Exception("OAuth version '$version' not supported");
		}
		return $version;
	} 
	
	/**
	 * figure out the signature with some defaults
	 */
	private function getSignatureMethod(&$request)
	{ 
		$signatureMethod = @$request->getParameter("oauth_signature_method");
		if (!$signatureMethod) {
			$signatureMethod = "PLAINTEXT";
		}
		if (!in_array($signatureMethod, array_keys($this->signatureMethods))) {
			throw new Oauth_Exception("Signature method '$signatureMethod' not supported try one of the following: " . implode(", ", array_keys($this->signatureMethods)));
		}
		return $this->signatureMethods [$signatureMethod];
	} 
	
	/**
	 * try to find the consumer for the provided request's consumer key
	 */
	private function getConsumer(&$request)
	{ 
		$consumerKey = @$request->getParameter("oauth_consumer_key");
		if (!$consumerKey) {
			throw new Oauth_Exception("Invalid consumer key");
		}
		
		$consumer = $this->dataStore->lookupConsumer($consumerKey);
		if (!$consumer) {
			throw new Oauth_Exception("Invalid consumer");
		}
		
		return $consumer;
	} 
	
	/**
	 * try to find the token for the provided request's token key
	 */
	private function getToken(&$request, $consumer, $tokenType = "access")
	{ 
		$tokenField = @$request->getParameter('oauth_token');
		
		$token = $this->dataStore->lookupToken($consumer, $tokenType, $tokenField);
		
		return $token;
	} 
	
	/**
	 * all-in-one function to check the signature on a request
	 * should guess the signature method appropriately
	 */
	private function checkSignature(&$request, $consumer, $token)
	{ 
		// this should probably be in a different method
     	$timestamp = @$request->getParameter('oauth_timestamp');
		$nonce = @$request->getParameter('oauth_nonce');
		
		$this->checkTimestamp($timestamp);
		$this->checkNonce($consumer, $token, $nonce, $timestamp);
		
		$signatureMethod = $this->getSignatureMethod($request);
		
		$signature = $request->getParameter('oauth_signature');
		$validSig = $signatureMethod->checkSignature($request, $consumer, $token, $signature);
		
		if (!$validSig) {
			throw new Oauth_Exception("Invalid signature");
		}
	} 
	
	/**
	 * check that the timestamp is new enough
	 */
	private function checkTimestamp($timestamp)
	{ 
		// verify that timestamp is recentish
		$now = time ();
		if ($now - $timestamp > $this->timestampThreshold) {
			throw new Oauth_Exception("Expired timestamp, yours $timestamp, ours $now");
		}
	} 
	
	/**
	 * check that the nonce is not repeated
	 */
	private function checkNonce($consumer, $token, $nonce, $timestamp)
	{ 
		// verify that the nonce is uniqueish
		$found = $this->dataStore->lookupNonce($consumer, $token, $nonce, $timestamp);
		if ($found) {
			throw new Oauth_Exception("Nonce already used: $nonce");
		}
	} 
}