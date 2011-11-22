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
 * @package    Ot_Oauth_Server_Nonce
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 *
 * @package    Ot_Oauth_Server_Token
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 *
 */
class Ot_Model_DbTable_OauthClientToken extends Ot_Db_Table
{
    /**
     * Name of the table in the database
     *
     * @var string
     */
    protected $_name = 'tbl_ot_oauth_client_token';

    /**
     * Primary key of the table
     *
     * @var string
     */
    protected $_primary = array('consumerId', 'accountId');
    
    public function getTokenByAccountAndConsumer($accountId, $consumerId, $tokenType)
    {
        $dba = $this->getAdapter();
        
        $where = $dba->quoteInto('accountId = ?', $accountId)
               . ' AND '
               . $dba->quoteInto('consumerId = ?', $consumerId)
               . ' AND '
               . $dba->quoteInto('tokenType = ?', $tokenType);
               
        $result = $this->fetchAll($where, null, 1);

        if ($result->count() != 1) {
            return null;
        }
        
        return $result->current();
    }
    
    public function getToken($token)
    {
        $dba = $this->getAdapter();
        
        $where = $dba->quoteInto('token = ?', $token);
               
        $result = $this->fetchAll($where, null, 1);

        if ($result->count() != 1) {
            return null;
        }
        
        return $result->current();
    }
    
    public function getTokensForAccount($accountId, $tokenType)
    {
        $dba = $this->getAdapter();
        
        $where = $dba->quoteInto('accountId = ?', $accountId)
               . ' AND '
               . $dba->quoteInto('tokenType = ?', $tokenType);
               
        return $this->fetchAll($where);
    }
    
    public function storeToken($accountId, $consumerId, $token, $tokenSecret, $tokenType = "request")
    {
        $tokenType = strtolower($tokenType);
        
        if (!in_array($tokenType, array('request', 'access'))) {
            throw new Ot_Exception_Data("Invalid token type given. Must be 'request' or 'access'");
        }
        
        $where = $this->getAdapter()->quoteInto('accountId = ?', $accountId)
               . ' AND '
               . $this->getAdapter()->quoteInto('consumerId = ?', $consumerId);
               
        $this->delete($where);
        
        $data = array(
            'accountId'   => $accountId,
            'consumerId'  => $consumerId,
            'token'       => $token,
            'tokenSecret' => $tokenSecret,
            'tokenType'   => $tokenType,
        );
                
        return $this->insert($data);    
    }
    
    public function convertRequestTokenToAccessToken($accountId, $consumerId, $token, $tokenSecret)
    {
        $dba = $this->getAdapter();
        
        $where = $dba->quoteInto('accountId = ?', $accountId)
               . ' AND '
               . $dba->quoteInto('consumerId = ?', $consumerId);
        
        $data = array(
            'accountId'   => $accountId,
            'consumerId'  => $consumerId,
            'token'       => $token,
            'tokenSecret' => $tokenSecret,
            'tokenType'   => 'access',
        );
                
        return $this->update($data, $where);    
    }
    
    public function revokeAccess($accountId, $consumerId)
    {
        $dba = $this->getAdapter();
        
        $where = $dba->quoteInto('accountId = ?', $accountId)
               . ' AND '
               . $dba->quoteInto('consumerId = ?', $consumerId);
                
        return $this->delete($where);    
    } 
}