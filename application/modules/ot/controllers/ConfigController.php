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
 * @package    Ot_ConfigController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 * @license    BSD License
 * @version    SVN: $Id: $
 */

/**
 * Allows the user to manage all application-wide configuration variables.
 *
 * @package    Ot_ConfigController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 */
class Ot_ConfigController extends Zend_Controller_Action  
{   
    /**
     * Shows all configurable options
     */
    public function indexAction()
    {       
        $this->view->acl = array(
            'edit' => $this->_helper->hasAccess('edit')
        );
        
        $this->view->configList = Zend_Registry::get('config')->user;
        
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/public/css/ot/jquery.plugin.tipsy.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/public/scripts/ot/jquery.plugin.tipsy.js');
        
        $this->view->messages   = $this->_helper->flashMessenger->getMessages();
        $this->_helper->pageTitle('ot-config-index:title');
    }

    /**
     * Modifies a configuration variable
     *
     */
    public function editAction()
    {       
    	$config = Zend_Registry::get('config');
    	
        $overrideFile = APPLICATION_PATH . '/../overrides/config/config.xml';
        
        if (!file_exists($overrideFile)) {
            throw new Ot_Exception_Data("msg-error-configFileNotFound");
        }
        
        if (!is_writable($overrideFile)) {
        	throw new Ot_Exception_Data($this->view->translate('msg-error-configFileNotWritable', $overrideFile));
        }
                
        $get = Zend_Registry::get('getFilter');
        
        if (!isset($get->key)) {
        	throw new Ot_Exception_Input('msg-error-noKey');
        }
        
        $form = new Zend_Form();
        $form->setAttrib('id', 'configEditForm')
             ->setDecorators(array(
                     'FormElements',
                     array('HtmlTag', array('tag' => 'div', 'class' => 'zend_form')),
                     'Form',
             ));
        
        if ($get->key == 'timezone') {
        	$tz = Ot_Timezone::getTimezoneList();
        	
	        $el = new Zend_Form_Element_Select('keyValue');
	        $el->addMultiOptions($tz);      	
        } else {
        	$el = new Zend_Form_Element_Text('keyValue');
        	$el->setAttrib('size', '40');
        }
        
        $config = Zend_Registry::get('config');
        
        if (!isset($config->user->{$get->key})) {
        	throw new Ot_Exception_Input('msg-error-noConfigKey');
        }
        
        $el->setValue($config->user->{$get->key}->val);
        $el->setLabel($get->key . ':');
        
        $reset = $form->createElement('checkbox', 'resetToDefault', array('label' => 'ot-config-edit:form:reset '));
        
        $submit = $form->createElement('submit', 'editButton', array('label' => 'ot-config-edit:form:submit'));
        $submit->setDecorators(array(
                   array('ViewHelper', array('helper' => 'formSubmit'))
                 ));
        
        $cancel = $form->createElement('button', 'cancel', array('label' => 'form-button-cancel'));
        $cancel->setAttrib('id', 'cancel');
        $cancel->setDecorators(array(
                   array('ViewHelper', array('helper' => 'formButton'))
                ));
                        
        $form->addElements(array($el, $reset));

        $form->setElementDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array('HtmlTag', array('tag' => 'div', 'class' => 'elm')),
                  array('Label', array('tag' => 'span')),
              ))
             ->addElements(array($submit, $cancel)); 

        $messages = array();
        
        if ($this->_request->isPost()) {
        	if ($form->isValid($_POST)) {
        		
        		if ($form->getValue('resetToDefault') || ($form->getValue('keyValue') != $config->user->{$get->key}->val)) {
					$xml = simplexml_load_file($overrideFile);
	        			            
					$logMessage = '';
					
					if ($form->getValue('resetToDefault') && isset($xml->production->user->{$get->key})) {
	        			unset($xml->production->user->{$get->key});
	        			
	        			$logMessage = 'Key was reset to default';
	        		} else {
		        		if (!isset($xml->production->user->{$get->key})) {
		        			$xml->production->user->addChild($get->key);
		        			$xml->production->user->{$get->key}['val'] = $form->getValue('keyValue');
		        		} else {
		        			$xml->production->user->{$get->key}->attributes()->val = $form->getValue('keyValue');
		        		}	        	

		        		$logMessage = 'User config was edited';
	        		}
		            
		            $xmlStr = $xml->asXml();
		            
		            if (!file_put_contents($overrideFile, $xmlStr, LOCK_EX)) {
		                throw new Ot_Exception_Data("msg-error-savingConfig");
		            }
		            
		            // this formats the xml file if the xmllint command is available.  If it's
                    // not, it should just return nothing and nothing bad will happen.  It's merely
                    // a bonus feature for boxes that have xmllib2 all up ons their box.
                    $cmd = "xmllint --format --output $overrideFile $overrideFile";
                    exec($cmd, $result, $rc);		            
		            
		            $cache = Zend_Registry::get('cache');
		            $cache->remove('configObject');
		            
		            $logOptions = array(
	                        'attributeName' => 'userConfig',
	                        'attributeId'   => $get->key,
	                );
	                    
	                $this->_helper->log(Zend_Log::INFO, $logMessage, $logOptions);   
	                     			
        		}
	            
	            $this->_helper->flashMessenger->addMessage($this->view->translate('msg-info-configUpdated', $get->key));
	            
	            $this->_helper->redirector->gotoRoute(array('controller' => 'config'), 'ot', true);
        	} else {
        		$messages[] = 'msg-error-formError';
        	}
        }
        
        $this->view->messages    = $messages;
        $this->view->form        = $form;
        $this->view->description = $config->user->{$get->key}->description;
        $this->_helper->pageTitle('ot-config-edit:title');
    }
}