<?php
class Ot_Backup {
	
	public function getCsv($db, $tableName)
	{
		$data = $db->fetchAssoc("SELECT * FROM $tableName");
	    $colData = $db->describeTable($tableName);
	    
	    $columnNames = array();
	    
	    foreach ($colData as $colName => $value) {
	    	$columnNames[$colName] = '"' . $colName . '"';
	    }
	    
	    $fileName = $tableName . '.backup-' . date('Ymd-B') . '.csv';
	    
	    $filePath = $fileName;
	    
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
}