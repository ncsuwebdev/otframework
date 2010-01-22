<?php
/**
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file _LICENSE.txt.
 *
 * This license is also available via the world-wide-web at
 * http://itdapps.ncsu.edu/bsd.txt
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to itappdev@ncsu.edu so we can send you a copy immediately.
 *
 * @package    Ot_Trigger_Action
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    BSD License
 * @version    SVN: $Id: $
 */

/**
 * Model to interact with the action triggers
 *
 * @package    Ot_Trigger_Action
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 *
 */
class Ot_Trigger_Action extends Ot_Db_Table
{
    /**
     * Name of the table in the database
     *
     * @var string
     */
    protected $_name = 'tbl_ot_trigger_action';

    /**
     * Primary key of the table
     *
     * @var string
     */
    protected $_primary = 'triggerActionId';
    
    public function getActionsForTrigger($triggerId, $onlyEnabled = true)
    {
        $where = $this->getAdapter()->quoteInto('triggerId = ?', $triggerId);
        
        if ($onlyEnabled) {
            $where .= ' AND '
                   . $this->getAdapter()->quoteInto('enabled = ?', 1);
        }
        
        return $this->fetchAll($where);
    }
    
    /**
     * Gets the form for adding and editing an action
     *
     * @param Array $values The default values to set for the form
     */
    public function form($values = array()) 
    {
        
        $config = Zend_Registry::get('config');
        $helperTypes = array();
        
        foreach ($config->app->triggerPlugins as $key => $value) {
            $helperTypes[$key] = $value;
        }
        
        if (count($helperTypes) == 0) {
            throw new Ot_Exception_Data('No helpers are defined in the application config file.');
        }
        
        $form = new Zend_Form();
        $form->setAttrib('id', 'actionForm')
             ->setDecorators(array(
                     'FormElements',
                     array('HtmlTag', array('tag' => 'div', 'class' => 'zend_form')),
                     'Form',
             ));

         if (!isset($values['triggerActionId'])) {
            $helper = $form->createElement('select', 'helper', array('label' => 'Action:'));
            $helper->addMultiOptions($helperTypes)
                   ->setValue((isset($values['helper'])) ? $values['helper'] : '');
         } else {
            $helper = $form->createElement('text', 'helper', array('label' => 'Action:'));
            $helper->setAttrib('size', '40')
                   ->setAttrib('readonly', true)
                   ->setValue($values['helper']);
         }

        if (!isset($values['triggerId']) && !isset($values['triggerActionId'])) {
            throw new Ot_Exception_Data('You must provide a triggerId');
        }
                  
        // Create and configure username element:
        $name = $form->createElement('text', 'name', array('label' => 'Shortcut Name:'));
        $name->setRequired(true)
             ->addFilter('StringTrim')
             ->addFilter('StripTags')
             ->setValue(isset($values['name']) ? $values['name'] : '');
        
        $form->addElements(array($helper, $name)); 
        
        if (isset($values['helper'])) {
            $obj = $values['helper'];
        } else {
            $obj = key($helperTypes);
        }
            
        $thisHelper = new $obj;

        if (isset($values['triggerActionId'])) {
            $subForm = $thisHelper->editSubForm($values['triggerActionId']);
        } else {
            $subForm = $thisHelper->addSubForm();    
        }
        
        $form->addSubForm($subForm, $obj);
        
        $submit = $form->createElement('submit', 'submitButton', array('label' => 'Submit'));
        $submit->setDecorators(array(
                   array('ViewHelper', array('helper' => 'formSubmit'))
                 ));

        $cancel = $form->createElement('button', 'cancel', array('label' => 'Cancel'));
        $cancel->setAttrib('id', 'cancel');
        $cancel->setDecorators(array(
                   array('ViewHelper', array('helper' => 'formButton'))
                ));        
        
        $form->setElementDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array('HtmlTag', array('tag' => 'div', 'class' => 'elm')),
                  array('Label', array('tag' => 'span')),
              ))
             ->addElements(array($submit, $cancel));
             
        $triggerId = $form->createElement('hidden', 'triggerId');
        $triggerId->setValue($values['triggerId']);
        $triggerId->setDecorators(array(
            array('ViewHelper', array('helper' => 'formHidden'))
        ));
        
        $form->addElement($triggerId);
        
        if (isset($values['triggerActionId'])) {
            
            $triggerActionId = $form->createElement('hidden', 'triggerActionId');
            $triggerActionId->setValue($values['triggerActionId']);
            $triggerActionId->setDecorators(array(
                array('ViewHelper', array('helper' => 'formHidden'))
            ));
            
            $form->addElement($triggerActionId);
        }             
             
        return $form;
    }
}

