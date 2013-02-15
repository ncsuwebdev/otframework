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
    public function init()
    {
        parent::init();

        if (!$this->_helper->hasAccess('index', 'ot_api', $this->_helper->varReg('defaultRole'))) {
            throw new Exception('Default role (' . $this->_helper->varReg('defaultRole') . ') does not have access to ot_api');
        }

        $get = Zend_Registry::get('getFilter');

        $userData = array();

        $userData['accountId'] = Zend_Auth::getInstance()->getIdentity()->accountId;

        if ($get->accountId && $this->_helper->hasAccess('allApps')) {
            $userData['accountId'] = $get->accountId;
        }

        $account = new Ot_Model_DbTable_Account();
        $thisAccount = $account->find($userData['accountId']);

        if (is_null($thisAccount)) {
            throw new Ot_Exception_Data('msg-error-noAccount');
        }

        $userData = array_merge($userData, (array) $thisAccount);
        $this->_userData = $userData;
    }

    /**
     * Displays a list of all the api apps registered with application
     * regardless of the user who registered the app
     */
    public function allAppsAction()
    {
        $apiApp = new Ot_Model_DbTable_ApiApp();
        $account = new Ot_Model_DbTable_Account();

        $allApps = $apiApp->fetchAll(null, 'name ASC')->toArray();

        foreach ($allApps as &$a) {
            $user = $account->find($a['accountId']);

            if (!is_null($user)) {
                $a['user'] = $user;
            }
        }
        unset($a);

        $this->view->allApps = $allApps;

        $this->_helper->pageTitle('ot-apiapp-allApps:title', $this->_helper->varReg('appTitle'));
    }

    public function apiDocsAction()
    {
        $apiRegistry = new Ot_Api_Register();

        $endpoints = $apiRegistry->getApiEndpoints();

        $apiMethods = array('get', 'put', 'post', 'delete');

        $data = array();

        $thisAccount = Zend_Auth::getInstance()->getIdentity();

        $acl = new Ot_Acl('remote');

        $vr = new Ot_Var_Register();

        $role = $vr->getVar('defaultRole')->getValue();

        if (count($thisAccount->role) > 1) {

            $roles = array();
            // Get role names from the list of role Ids
            foreach ($thisAccount->role as $r) {
                $roles[] = $acl->getRole($r);
            }

            // Create a new role that inherits from all the returned roles
            $roleName = implode(',', $roles);

            $role = $roleName;

            $acl->addRole(new Zend_Acl_Role($roleName), $roles);

        } elseif (count($thisAccount->role) == 1) {
            $role = $thisAccount->role[0];
        }

        if ($role == '' || !$acl->hasRole($role)) {
            $role = $vr->getVar('defaultRole')->getValue();
        }

        foreach ($endpoints as &$e) {

            $data[$e->getName()] = array(
                'name'        => $e->getName(),
                'methods'     => array(),
                'description' => $e->getDescription(),
            );

            $classname = get_class($e->getMethod());

            $reflection = new ReflectionClass($classname);

            $methods = $reflection->getMethods();

            foreach ($methods as $m) {

                // the api "module" here is really a kind of placeholder
                $aclResource = 'api_' . strtolower($e->getName());

                if (in_array($m->name, $apiMethods) && $m->class == $classname && $acl->isAllowed($role, $aclResource, $m->name)) {

                    $instructions = 'No instructions provided';

                    if ($m->getDocComment() != '') {
                        $instructions = $this->_cleanComment($m->getDocComment());
                    }

                    $data[$e->getName()]['methods'][$m->getName()] = $instructions;
                }
            }
        }

        $endpoints = array();

        foreach ($data as $key => $val) {
            if (count($val['methods']) != 0) {
                $endpoints[$key] = $val;
            }
        }

        $this->view->endpoints = $endpoints;
        $this->_helper->pageTitle('API Documentation');
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
                    'accountId'   => $this->_userData['accountId'],
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

                $this->_helper->redirector->gotoRoute(array('tab' => 'apps', 'accountId' => $this->_userData['accountId']), 'account', true);

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

        if ($thisApp->accountId != $this->_userData['accountId'] && !$this->_helper->hasAccess('allApps')) {
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
                $this->_helper->redirector->gotoRoute(array('tab' => 'apps', 'accountId' => $this->_userData['accountId']), 'account', true);

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

        if ($thisApp->accountId != $this->_userData['accountId'] && !$this->_helper->hasAccess('allApps')) {
            throw new Ot_Exception_Access('ot-apiapp-delete:notAllowedtoEdit');
        }

        $form = Ot_Form_Template::delete('deleteApiApp', 'ot-apiapp-delete:deleteLabel');

        if ($this->_request->isPost() && $form->isValid($_POST)) {
            $apiApp->delete($thisApp->appId);

            $this->_helper->messenger->addSuccess('ot-apiapp-delete:applicationRemoved');

            $this->_helper->redirector->gotoRoute(array('tab' => 'apps', 'accountId' => $this->_userData['accountId']), 'account', true);
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

    protected function _cleanComment($comment)
    {
        $comment = trim(
            preg_replace('/[ \t]*(?:\/\*\*|\*\/|\*)?[ \t]{0,1}(.*)?/i', '$1', $comment)
        );

        if (substr($comment, -2) == '*/') {
            $comment = trim(substr($comment, 0, -2));
        }

        $comment = str_replace(array("\r\n", "\r"), "\n", $comment);

        return $comment;
    }

}