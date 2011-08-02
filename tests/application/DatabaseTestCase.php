<?php

require_once 'Zend/Application.php';
require_once 'Zend/Test/PHPUnit/DatabaseTestCase.php';
require_once 'PHPUnit/Extensions/Database/DataSet/FlatXmlDataSet.php';

abstract class DatabaseTestCase extends Zend_Test_PHPUnit_DatabaseTestCase
{
    private $_dbMock;
    private $_application;
    
    protected function getSetUpOperation()
    {
        return PHPUnit_Extensions_Database_Operation_Factory::CLEAN_INSERT();
    }
    
    protected function setUp()
    {
        $configFilePath = APPLICATION_PATH . '/configs';
        $configXml = new Zend_Config_Xml($configFilePath . '/config.xml', 'production');
        Zend_Registry::set('config', $configXml);
        $this->application = new Zend_Application(
            APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
        $this->bootstrap = array($this, 'appBootstrap');
        parent::setUp();
    }
    
    public function appBootstrap()
    {
        $this->application->bootstrap();
    }
    protected function getConnection()
    {
        if (null === $this->_dbMock) {
            $bootstrap = $this->application->getBootstrap();
            $bootstrap->bootstrap('db');
            $connection = $bootstrap->getResource('db');
            $this->_dbMock = $this->createZendDbConnection($connection,'otframework_test');
            Zend_Db_Table_Abstract::setDefaultAdapter($connection);
        }
        return $this->_dbMock;
    }
    protected function getDataSet()
    {
        return $this->createFlatXmlDataSet(TESTS_PATH . '/_files/dbtest.xml');
    }
    
    /**
     * compares a database table to an xml file
     * $xmlFile = the path to the xml file. dir is tests/_files/
     * $table = the db table you're looking up
     * $ignoredColumns = an array of columns to ignore (such as primary keys that could get different numbers)
     */
    public function dbXmlCompare($xmlFile, $table, $ignoredColumns)
    {
        $config = Zend_Registry::get('config');
        $tablePrefix = $config->app->tablePrefix;
        
        $xmlSet = $this->getDataSet(TESTS_PATH . '/_files/' . $xmlFile);
        $dbSetTable = $this->getConnection()->createDataSet();
        
        if($ignoredColumns) {
            if(!is_array($ignoredColumns)) {
                $ignoredColumns = array($ignoredColumns);
            }
            $dbset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($dbSetTable, array($tablePrefix . $table => $ignoredColumns));
        }
        
        $this->assertTablesEqual($xmlSet->getTable($tablePrefix . $table), $dbset->getTable($tablePrefix . $table));
    
    }
    
}