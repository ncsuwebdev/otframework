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
 * @package    Ot_Application_Resource_Keymanagerdb
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    BSD License
 * @version    SVN: $Id: $
 */

/**
 *
 *
 * @package   Ot_Application_Resource_Keymanagerdb
 * @category  Library
 * @copyright Copyright (c) 2007 NC State University Office of Information Technology
 *
 */

class Ot_Application_Resource_Keymanagerdb extends Zend_Application_Resource_ResourceAbstract
{
    protected $_key = null;
    
    public function setKey($key)
    {
        $this->_key = $key;
    }
    
    public function init()
    {
        if (!is_null($this->_key) && $this->_key) {
            require_once $_SERVER['KEY_MANAGER2_PATH'];
            
            $km = new KeyManager();
                
            if ($km->isKey($this->_key)) {
                
                $key = $km->getKey($this->_key);
                
                $dbConfig = array(
                    'adapter'  => 'PDO_MYSQL',
                    'username' => $key->username,
                    'password' => $key->password,
                    'host'     => $key->host,
                    'port'     => $key->port,
                    'dbname'   => $key->dbname
                );  
        
                $db = Zend_Db::factory($dbConfig['adapter'], $dbConfig);
                Zend_Db_Table::setDefaultAdapter($db);
                Zend_Registry::set('dbAdapter', $db);
            } else {
                throw new Ot_Exception('KeyManager could not find the key: ' . $this->_key);
            }
        }
    }
}