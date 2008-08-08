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

	    if (!is_writable('./backup/')) {
	    	throw new Ot_Exception_Data('Backup folder is not writable');
	    }
	    
	    $fileName = $tableName . '.backup-' . date('Ymd-B') . '.csv';
	    
	    $filePath = './backup/' . $fileName;
	    
	    $fp = fopen($filePath, 'w');
	    
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
	    
	    file_get_contents($filePath);
	    
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Length: ' . filesize($filePath));
		header("Content-Disposition: attachment; filename=$fileName");
		readfile($filePath);
		unlink($filePath);
	}
}