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
 * @package    Ot_ApiappController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    BSD License
 * @version    SVN: $Id: $
 */

/**
 * Allows the user register and grant access to OAuth enabled apps
 *
 * @package    Ot_ApiappController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 *
 */
class Ot_ApiappController extends Zend_Controller_Action
{
        
    /**
     * Displays a list of all a user's registered consumers
     *
     */
    public function indexAction()
    {
        $apiApp = new Ot_Model_DbTable_ApiApp();
        
        $apps = $apiApp->getAppsForAccount(Zend_Auth::getInstance()->getIdentity()->accountId);
        
        $this->view->apiApps = $apps->toArray();
        
        $config = Zend_Registry::get('config');

        $this->_helper->pageTitle('ot-oauth-index:title', $config->user->appTitle->val);
        $this->view->messages = $this->_helper->flashMessenger->getMessages();
    }
        
    /**
     * Displays a list of all the api apps registered with application
     * regardless of the user who registered the app
     */
    public function allApiAppsAction()
    {
        $apiApp = new Ot_Model_DbTable_ApiApp();
    
        $allApps = $apiApp->fetchAll(null, 'name ASC');
        
        $this->view->allApiApps = $allApps->toArray();
        
        $config = Zend_Registry::get('config');
        
        $this->_helper->pageTitle('ot-oauth-allConsumers:title', $config->user->appTitle->val);
    }
    
    public function apiDocsAction()
    {
        
    } 
           
        
    /**
     * Displays the details about a registered consumer
     *
     */
    public function detailsAction()
    {                
        $get = Zend_Registry::get('getFilter');
        
        if (!isset($get->appId)) {
            throw new Ot_Exception_Input('ot-oauth-details:consumerIdNotSet');
        }
        
        if (isset($get->all)) {
            $this->view->all = true;
        }
        
        $apiApp = new Ot_Model_DbTable_ApiApp();
        
        $thisApp = $apiApp->find($get->appId);
        if (is_null($thisApp)) {
            throw new Ot_Exception_Data('ot-oauth-details:consumerNotFound');
        }
        
        if ($thisApp->accountId != Zend_Auth::getInstance()->getIdentity()->accountId
            && !$this->_helper->hasAccess('allApiApps')) {
                throw new Ot_Exception_Access('ot-oauth-details:notAllowedToEdit');
        }
        
        $this->view->apiApp = $thisApp;
        
        $this->_helper->pageTitle('ot-oauth-details:title', $thisApp->name);
        $this->view->messages = $this->_helper->flashMessenger->getMessages();
    }
        
    /**
     * Add a new registered api app
     *
     */
    public function addAction()
    {
        $this->_helper->pageTitle('ot-oauth-add:title');
        
        $apiApp = new Ot_Model_DbTable_ApiApp();
        
        $form = $apiApp->form(array('imagePath' => $this->_getImage(0)));
        
        $messages = array();
        if ($this->_request->isPost()) {
            if ($form->isValid($_POST)) {
                $data = array(
                    'name'        => $form->getValue('name'),
                    'description' => $form->getValue('description'),
                    'website'     => $form->getValue('website'),
                    'accountId'   => Zend_Auth::getInstance()->getIdentity()->accountId,
                );
                        
                $imageValue = $form->getValue('image');
                
                if ($imageValue != '/tmp/' && $imageValue != '') {
                            
                    $image = new Ot_Model_DbTable_Image();
        
                    $image->resizeImage($form->image->getFileName(), 64, 64);
        
                    $iData = array('source' => file_get_contents(trim($form->image->getFileName())));
        
                    $data['imageId'] = $image->insert($iData);
                }                                
                    
                $appId = $apiApp->insert($data);
                
                $this->_helper->flashMessenger->addMessage('ot-oauth-add:successfullyRegistered');
                
                $this->_helper->redirector->gotoRoute(array('action' => 'details', 'appId' => $appId), 'apiapp', true);
                
            } else {
                $messages[] = $this->view->translate('ot-oauth-add:problemSubmitting');
            }
        }
        
        $this->view->messages = $messages;
        $this->view->form     = $form;
    }
    
