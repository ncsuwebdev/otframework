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
 * @package    Admin_ConfigController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 * @license    BSD License
 * @see        http://itdapps.ncsu.edu
 * @version    SVN: $Id: $
 */

/**
 * Allows the user to manage all application-wide configuration variables.
 *
 * @package    Admin_ConfigController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 */
class Admin_ConfigController extends Internal_Controller_Action  
{   
	/**
	 * Path to the config file
	 *
	 * @var string
	 */
	protected $_configFilePath = '';
	
	/**
	 * Flash messenger variable
	 *
	 * @var unknown_type
	 */
	protected $_flashMessenger = null;
	
	/**
	 * Setup flash messenger and the config file path
	 *
	 */
	public function init()
	{
		$configFiles = Zend_Registry::get('configFiles');
        
        $this->_configFilePath = $configFiles['user'];
        
        $this->_flashMessenger = $this->getHelper('FlashMessenger');
        $this->_flashMessenger->setNamespace('config');
        
        parent::init();
	}
	
    /**
     * Shows all configurable options
     */
    public function indexAction()
    {
        $this->view->title = "Configuration Admin";
        
        $this->view->acl = array(
            'edit' => $this->_acl->isAllowed($this->_role, $this->_resource, 'edit'),
        );
        
        $uc = Zend_Registry::get('userConfig');
                
        $config = array();
        foreach ($uc as $key => $value) {
            $config[] = array(
                'key'         => $key, 
                'value'       => $value->value, 
                'description' => $value->description
            );
        }
        
        if (count($config) != 0) {
        	$this->view->javascript = 'sortable.js';
        }
        
        $this->view->messages = $this->_flashMessenger->getMessages();
        $this->view->config = $config;
    }

    /**
     * Modifies a configuration variable
     *
     */
    public function editAction()
    {
        $this->view->title = "Edit Application Configuration";
        
        if (!is_writable($this->_configFilePath)) {
            throw new Ot_Exception_Data('User Config File (' . $this->_configFilePath . ') is not writable, therefore it cannot be edited');
        }
        
        $get = Zend_Registry::get('getFilter');
        if (!isset($get->key)) {
        	throw new Ot_Exception_Input('No key was found in query string.');
        }
        
        
        $form = new Zend_Form();
        $form->setAction('')
             ->setMethod('post')
             ->setAttrib('id', 'editConfig')
             ;
        
        if ($get->key == 'timezone') {
        	$tz = Ot_Timezone::getTimezoneList();
        	
	        $el = new Zend_Form_Element_Select('keyValue');
	        $el->addMultiOptions($tz);      	
        } else {
        	$el = new Zend_Form_Element_Text('keyValue');
        	$el->setAttrib('size', '40');
        }
        
        $uc = Zend_Registry::get('userConfig');
        
        if (!($uc->{$get->key} instanceof Zend_Config)) {
        	throw new Ot_Exception_Input('Key not found in config file');
        }
        
        $el->setValue($uc->{$get->key}->value);
        $el->setLabel($get->key . ':');
        
        $form->addElement($el)
             ->addDisplayGroup(array('keyValue'), 'fields')
             ->addElement('submit', 'editButton', array('label' => 'Save Config Option'))
             ->addElement('button', 'cancel', array('label' => 'Cancel'))
             ;  

        $messages = array();
        
        if ($this->_request->isPost()) {
        	if ($form->isValid($_POST)) {
        		
	            if (file_exists($this->_configFilePath)) {
	                $xml = simplexml_load_file($this->_configFilePath);
	            } else {
	                throw new Ot_Exception_Data("Error reading user configuration file");
	            }
	            
	            $xml->production->{$get->key}->value = $form->getValue('keyValue');
	            
	            $xmlStr = $xml->asXml();
	
	            if (!file_put_contents($this->_configFilePath, $xmlStr, LOCK_EX)) {
	                throw new Ot_Exception_Data("Error saving user configuration file to disk");
	            }
	            
	            $this->_logger->setEventItem('attributeName', 'userConfig');
	            $this->_logger->setEventItem('attributeId', '0');
	            $this->_logger->info("User config was edited");
	            
	            $this->_flashMessenger->addMessage('The value for ' . $get->key . ' has been updated!');
	            
	            $this->_helper->redirector->gotoUrl('/admin/config/');
        	} else {
        		$messages[] = 'The form was not filled out properly';
        	}
        }
        
        $this->view->messages = $messages;
        $this->view->form = $form;
        $this->view->description = $uc->{$get->key}->description;
        $this->view->title = "Edit configuration option";
    }
}