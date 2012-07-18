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
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    BSD License
 * @version    SVN: $Id: $
 */

/**
 * Allows the user to manage all application-wide configuration variables.
 *
 * @package    Ot_ConfigController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
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

        $register = new Ot_Var_Register();
        
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/public/css/ot/jquery.plugin.tipsy.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/public/scripts/ot/jquery.plugin.tipsy.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/public/scripts/ot/jquery.iphone.password.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/public/scripts/ot/caret.js');

        $vars = $register->getVars();

        $varsByModule = array();

        foreach ($vars as $v) {
            if (!isset($varsByModule[$v['namespace']])) {
                $varsByModule[$v['namespace']] = array();
            }

            $varsByModule[$v['namespace']][] = $v['object'];

        }


        $form = new Zend_Form();
        $form->setDecorators(array(
                 'FormElements',
                 array('HtmlTag', array('tag' => 'div', 'class' => 'zend_form')),
                 'Form',
             ));
        
        $section = new Zend_Form_Element_Select('section', array('label' => 'Select Configuration Section:'));
        $section->setDecorators(array(
                    'ViewHelper',
                    array(array('wrapperField' => 'HtmlTag'), array('tag' => 'div', 'class' => 'select-control')),                
                    array('Label', array('placement' => 'prepend', 'class' => 'select-label')),                   
                    array(array('wrapperAll' => 'HtmlTag'), array('tag' => 'div', 'class' => 'select-header ui-widget-header')),                    
                ))
                ->setValue($this->_getParam('selected'));
        $form->addElement($section);
        
        $sectionOptions = array();
        
        foreach ($varsByModule as $key => $value) {
            $group = array();
            foreach ($value as $v) {
                //$elm = $v->getFormElement();
                $elm = $v->renderFormElement();
                $elm->setDecorators(array(
                    'ViewHelper',
                    array('Errors', array('class' => 'help-inline')),
                    array(array('wrapperField' => 'HtmlTag'), array('tag' => 'div', 'class' => 'fields')),                
                    array('Label', array('placement' => 'append', 'class' => 'field-label')),      
                    array('Description', array('placement' => 'append', 'tag' => 'div', 'class' => 'field-description')),                     
                    array(array('empty' => 'HtmlTag'), array('placement' => 'append', 'tag' => 'div', 'class' => 'ui-helper-clearfix')),
                    array(array('wrapperAll' => 'HtmlTag'), array( 'tag' => 'div', 'class' => 'field-group')),                    
                ));
                
                $group[] = $elm->getName();

                $form->addElement($elm);
            }

            $sectionOptions[preg_replace('/[^a-z]/i', '', $key)] = $key;
                        
            $form->addDisplayGroup($group, $key);
        }
        
        asort($sectionOptions);
        
        $section->setMultiOptions($sectionOptions);

        $form->setDisplayGroupDecorators(array(
            'FormElements',
            'Fieldset'
        ));

        $submit = $form->createElement('submit', 'saveButton', array('label' => 'Save Configuration'));
        $submit->setDecorators(array(
            array('ViewHelper', array('helper' => 'formSubmit'))
        ));

        $cancel = $form->createElement('button', 'cancel', array('label' => 'Cancel'));
        $cancel->setAttrib('id', 'cancel');
        $cancel->setDecorators(array(
            array('ViewHelper', array('helper' => 'formButton'))
        ));

        $form->addElements(array($submit, $cancel));

        if ($this->_request->isPost()) {
            if ($form->isValid($_POST)) {
                                
                foreach ($varsByModule as $key => $value) {
                    foreach ($value as $v) {
                        $v->setValue($form->getElement('config_' . $v->getName())->getValue());
                    }
                }

                $this->_helper->messenger->addSuccess($this->view->translate('msg-info-configUpdated', ''));

                $this->_helper->redirector->gotoRoute(array('controller' => 'config', 'selected' => $form->getElement('section')->getValue()), 'ot', true);
            }
        }

        $this->view->assign(array(
            'messages' => $this->_helper->messenger->getMessages(),
            'form'     => $form,
        ));
        
        $this->_helper->pageTitle('ot-config-index:title');
    }
    
    public function importAction()
    {
        $form = new Ot_Form_ImportConfigCsv();
        
        if ($this->_request->isPost()) {
            if ($form->isValid($_POST)) {
                if (!$form->config->receive()) {
                    throw new Exception("Error receiving the file");
                }
 
                $location = $form->config->getFileName();
                
                $options = array();
                
                if (($handle = fopen($location, "r")) !== FALSE) {
                    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {              
                        $options[] = $data;
                    }
                    
                    fclose($handle);
                }                                
                
                unlink($location);
                
                $vr = new Ot_Var_Register();
                
                foreach ($options as $o) {
                    list($key, $value) = $o;
                    
                    $var = $vr->getVar($key);
                    
                    if (!is_null($var)) {
                        $unserialized = unserialize($value);
                        
                        $value = ($unserialized) ? $unserialized : $value;
                        
                        $var->setValue($value);
                    }
                }
                

                $this->_helper->messenger->addSuccess($this->view->translate('msg-info-configUpdated', ''));

                $this->_helper->redirector->gotoRoute(array('controller' => 'config'), 'ot', true);
                
            }
        }
        
        $this->_helper->pageTitle('Import CSV Config File');
        
        $this->view->assign(array(
            'form'     => $form,            
            'messages' => $this->_helper->messenger->getMessages(),
        ));
    }
    
    public function exportAction()
    {
        $this->_helper->viewRenderer->setNeverRender();
        $this->_helper->layout->disableLayout();
        
        header('Content-type: text/csv');
        header('Content-disposition: attachment;filename=configExport-' . date('c') . '.csv');

        $vr = new Ot_Var_Register();
        
        $options = $vr->getVars();
        
        $data = array();
        
        foreach ($options as $key => $o) {

            $value = $o['object']->getValue();
            
            if (is_array($value) || is_object($value)) {
                $value = serialize($value);
            }
                
            $data[] = array($key, $value);
        }
        
        $tmpfname = tempnam("/tmp", "FOO");

        $handle = fopen($tmpfname, "w");
        
        foreach ($data as $d) {
            fputcsv($handle, $d);
        }
        
        echo file_get_contents($tmpfname);
        
        fclose($handle);               

        unlink($tmpfname);
        
    }

}