    public function regenerateKeyAction()
    {
        $get = Zend_Registry::get('getFilter');
        
        if (!isset($get->appId)) {
            throw new Ot_Exception_Input('ot-oauth-edit:consumerIdNotSet');
        }
        
        $apiApp = new Ot_Model_DbTable_ApiApp();
        
        $thisApp = $apiApp->find($get->appId);
        if (is_null($thisApp)) {
            throw new Ot_Exception_Data('ot-oauth-edit:consumerNotfound');
        }
        
        if ($thisApp->accountId != Zend_Auth::getInstance()->getIdentity()->accountId && !$this->_helper->hasAccess('allApiApps')) {
            throw new Ot_Exception_Access('ot-oauth-edit:notAllowedToEdit');
        }
        
        $apiApp->regenerateApiKey($get->appId);
        
        $this->_helper->redirector->gotoRoute(array('action' => 'details', 'appId' => $get->appId), 'apiapp', true);
        
    }
        
    /**
     * Edit an api app's details
     *
     */
    public function editAction()
    {
        $this->_helper->pageTitle('ot-oauth-edit:title');
        
        $get = Zend_Registry::get('getFilter');
        
        if (!isset($get->appId)) {
            throw new Ot_Exception_Input('ot-oauth-edit:consumerIdNotSet');
        }
        
        $apiApp = new Ot_Model_DbTable_ApiApp();
        
        $thisApp = $apiApp->find($get->appId);
        if (is_null($thisApp)) {
            throw new Ot_Exception_Data('ot-oauth-edit:consumerNotfound');
        }
        
        if ($thisApp->accountId != Zend_Auth::getInstance()->getIdentity()->accountId && !$this->_helper->hasAccess('allApiApps')) {
            throw new Ot_Exception_Access('ot-oauth-edit:notAllowedToEdit');
        }
        
        $form = $apiApp->form(
            array_merge($thisApp->toArray(), array('imagePath' => $this->_getImage($thisApp->imageId)))
        );
        
        $messages = array();
        if ($this->_request->isPost()) {
            
            if ($form->isValid($_POST)) {
                $data = array(
                    'appId'       => $thisApp->appId,
                    'name'        => $form->getValue('name'),
                    'description' => $form->getValue('description'),
                    'website'     => $form->getValue('website'),
                );
                
                $imageValue = $form->getValue('image');
                
                if ($imageValue != '/tmp/' && $imageValue != '') {

                    $image = new Ot_Model_DbTable_Image();
                
                    $image->resizeImage($form->image->getFileName(), 64, 64);
                
                    $iData = array('source' => file_get_contents(trim($form->image->getFileName())));

                    if (isset($thisApp->imageId) && $thisApp->imageId != 0) {
                        $image->deleteImage($thisApp->imageId);
                    }
                                    
                    $data['imageId'] = $image->insert($iData);
                }                                        
                        
                $apiApp->update($data, null);
                
                $this->_helper->flashMessenger->addMessage('ot-oauth-edit:successfullyModified');
                $this->_helper->redirector->gotoRoute(array('action' => 'details', 'appId' => $thisApp->appId), 'apiapp', true);
                
            } else {
                $messages[] = 'ot-oauth-edit:problemSubmitting';
            }
        }
        
        $this->view->messages = $messages;
        $this->view->form     = $form;
    }
        
    public function deleteAction()
    {
        $this->_helper->pageTitle('ot-oauth-delete:title');
        
        $get = Zend_Registry::get('getFilter');
        
        if (!isset($get->appId)) {
            throw new Ot_Exception_Input('ot-oauth-delete:consumerIdNotSet');
        }
        
        $apiApp = new Ot_Model_DbTable_ApiApp();
        
        $thisApp = $apiApp->find($get->appId);
        if (is_null($thisApp)) {
            throw new Ot_Exception_Data('ot-oauth-delete:consumerNotFound');
        }
        
        if ($thisApp->accountId != Zend_Auth::getInstance()->getIdentity()->accountId && !$this->_helper->hasAccess('allApiApps')) {
            throw new Ot_Exception_Access('ot-oauth-delete:notAllowedtoEdit');
        }
        
        $form = Ot_Form_Template::delete('deleteApiApp', 'ot-oauth-delete:deleteLabel');
        
        if ($this->_request->isPost() && $form->isValid($_POST)) {
            $apiApp->delete($thisApp->appId);
                                    
            $this->_helper->flashMessenger->addMessage('ot-oauth-delete:applicationRemoved');
            
            $this->_helper->redirector->gotoRoute(array(), 'apiapp', true);
        }
        
        $this->view->form = $form;
        $this->view->apiApp = $thisApp;                
    }
    
    protected function _getImage($imageId)
    {
        if ($imageId == 0) {
                return $this->view->baseUrl() . '/ot/images/consumer.png';
        }
        
        return $this->view->url(array('imageId' => $imageId), 'image');
    }
}