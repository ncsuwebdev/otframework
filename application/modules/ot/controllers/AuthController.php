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
 * @package    Ot_IndexController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Authentication Adapter Controller
 *
 * @package    Ot_AuthController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */
class Ot_AuthController extends Zend_Controller_Action
{
    /**
     * shows the list of adapters
     *
     */
    public function indexAction()
    {               
        $this->view->acl = array(
            'toggle' => $this->_helper->hasAccess('toggle'),
            'edit'   => $this->_helper->hasAccess('edit'),
        );        
        
        $authAdapter = new Ot_Model_DbTable_AuthAdapter();
        $adapters = $authAdapter->fetchAll(null, 'displayOrder');
        $this->view->adapters = $adapters;
        
        $this->view->numEnabledAdapters = $authAdapter->getNumberOfEnabledAdapters();
        $this->_helper->pageTitle('ot-auth-index:title');
    }
    
    public function toggleAction()
    {
        
        $get = Zend_Registry::get('getFilter');

        if (!isset($get->key)) {
            throw new Ot_Exception_Data('ot-auth-toggle:keyNotSet');
        }
        
        $authAdapter = new Ot_Model_DbTable_AuthAdapter();
        $adapter = $authAdapter->find($get->key);
        if (is_null($adapter)) {
            throw new Ot_Exception_Data('ot-auth-toggle:noAdapter');
        }
        $this->view->adapter = $adapter;
        if ($adapter->enabled) {
            $this->view->verb = 'disable';
        } else {
            $this->view->verb = 'enable';
        }
        
        $numEnabledAdapters = $authAdapter->getNumberOfEnabledAdapters();

        $form = new Zend_Form();
        $form->setAction('?key=' . $get->key)->setMethod('post')->setAttrib('id', 'toggleAuthAdapter');
       
        $submit = $form->createElement('submit', 'submitButton', array('label' => 'form-button-yes'));
        $submit->setDecorators(array(array('ViewHelper', array('helper' => 'formSubmit'))));
                 
        $cancel = $form->createElement('button', 'cancel', array('label' => 'form-button-cancel'));
        $cancel->setAttrib('id', 'cancel');
        $cancel->setDecorators(array(array('ViewHelper', array('helper' => 'formButton'))));
                        
        $form->setElementDecorators(
            array(
                'ViewHelper',
                'Errors',      
                array('HtmlTag', array('tag' => 'div', 'class' => 'elm')), 
                array('Label', array('tag' => 'span')),      
            )
        )->addElements(array($submit, $cancel));

        if ($this->_request->isPost() && $form->isValid($_POST)) {
            if ($adapter->enabled) {
                if ($numEnabledAdapters > 1) {
                    $data = array('adapterKey' => $adapter->adapterKey, 'enabled' => 0);
                    $authAdapter->update($data, null);
                } else {
                    throw new Ot_Exception_Data('ot-auth-toggle:mustBeOneAdapter');
                }
            } else {
                $data = array('enabled' => 1, 'adapterKey' => $adapter->adapterKey);
                $authAdapter->update($data, null);
            }
            $this->_helper->redirector->gotoRoute(array('controller' => 'auth'), 'ot', true);
        }

        $this->_helper->pageTitle('ot-auth-toggle:title');
        $this->view->form = $form;
    }
    
    public function editAction()
    {
        $get = Zend_Registry::get('getFilter');
        
        if (!isset($get->key)) {
            throw new Ot_Exception_Input('ot-auth-edit:valueNotFound');
        }
        
        $authAdapter = new Ot_Model_DbTable_AuthAdapter();
        $thisAdapter = $authAdapter->find($get->key);
        if (is_null($thisAdapter)) {
            throw new Ot_Exception_Data('ot-auth-edit:noAdapter');
        }     

        $form = $authAdapter->form($thisAdapter->toArray());
        
        
        if ($this->_request->isPost()) {
            if ($form->isValid($_POST)) {
                $data = array(
                    'adapterKey'  => $thisAdapter->adapterKey,
                    'name'        => $form->getValue('name'),
                    'description' => $form->getValue('description'),
                );
                
                $authAdapter->update($data, null);
                
                $this->_helper->redirector->gotoRoute(array('controller' => 'auth'), 'ot', true);
            } else {
                $this->_helper->messenger->addError('ot-auth-edit:problemSubmitting');
            }
        }
        
        $this->view->form = $form;
        $this->_helper->pageTitle('ot-auth-edit:title');
    }
    
    /**
     * Updates the display order of the attributes from the AJAX request
     *
     */
    public function saveAdapterOrderAction()
    {
        $this->_helper->viewRenderer->setNeverRender();
        $this->_helper->layout->disableLayout();

        if ($this->_request->isPost()) {
            
            $post = Zend_Registry::get('postFilter');
            
            if (!isset($post->adapterKeys)) {
                $ret = array('rc' => 0, 'msg' => $this->view->translate('msg-error-attributeIdsNotSet'));
                echo Zend_Json_Encoder::encode($ret);
                return;
            }

            $adapterKeys = $post->adapterKeys;
            
            foreach ($adapterKeys as &$key) {
                $key = substr($key, strpos($key, '_')+1);
            }

            $adapter = new Ot_Model_DbTable_AuthAdapter();
            
            try {
                $adapter->updateAdapterOrder($adapterKeys);
                $ret = array('rc' => 1, 'msg' => $this->view->translate('msg-info-newOrderSaved'));
                echo Zend_Json_Encoder::encode($ret);
                return;
            } catch (Exception $e) {
                $ret = array('rc' => 0, 'msg' => $this->view->translate('msg-error-orderNotSaved', $e->getMessage()));
                echo Zend_Json_Encoder::encode($ret);
                return;
            }
        }
    }    
}
