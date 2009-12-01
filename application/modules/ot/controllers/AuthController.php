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
 * @package    Admin_IndexController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Authentication Adapter Controller
 *
 * @package    Ot_AuthController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 */
class Ot_AuthController extends Zend_Controller_Action 
{
    /**
     * shows the homepage
     *
     */
    public function indexAction()
    {       
        $this->_helper->pageTitle('ot-auth-index:title');
        
        $authAdapter = new Ot_Auth_Adapter;
        $adapters = $authAdapter->fetchAll();
        $this->view->adapters = $adapters;
        
        $where = $authAdapter->getAdapter()->quoteInto("enabled = ?", 1);
        $adaptersEnabled = $authAdapter->fetchAll($where)->count();
        $this->view->adaptersEnabled = $adaptersEnabled;
    }
    
    public function toggleAction()
    {
        
        $get = Zend_Registry::get('getFilter');

        if (!isset($get->key)) {
            throw new Ot_Exception_Data('The authentication adapter key is not set in the query string.');
        }
        
        $authAdapter = new Ot_Auth_Adapter;
        $adapter = $authAdapter->find($get->key);
        if (is_null($adapter)) {
            throw new Ot_Exception_Data('No authentication adapter exists with the given key.');
        }
        $this->view->adapter = $adapter;
        if ($adapter->enabled) {
            $this->view->verb = 'disable';
        } else {
            $this->view->verb = 'enable';
        }
        
        $where = $authAdapter->getAdapter()->quoteInto("enabled = ?", 1);
        $adaptersEnabled = $authAdapter->fetchAll($where)->count();

        $form = new Zend_Form();
        $form->setAction('?key=' . $get->key)
             ->setMethod('post')
             ->setAttrib('id', 'toggleAuthAdapter');
       
        $submit = $form->createElement('submit', 'submitButton', array('label' => 'form-button-yes'));
        $submit->setDecorators(array(
                   array('ViewHelper', array('helper' => 'formSubmit'))
                 ));
                 
        $cancel = $form->createElement('button', 'cancel', array('label' => 'form-button-cancel'));
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

        if ($this->_request->isPost() && $form->isValid($_POST)) {
            if ($adapter->enabled) {
                if ($adaptersEnabled > 1) {
                    $where = $authAdapter->getAdapter()->quoteInto('adapterKey = ?', $adapter->adapterKey);
                    $authAdapter->update(array('enabled' => 0), $where);
                } else {
                    throw new Ot_Exception_Data('There must be one authentication adapter enabled at all times.');
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
}
