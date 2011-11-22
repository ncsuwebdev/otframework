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
 * @package    Ot_Api_Code
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Model to deal with access codes for API access
 * 
 * @package    Ot_Api_Code
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 *
 */
class Ot_Model_DbTable_ApiCode extends Ot_Db_Table
{
    /**
     * Database table name
     *
     * @var string
     */
    protected $_name = 'tbl_ot_api_code';

    /**
     * Primary key for the database
     *
     * @var string
     */
    protected $_primary = 'userId';

    protected $_apiCodeUser = '';

    public function generateCodeForUser($userId)
    {
        $data = array(
            'userId' => $userId,
            'code'   => md5(microtime()),
        );
        
        $thisCode = $this->find($userId);
        
        if (is_null($thisCode)) {
            parent::insert($data);
        } else {
            parent::update($data, null);
        }
        
        return $data['code'];
    }

    public function verify($accessCode)
    {
        $where = $this->getAdapter()->quoteInto('code = ?', $accessCode);
        $this->_messages[] = $where;
        $result = $this->fetchAll($where, null, 1);
        
        if ($result->count() != 1) {
            throw new Exception('Code not found');
        }
        
        $this->_apiCodeUser = $result->current()->userId;
            
        $config = Zend_Registry::get('appConfig');
        Zend_Loader::loadClass($config->authorization);

        $authz = new $config->authorization;   
        
        $result = $authz->getUser($this->_apiCodeUser);
    }
    
    public function getApiUserId()
    {
        return $this->_apiCodeUser;
    }
}
