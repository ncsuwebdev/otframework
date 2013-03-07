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
 * @package    Ot_Model_DbTable_ApiApp
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Model to do deal with Api apps
 *
 * @package    Ot_Model_DbTable_ApiApp
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 *
 */
class Ot_Model_DbTable_ApiApp extends Ot_Db_Table
{
    /**
     * Name of the table in the database
     *
     * @var string
     */
    protected $_name = 'tbl_ot_api_app';

    /**
     * Primary key of the table
     *
     * @var string
     */
    protected $_primary = 'appId';
    
    public function getAppByKey($key)
    {
        $where = $this->getAdapter()->quoteInto('apiKey = ?', $key);

        $result = $this->fetchAll($where);

        if ($result->count() != 1) {
                return null;
        }

        return $result->current();
    }    

    public function getAppsForAccount($accountId)
    {
        $where = $this->getAdapter()->quoteInto('accountId = ?', $accountId);

        return $this->fetchAll($where, 'name');
    }    
    
    public function insert(array $data)
    {
        $data['apiKey'] = $this->_generateApiKey();
        
        $data = array_merge($data);

        return parent::insert($data);
    }
    
    private function _generateApiKey()
    {
        return sha1(time() + microtime() + rand(1, 1000000));
    }
    
    public function regenerateApiKey($appId)
    {
        $data = array(
            'appId' => $appId,
            'apiKey'   => $this->_generateApiKey()
        );        
        
        return parent::update($data);
    }    
}