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
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Model to do deal with bug reports.
 *
 * @package    Ot_Bug
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 *
 */
class Ot_Model_DbTable_Bug extends Ot_Db_Table
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
        
        $bt = new Ot_Model_DbTable_BugText();
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
    
        /**
     * Deletes a bug
     *
     * @param array $bugId
     * @return Result from Zend_Db_Table::insert()
     */
    public function delete($bugId)
    {
        $dba = $this->getAdapter();
        
        $dba->beginTransaction();
        
        $bt = new Ot_Model_DbTable_BugText();
        
        $where = $dba->quoteInto('bugId = ?', $bugId);
        
        try {
            parent::delete($where);
        } catch (Exception $e) {
            $dba->rollback();
            throw $e;
        }
        
        try {
            $bt->delete($where);
        } catch (Exception $e) {
            $dba->rollback();
            throw $e;
        }

        $dba->commit();
        
        return true;
    }
    
    public function update(array $data, $where)
    {
        $dba = $this->getAdapter();
        
        $dba->beginTransaction();
        
        if (isset($data['text'])) {
            $bt = new Ot_Model_DbTable_BugText();
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
    
    protected function _getColumnOptions($col)
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
    
    /**
     * Gets the form for adding and editing a ticket
     *
     * @param array $values
     * @return Zend_Form
     */
    public function form($values = array())
    {
        $form = new Zend_Form();
        $form->setAttrib('id', 'bugForm')->setDecorators(
            array(
                'FormElements',
                array('HtmlTag', array('tag' => 'div', 'class' => 'zend_form')),
                'Form',
            )
        );
             
        $title = $form->createElement('text', 'title', array('label' => 'Title:'));
        $title->setRequired(true)
              ->addFilter('StringTrim')
              ->addFilter('StripTags')
              ->setAttrib('maxlength', '64')
              ->setValue((isset($values['title']) ? $values['title'] : ''));
              
        if (isset($values['bugId'])) {
              
            $status = $form->createElement('select', 'status', array('label' => 'Status:'));
            $status->addMultiOptions($this->_getColumnOptions('status'))
                   ->setValue((isset($values['status'])) ? $values['status'] : '');
        }

        $reproducibility = $form->createElement('select', 'reproducibility', array('label' => 'Reproducibility:'));
        $reproducibility->addMultiOptions($this->_getColumnOptions('reproducibility'))
                        ->setValue((isset($values['reproducibility'])) ? $values['reproducibility'] : '');
        
        $severity = $form->createElement('select', 'severity', array('label' => 'Severity:'));
        $severity->addMultiOptions($this->_getColumnOptions('severity'))
                 ->setValue((isset($values['severity'])) ? $values['severity'] : '');
        
        $priority = $form->createElement('select', 'priority', array('label' => 'Priority:'));
        $priority->addMultiOptions($this->_getColumnOptions('priority'))
                 ->setValue((isset($values['priority'])) ? $values['priority'] : '');

        $description = $form->createElement('textarea', 'description', array('label' => 'Description:'));
        
        $description->setRequired(true)
                    ->addFilter('StringTrim')
                    ->addFilter('StripTags')
                    ->setAttrib('style', 'width: 300px; height: 150px;')
                    ->setValue((isset($values['description']) ? $values['description'] : ''));
                    
        if (isset($values['bugId'])) {
            $description->setRequired(false);
            $description->setLabel('Add Note:');    
        }

        $submit = $form->createElement('submit', 'submitButton', array('label' => 'Submit'));
        $submit->setDecorators(array(array('ViewHelper', array('helper' => 'formSubmit'))));

        $cancel = $form->createElement('button', 'cancel', array('label' => 'Cancel'));
        $cancel->setAttrib('id', 'cancel');
        $cancel->setDecorators(array(array('ViewHelper', array('helper' => 'formButton'))));

        $form->addElement($title);
        
        if (isset($values['bugId'])) {
            $form->addElement($status);
        }
        
        $form->addElements(array($reproducibility, $severity, $priority, $description));

        $form->setElementDecorators(
            array(
                'ViewHelper',
                'Errors',
                array('HtmlTag', array('tag' => 'div', 'class' => 'elm')),
                array('Label', array('tag' => 'span')),
            )
        )->addElements(array($submit, $cancel));

        if (isset($values['bugId'])) {

            $bugId = $form->createElement('hidden', 'bugId');
            $bugId->setValue($values['bugId']);
            $bugId->setDecorators(array(array('ViewHelper', array('helper' => 'formHidden'))));

            $form->addElement($bugId);
        }
        return $form;
    }
}