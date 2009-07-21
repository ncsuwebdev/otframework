<?php
class Ot_Backup {
	
    public function getBackup($db, $tableName, $type)
    {
        $this->_db = $db;
        $this->_tableName = $tableName;
        
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
	    $config = Zend_Registry::get('config');
        
        if (!(isset($config->app->tablePrefix) && !empty($config->app->tablePrefix))) {
        	throw new Ot_Exception_Access('No table prefix is defined, therefore you cannot make any backups.');
        }

        if (!preg_match('/^' . $config->app->tablePrefix . '/i', $this->_tableName)) {
        	throw new Ot_Exception_Access('You are attempting to access a table outside your application.  This is not allowed.');
        }
        
		$data = $this->_db->fetchAssoc("SELECT * FROM $this->_tableName");
	    $colData = $this->_db->describeTable($this->_tableName);
	    
	    $columnNames = array();
	    
	    foreach ($colData as $colName => $value) {
	    	$columnNames[$colName] = '"' . $colName . '"';
	    }
	    
	    $fileName = $this->_tableName . '.backup-' . date('Ymd-B') . '.csv';
	    
	    $tmpName = tempnam('/tmp', $fileName);
	    
	    $fp = fopen($tmpName, 'w');
	    
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
	    
	    file_get_contents($tmpName);
	    
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Length: ' . filesize($tmpName));
		header("Content-Disposition: attachment; filename=$fileName");
		readfile($tmpName);
		unlink($tmpName);
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
        $config = Zend_Registry::get('config');
        
        if (!(isset($config->app->tablePrefix) && !empty($config->app->tablePrefix))) {
            throw new Ot_Exception_Access('No table prefix is defined, therefore you cannot make any backups.');
        }

        if (!$allTables) {
            if (!preg_match('/^' . $config->app->tablePrefix . '/i', $this->_tableName)) {
                throw new Ot_Exception_Access('You are attempting to access a table outside your application.  This is not allowed.');
            }
        }
        
        $dbConfig = $this->_db->getConfig();
        $dbName = $dbConfig['dbname'];
        $dbHost = $dbConfig['host'];
        $dbUser = $dbConfig['username'];
        $dbPass = $dbConfig['password'];
        
        if ($allTables) {
            $fileName = $dbConfig['dbname'] . '_' . $config->app->tablePrefix . '.backup-' . date('Ymd-B') . '.sql';
            $tables = implode(' ', $this->_getTables());
            $cmd = "mysqldump $dbName --host=$dbHost --user=$dbUser --password=$dbPass --extended-insert $tables > ./cache/$fileName";
        } else {
            $fileName = $this->_tableName . '.backup-' . date('Ymd-B') . '.sql';
            $tableName = $this->_tableName;
            $cmd = "mysqldump $dbName --host=$dbHost --user=$dbUser --password=$dbPass --extended-insert $tableName > ./cache/$fileName";
        }
        
        exec($cmd, $result, $rc);
        
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Length: ' . filesize('./cache/' . $fileName));
        header("Content-Disposition: attachment; filename=$fileName");
        readfile('./cache/' . $fileName);
        unlink('./cache/' . $fileName);
    }	
    
    protected function _getTables()
    {
        $db = Zend_Registry::get('dbAdapter');
        
        $tables = $db->listTables();
        $tableList = array();
        
        $config = Zend_Registry::get('config');
        
        foreach ($tables as $t) {
            if (preg_match('/^' . $config->app->tablePrefix . '/i', $t)) {
                $tableList[$t] = $t;
            }
        }
        
        return $tableList;
    }
	
    /**
     * The form for downloading the database tables
     */
    public function _form()
    {
        $form = new Zend_Form();
        
        $config = Zend_Registry::get('config');
        
        if (!(isset($config->app->tablePrefix) && !empty($config->app->tablePrefix))) {
            throw new Ot_Exception_Access('No table prefix is definied, therefore you cannot make any backups.');
        }
        
        $tableList = $this->_getTables();
        
        $form->setAttrib('id', 'downloadDbTableForm')
             ->setDecorators(array(
                     'FormElements',
                     array('HtmlTag', array('tag' => 'div', 'class' => 'zend_form')),
                     'Form',
             ));
                       
        $tableName = $form->createElement('select', 'tableName', array('label' => 'Select A Table:'));
        $tableName->setRequired(true)
                  ->setMultiOptions($tableList);
        
        $submitCsv = $form->createElement('submit', 'submitCsv', array('label' => 'Download as CSV'));
        $submitCsv->setDecorators(array(
                   array('ViewHelper', array('helper' => 'formSubmit'))
                 ));

        // if the mysqldump command is available on the system then allow the download as SQL option
        if (!is_null(`mysqldump`)) {
            $submitSql = $form->createElement('submit', 'submitSql', array('label' => 'Download as SQL'));
            $submitSql->setDecorators(array(
                array('ViewHelper', array('helper' => 'formSubmit'))
            ));
        }
                        
        $form->addElements(array($tableName))
             ->setElementDecorators(array(
                  'ViewHelper',
                  'Errors',      
                  array('HtmlTag', array('tag' => 'div', 'class' => 'elm')), 
                  array('Label', array('tag' => 'span')),      
              ))
             ->addElements(array($submitCsv));
             
        if (!is_null(`mysqldump`)) {
            $form->addElement($submitSql);    
        }
             
        return $form;
    }
}