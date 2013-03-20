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
 * @package    Ot_Backup
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Handles backup functionality for the application.
 *
 * @package    Ot_Backup
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * 
 */

class Ot_Model_Backup
{
    
    /**
     * Database adapter to use
     */
    protected $_db = null;
    
    /**
     * Name of table to get
     */
    protected $_tableName = '';

    protected $_prefix = '';

    public function __construct()
    {
        global $application;

        $this->_prefix = $application->getOption('tablePrefix');                             
        
        if (empty($this->_prefix)) {
            throw new Ot_Exception_Access('No table prefix is defined, therefore you cannot make any backups.');
        }
        
        if (!is_writable(APPLICATION_PATH . '/../cache')) {
            throw new Ot_Exception_Data($this->view->translate('msg-error-cacheDirectoryNotWritable'));
        }
        
        $this->_db = Zend_Db_Table::getDefaultAdapter();
    }
    /**
     * Does some sanity checking to make sure there's a prefix and the user is
     * only access the tables in their application and that the cache is 
     * writable before dispatching which backup to get 
     * 
     * @param $db Zend_Db_Adapter to use
     * @param $tableName The name of the table to fetch
     * @param $type The type of backup to get (csv, sql, or sqlAll)
     */
    public function getBackup($tableName, $type)
    {        
        $this->_tableName = $tableName;   
        
        if ($type != 'sqlAll' && !preg_match('/^' . $this->_prefix . '/i', $this->_tableName)) {
            throw new Ot_Exception_Access('You are attempting to access a table outside your application.  This is not allowed.');
        }
        
        switch($type) {            
            case 'csv':
                $this->_getCsv();
                break;
                
            case 'sql':
                $this->_getSql();
                break;
                
            case 'sqlAll':
                $this->_getSql(true);
                break;
                
            default: 
                $this->_getCsv();
                break;
        }
    }
    
    /**
     * Generates a CSV from a database table and sends it to the browser
     * for download
     *
     * @param Zend_Db_Adapter $db
     * @param string $tableName
     */
    protected function _getCsv()
    {
        $data = $this->_db->fetchAssoc("SELECT * FROM $this->_tableName");
        $colData = $this->_db->describeTable($this->_tableName);
        
        $columnNames = array();
        
        foreach ($colData as $colName => $value) {
            $columnNames[$colName] = '"' . $colName . '"';
        }
        
        $filePath = APPLICATION_PATH . '/../cache/';
        $fileName = $this->_tableName . '.backup-' . date('Ymd-B') . '.csv';
        
        $fp = fopen($filePath . $fileName, 'w+');
        
        $ret = fputcsv($fp, $columnNames, ',', '"');
        
        if ($ret === false) {
            throw new Ot_Exception_Data('Error writing backup CSV file');
        }
        
        foreach ($data as $row) {
            $ret = fputcsv($fp, $row, ',', '"');
            
            if ($ret === false) {
                throw new Ot_Exception_Data('Error writing backup CSV file');
            }   
        }
        
        fclose($fp);
        
        file_get_contents($filePath . $fileName);
        
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Length: ' . filesize($filePath . $fileName));
        header("Content-Disposition: attachment; filename=$fileName");
        readfile($filePath . $fileName);
        unlink($filePath . $fileName);
    }
    
    /**
     * Generates an SQL from a database table using mysqldump and sends it to 
     * the browser for download
     *
     * @param Zend_Db_Adapter $db
     * @param string $tableName
     */
    protected function _getSql($allTables = false)
    {        
        $dbConfig = $this->_db->getConfig();
        $dbName = $dbConfig['dbname'];
        $dbHost = $dbConfig['host'];
        $dbUser = $dbConfig['username'];
        $dbPass = $dbConfig['password'];
        
        $path = APPLICATION_PATH . '/../cache';
                
        if ($allTables) {            
            $fileName = $dbConfig['dbname'] . '_' . $this->_prefix . '.backup-' . date('Ymd-B') . '.sql';
            $tables = implode(' ', $this->_getTables());
            $cmd = "mysqldump $dbName --host=$dbHost --user=$dbUser
                --password=$dbPass --extended-insert $tables > $path/$fileName";
        } else {

            $fileName = $this->_tableName . '.backup-' . date('Ymd-B') . '.sql';
            $tableName = $this->_tableName;
            $cmd = "mysqldump $dbName --host=$dbHost --user=$dbUser
                --password=$dbPass --extended-insert $tableName > $path/$fileName";
        }

        exec($cmd, $result, $rc);
        
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Length: ' . filesize($path . '/' . $fileName));
        header("Content-Disposition: attachment; filename=$fileName");
        readfile($path . '/' . $fileName);
        unlink($path . '/' . $fileName);
    }   
    
    public function getTables()
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        
        $tables = $db->listTables();
        $tableList = array();
        
        foreach ($tables as $t) {
            if (preg_match('/^' . $this->_prefix . '/i', $t)) {
                $tableList[$t] = $t;
            }
        }
        
        return $tableList;
    }
}