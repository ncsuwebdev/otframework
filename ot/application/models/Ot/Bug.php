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
 * @package    Ot_Bug
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Model to do deal with bug reports
 *
 * @package    Ot_Bug
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 *
 */
class Ot_Bug extends Ot_Db_Table
{
    /**
     * Name of the table in the database
     *
     * @var string
     */
    protected $_name = 'tbl_ot_bug';

    /**
     * Primary key of the table
     *
     * @var string
     */
    protected $_primary = 'bugId';

    /**
     * Inserts a new row into the table
     *
     * @param array $data
     * @return Result from Zend_Db_Table::insert()
     */
    public function insert(array $data)
    {
        $dba = $this->getAdapter();
        
        $dba->beginTransaction();
        
        $bt = new Ot_Bug_Text();
        $text = $data['text'];
        unset($data['text']);
        
        try {
            $bugId = parent::insert($data);
        } catch (Exception $e) {
            $dba->rollback();
            throw $e;
        }
        
        try {
        	$text['bugId'] = $bugId;
            $bt->insert($text);
        } catch (Exception $e) {
        	$dba->rollback();
        	throw $e;
        }

        $dba->commit();
        
        return $bugId;
    }
    
    public function update(array $data, $where)
    {
        $dba = $this->getAdapter();
        
        $dba->beginTransaction();
        
        if (isset($data['text'])) {
	        $bt = new Ot_Bug_Text();
	        $text = $data['text'];
	        unset($data['text']);
	        
	        try {
	            $bt->insert($text);
	        } catch (Exception $e) {
	            $dba->rollback();
	            throw $e;
	        }	        
        }
        
        try {
            parent::update($data, $where);
        } catch (Exception $e) {
            $dba->rollback();
            throw $e;
        }

        $dba->commit();    	
    }

    /**
     * Gets all the bugs, with options to only show new bugs
     *
     * @param boolean $newOnly
     * @return result from fetchAll
     */
    public function getBugs($newOnly = true)
    {
        if ($newOnly) {
            $where = $this->getAdapter()->quoteInto('status IN (?)', array('new', 'escalated'));
        } else {
            $where = null;
        }

        return parent::fetchAll($where, 'submitDt DESC');
    }
    
    public function getColumnOptions($col)
    {
    	$info = $this->info();
    	
    	$dataType = $info['metadata'][$col]['DATA_TYPE'];

        $options = array($col);
        
        if (!preg_match('/enum/i', $dataType)) {
        	return $options;
        }
        
        $options = array();
        
        $dataType = preg_replace('/(enum\(|\)|\')/i', '', $dataType);
        $dataType = explode(',', $dataType);
        
        return array_combine($dataType, $dataType);
    }
}
?>
