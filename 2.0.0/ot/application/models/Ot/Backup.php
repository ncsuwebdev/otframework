<?php
class Ot_Backup {
	
	/**
	 * Generates a CSV from a database table and sends it to the browser
	 * for download
	 *
	 * @param unknown_type $db
	 * @param unknown_type $tableName
	 */
	public function getCsv($db, $tableName)
	{
	    $config = Zend_Registry::get('config');
        
        if (!(isset($config->app->tablePrefix) && !empty($config->app->tablePrefix))) {
        	throw new Ot_Exception_Access('No table prefix is definied, therefore you cannot make any backups.');
        }

        if (!preg_match('/^' . $config->app->tablePrefix . '/i', $tableName)) {
        	throw new Ot_Exception_Access('You are attempting to access a table outside your application.  This is not allowed.');
        }
        
		$data = $db->fetchAssoc("SELECT * FROM $tableName");
	    $colData = $db->describeTable($tableName);
	    
	    $columnNames = array();
	    
	    foreach ($colData as $colName => $value) {
	    	$columnNames[$colName] = '"' . $colName . '"';
	    }
	    
	    $fileName = $tableName . '.backup-' . date('Ymd-B') . '.csv';
	    
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
     * The form for downloading the database tables
     */
    public function _form()
    {
        $form = new Zend_Form();
        
        $db = Zend_Registry::get('dbAdapter');
        
        $tables = $db->listTables();
        
        $tableList = array();
        
        $config = Zend_Registry::get('config');
        
        if (!(isset($config->app->tablePrefix) && !empty($config->app->tablePrefix))) {
        	throw new Ot_Exception_Access('No table prefix is definied, therefore you cannot make any backups.');
        }
        
        foreach ($tables as $t) {
			if (preg_match('/^' . $config->app->tablePrefix . '/i', $t)) {
				$tableList[$t] = $t;
			}
        }
        
        $form->setAttrib('id', 'downloadDbTableForm');
                       
        $tableName = $form->createElement('select', 'tableName', array('label' => 'Table:'));
        $tableName->setRequired(true)
                  ->setMultiOptions($tableList);
        
        $submit = $form->createElement('submit', 'submitButton', array('label' => 'Get Backup'));
        $submit->setDecorators(array(
                   array('ViewHelper', array('helper' => 'formSubmit'))
                 ));
                        
        $form->addElements(array($tableName))
             ->setElementDecorators(array(
                  'ViewHelper',
                  'Errors',      
                  array('HtmlTag', array('tag' => 'div', 'class' => 'elm')), 
                  array('Label', array('tag' => 'span')),      
              ))
             ->addElements(array($submit));
             
        return $form;
    }
}