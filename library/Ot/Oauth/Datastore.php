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
 * @package    Oauth_Datastore
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * 
 *
 * @package    Oauth_Datastore
 * @category   OAuth
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 */
class Ot_Oauth_Datastore implements Oauth_Datastore_Interface 
{
	public function lookupConsumer($consumerKey) 
	{ 
		$consumer = new Ot_Oauth_Server_Consumer();
		$thisConsumer = $consumer->getConsumerByKey($consumerKey);
		
		if (is_null($thisConsumer)) {
			return null;
		}
		
		$newConsumer = new stdClass();
		$newConsumer->secret = $thisConsumer->consumerSecret;
		$newConsumer->key    = $thisConsumer->consumerKey;
		$newConsumer->consumerId = $thisConsumer->consumerId;
		
		return $newConsumer;
	} 
	
	public function lookupToken($consumer, $tokenType, $token) 
	{ 
		$st = new Ot_Oauth_Server_Token();
		
		$thisToken = $st->getTokenByTypeAndConsumerId($token, $tokenType, $consumer->consumerId);
		
		if (is_null($thisToken)) {
			throw new Oauth_Exception('Invalid ' . $tokenType . ' token:  Token ' . $token . ' not found.');
			return null;
		}
		
		return new Oauth_Token($thisToken->token, $thisToken->tokenSecret);
	} 
	
	public function lookupNonce($consumer, $token, $nonce, $timestamp) 
	{ 
		$sn = new Ot_Oauth_Server_Nonce();
		
		$thisNonce = $sn->getNonceByConsumerAndToken($consumer->consumerId, $token);
		
		if (!is_null($thisNonce) && $thisNonce->timestamp > $timestamp) {
			throw new Oauth_Exception('Timestamp out of sync.  Denying request.');
		}

		if (!is_null($token) && (!is_null($thisNonce) && $nonce != $thisNonce->nonce)) {
			$data = array(
				'consumerId' => $consumer->consumerId,
				'token'      => $token,
				'timestamp'  => $timestamp,
				'nonce'      => $nonce,
			);
			
			try {
				$sn->insert($data);
			} catch (Exception $e) {
				throw new Oauth_Exception('Nonce already used.  Possible replay attack!  Denying request.' . $e->getMessage());
			}
			
			$sn->deleteOldNonce($consumer->consumerId, $token, $timestamp);
		}
	} 
	
	public function newToken($consumer, $type = "request", $accountId = 0) 
	{ 
		$key = md5(time());
		$secret = md5(md5(time() + time()));
		
		$token = new Oauth_Token($key, $secret);
		
		$data = array(
			'consumerId'  => $consumer->consumerId,
			'token'       => $key,
			'tokenSecret' => $secret,
			'tokenType'   => $type,
			'requestDt'   => time(),
			'accountId'   => $accountId,
		);
		
		$st = new Ot_Oauth_Server_Token();
		
		$st->insert($data);

		return $token;
	} 
	
	public function newRequestToken($consumer) 
	{ 
		return $this->newToken($consumer, "request");
	} 
	
	public function newAccessToken($token, $consumer) 
	{ 
		$st = new Ot_Oauth_Server_Token();
		
		$thisToken = $st->getTokenByTypeAndConsumerId($token->key, 'request', $consumer->consumerId);
		
		if (is_null($thisToken)) {
			throw new Oauth_Exception('Request token not found.  No access token granted.');
			return null;
		}
		
		if ($thisToken->authorized != 1) {
			throw new Oauth_Exception('Request token is not authorized.  No access token granted and request token removed.');
			return null;
		}
		
		$accountId = $thisToken->accountId;
		
	    $thisToken->delete();
		
		return $this->newToken($consumer, 'access', $accountId);
	} 
}