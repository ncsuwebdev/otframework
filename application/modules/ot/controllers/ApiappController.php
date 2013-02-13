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
     * Displays a list of all the api apps registered with application
     * regardless of the user who registered the app
     */
    public function allAppsAction()
    {
        $apiApp = new Ot_Model_DbTable_ApiApp();
    
        $allApps = $apiApp->fetchAll(null, 'name ASC');
        
        $this->view->allApps = $allApps->toArray();
        
        $this->_helper->pageTitle('ot-apiapp-allApps:title', $this->_helper->varReg('appTitle'));
    }
    
    public function apiDocsAction()
    {
        $apiRegistry = new Ot_Api_Register();
        
        $endpoints = $apiRegistry->getApiEndpoints();
        
        $apiMethods = array('get', 'put', 'post', 'delete');
        
        $data = array();
        
        foreach ($endpoints as &$e) {
            
            $data[$e->getName()] = array();
            
            $classname = get_class($e->getMethod());
            
            $reflection = new ReflectionClass($classname);

            $methods = $reflection->getMethods();
            
            foreach ($methods as $m) {                
                if (in_array($m->getName(), $apiMethods)) {
                    $data[$e->getName()][$m->getName()] = $m->getDocComment();
                }
            }
        }
        
        $this->view->endpoints = $data;
    }
        
    /**
     * Add a new registered api app
     *
     */
    public function addAction()
    {
        $this->_helper->pageTitle('ot-apiapp-add:title');
        
        $apiApp = new Ot_Model_DbTable_ApiApp();
        
        $form = $apiApp->form(array('imagePath' => $this->_getImage(0)));
        
        
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
                
                $this->_helper->messenger->addSuccess('ot-apiapp-add:successfullyRegistered');
                
                $this->_helper->redirector->gotoRoute(array('tab' => 'apps'), 'account', true);
                
            } else {
                $this->_helper->messenger->addError('ot-apiapp-add:problemSubmitting');
            }
        }
        
        $this->view->form = $form;
    }
        
    /**
     * Edit an api app's details
     *
     */
    public function editAction()
    {
        $this->_helper->pageTitle('ot-apiapp-edit:title');
        
        $get = Zend_Registry::get('getFilter');
        
        if (!isset($get->appId)) {
            throw new Ot_Exception_Input('ot-apiapp-edit:appIdNotSet');
        }
        
        $apiApp = new Ot_Model_DbTable_ApiApp();
        
        $thisApp = $apiApp->find($get->appId);
        if (is_null($thisApp)) {
            throw new Ot_Exception_Data('ot-apiapp-edit:appNotfound');
        }
        
        if ($thisApp->accountId != Zend_Auth::getInstance()->getIdentity()->accountId && !$this->_helper->hasAccess('allApiApps')) {
            throw new Ot_Exception_Access('ot-apiapp-edit:notAllowedToEdit');
        }
        
        $form = $apiApp->form(
            array_merge($thisApp->toArray(), array('imagePath' => $this->_getImage($thisApp->imageId)))
        );
        
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
                
                $this->_helper->messenger->addSuccess('ot-apiapp-edit:successfullyModified');
                $this->_helper->redirector->gotoRoute(array('tab' => 'apps'), 'account', true);
                
            } else {
                $this->_helper->messenger->addError('ot-apiapp-edit:problemSubmitting');
            }
        }
        
        $this->view->form = $form;
    }
        
    public function deleteAction()
    {
        $this->_helper->pageTitle('ot-apiapp-delete:title');
        
        $get = Zend_Registry::get('getFilter');
        
        if (!isset($get->appId)) {
            throw new Ot_Exception_Input('ot-apiapp-delete:appIdNotSet');
        }
        
        $apiApp = new Ot_Model_DbTable_ApiApp();
        
        $thisApp = $apiApp->find($get->appId);
        if (is_null($thisApp)) {
            throw new Ot_Exception_Data('ot-apiapp-delete:appNotFound');
        }
        
        if ($thisApp->accountId != Zend_Auth::getInstance()->getIdentity()->accountId && !$this->_helper->hasAccess('allApiApps')) {
            throw new Ot_Exception_Access('ot-apiapp-delete:notAllowedtoEdit');
        }
        
        $form = Ot_Form_Template::delete('deleteApiApp', 'ot-apiapp-delete:deleteLabel');
        
        if ($this->_request->isPost() && $form->isValid($_POST)) {
            $apiApp->delete($thisApp->appId);
                                    
            $this->_helper->messenger->addSuccess('ot-apiapp-delete:applicationRemoved');
            
            $this->_helper->redirector->gotoRoute(array('tab' => 'apps'), 'account', true);
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