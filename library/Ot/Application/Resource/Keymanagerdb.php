<?php
class Ot_Application_Resource_Keymanagerdb extends Zend_Application_Resource_ResourceAbstract
{   
    protected $_key = null;
    
    public function setKey($key)
    {
        $this->_key = $key;
    }
    
    public function init()
    {
    	if (!is_null($this->_key)) {
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
        } else {
        	$resource = $this->getPluginResource('db');
        	$adapter = $resource->getDbAdapter();
	        Zend_Registry::set('dbAdapter', $adapter);
        }
    }
}