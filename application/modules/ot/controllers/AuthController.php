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

        $this->view->assign(array(
            'adapters'           => $adapters,
            'numEnabledAdapters' => $authAdapter->getNumberOfEnabledAdapters()
        ));

        $this->view->headScript()->appendFile('//ajax.googleapis.com/ajax/libs/jqueryui/1.10.1/jquery-ui.min.js');
        $this->_helper->pageTitle('ot-auth-index:title');
    }

    /**
     * Toggles the availability of the adapter
     *
     * @throws Ot_Exception_Data
     * @throws Ot_Exception_Access
     */
    public function toggleAction()
    {
        $key = $this->_getParam('key', null);

        if (is_null($key)) {
            throw new Ot_Exception_Data('ot-auth-toggle:keyNotSet');
        }

        $authAdapter = new Ot_Model_DbTable_AuthAdapter();
        $adapter = $authAdapter->find($key);

        if (is_null($adapter)) {
            throw new Ot_Exception_Data('ot-auth-toggle:noAdapter');
        }

        $numEnabledAdapters = $authAdapter->getNumberOfEnabledAdapters();

        if ($numEnabledAdapters < 1) {
            throw new Ot_Exception_Data('ot-auth-toggle:mustBeOneAdapter');
        }
        
        $auth = Zend_Auth::getInstance();
        
        $identity = $auth->getIdentity();
        
        if ($identity->realm == $adapter->adapterKey) {
            throw new Ot_Exception_Access('You can not toggle the status of the authentication adapter that you are currently logged in with.');
        }        

        if ($this->_request->isPost()) {

            $data = array(
                'adapterKey' => $adapter->adapterKey,
                'enabled'    => ($adapter->enabled) ? 0 : 1,
            );

            $authAdapter->update($data, null);


            $this->_helper->redirector->gotoRoute(array('controller' => 'auth'), 'ot', true);

        } else {
            throw new Ot_Exception_Access('You are not allowed to access this method directly');
        }
    }

    /**
     * Allows for the editing of the meta data attached to an auth adapter
     *
     * @throws Ot_Exception_Input
     * @throws Ot_Exception_Data
     */
    public function editAction()
    {
        $key = $this->_getParam('key', null);

        if (is_null($key)) {
            throw new Ot_Exception_Data('ot-auth-toggle:keyNotSet');
        }

        $authAdapter = new Ot_Model_DbTable_AuthAdapter();
        $adapter = $authAdapter->find($key);

        if (is_null($adapter)) {
            throw new Ot_Exception_Data('ot-auth-toggle:noAdapter');
        }

        $form = new Ot_Form_AuthAdapter();

        $form->populate($adapter->toArray());

        if ($this->_request->isPost()) {
            if ($form->isValid($_POST)) {

                $data = array(
                    'adapterKey'  => $adapter->adapterKey,
                    'name'        => $form->getValue('name'),
                    'description' => $form->getValue('description'),
                );

                $authAdapter->update($data, null);

                $this->_helper->redirector->gotoRoute(array('controller' => 'auth'), 'ot', true);
            } else {
                $this->_helper->messenger->addError('ot-auth-edit:problemSubmitting');
            }
        }

        $this->_helper->pageTitle('ot-auth-edit:title', $adapter->name);

        $this->view->assign(array(
            'form' => $form
        ));
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
