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
class Ot_Oauth_Server_Token extends Ot_Db_Table
{
    /**
     * Name of the table in the database
     *
     * @var string
     */
    protected $_name = 'tbl_ot_oauth_server_token';

    /**
     * Primary key of the table
     *
     * @var string
     */
    protected $_primary = 'tokenId';
    
    public function getTokenByTypeAndConsumerId($token, $tokenType, $consumerId)
    {
            
            $result = $this->getToken($token);
            
            if (is_null($result) || ($result->consumerId == $consumerId && $result->tokenType == $tokenType)) {
                    return $result;
            }
            
            return null;
    }
    
    public function getToken($token)
    {
                $where = $this->getAdapter()->quoteInto('token = ?', $token);
            
            $result = $this->fetchAll($where);
            
            if ($result->count() != 1) {
                    return null;
            }
            
            return $result->current();            
    }
    
    public function authorizeToken($token, $accountId)
    {
            $thisToken = $this->getToken($token);
            
            if (is_null($thisToken)) {
                    return null;
            }
            
            $thisToken->authorized = 1;
            $thisToken->accountId = $accountId;
            $thisToken->save();
            
            return $thisToken;
    }
    
    public function removeToken($token)
    {
            $thisToken = $this->getToken($token);
            
            $thisToken->delete();
    }
    
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
    
    public function getTokensForAccount($accountId, $tokenType)
    {
            $dba = $this->getAdapter();
            
            $where = $dba->quoteInto('accountId = ?', $accountId)
                   . ' AND '
                   . $dba->quoteInto('tokenType = ?', $tokenType);
                   
            return $this->fetchAll($where, 'requestDt DESC');            
    }
    
    public function getTokensForConsumerId($consumerId, $tokenType)
    {
            $dba = $this->getAdapter();
            
            $where = $dba->quoteInto('consumerId = ?', $consumerId)
                   . ' AND '
                   . $dba->quoteInto('tokenType = ?', $tokenType);
                   
            return $this->fetchAll($where, 'requestDt DESC');             
    }
}