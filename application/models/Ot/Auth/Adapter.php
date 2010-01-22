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
 * Model to do deal with auth adapters
 *
 * @package    Ot_Bug
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 *
 */
class Ot_Auth_Adapter extends Ot_Db_Table
{
    /**
     * Name of the table in the database
     *
     * @var string
     */
    protected $_name = 'tbl_ot_auth_adapter';

    /**
     * Primary key of the table
     *
     * @var string
     */
    protected $_primary = 'adapterKey';
    
    /**
     * Returns all the enabled adapters
     */
    public function getEnabledAdapters()
    {
        $where = $this->getAdapter()->quoteInto('enabled = ?', 1);
        return $this->fetchAll($where, 'displayOrder');
    }
    
    /**
     * Returns the number of enabled adapters
     */
    public function getNumberOfEnabledAdapters()
    {
        $enabledAdapters = $this->getEnabledAdapters();
        return $enabledAdapters->count();
    }
    
    public function form($values = array())
    {
        $form = new Zend_Form();
        $form->setAttrib('id', 'authAdapterForm')
             ->setDecorators(array(
                 'FormElements',
                 array('HtmlTag', array('tag' => 'div', 'class' => 'zend_form')),
                 'Form',
             ));
             
        $name = $form->createElement('text', 'name', array('label' => 'Name:'));
        $name->setRequired(true)
              ->addFilter('StringTrim')
              ->addFilter('StripTags')
              ->setAttrib('maxlength', '64')
              ->setValue((isset($values['name']) ? $values['name'] : ''));

        $description = $form->createElement('textarea', 'description', array('label' => 'Description:'));
        
        $description->setRequired(true)
                    ->addFilter('StringTrim')
                    ->addFilter('StripTags')
                    ->setAttrib('maxlength', '64')
                    ->setAttrib('style', 'width: 300px; height: 50px;')
                    ->setValue((isset($values['description']) ? $values['description'] : ''));

        $submit = $form->createElement('submit', 'submitButton', array('label' => 'Submit'));
        $submit->setDecorators(array(
                   array('ViewHelper', array('helper' => 'formSubmit'))
                 ));

        $cancel = $form->createElement('button', 'cancel', array('label' => 'Cancel'));
        $cancel->setAttrib('id', 'cancel');
        $cancel->setDecorators(array(
                   array('ViewHelper', array('helper' => 'formButton'))
                ));

        $form->addElements(array($name, $description));

        $form->setElementDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array('HtmlTag', array('tag' => 'div', 'class' => 'elm')),
                  array('Label', array('tag' => 'span')),
              ))
             ->addElements(array($submit, $cancel));

        return $form;        
    }
    
    /**
     * Updates the display order of the Adapters
     *
     * @param array $order
     */
    public function updateAdapterOrder($order)
    {
        $dba = $this->getAdapter();
        
        $dba->beginTransaction();

        $i = 1;
        foreach ($order as $o) {

            $data = array("displayOrder" => $i);

            $where = $dba->quoteInto('adapterKey = ?', $o);

            try {
                $this->update($data, $where);
            } catch(Exception $e) {
                $dba->rollBack();
                throw $e;
            }
            $i++;
        }
        
        $dba->commit();
    }           
}