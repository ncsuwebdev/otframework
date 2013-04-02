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
 * @package    Api_IndexController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Handles calls to the API
 *
 * @package
 * @subpackage Api_IndexController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of
 *             Information Technology
 */
class Ot_ApiController extends Zend_Controller_Action
{

    public function init()
    {
        set_time_limit(0);

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNeverRender();

        parent::init();
    }

    public function indexAction()
    {                
        $returnType = 'json';
        
        try {
            $apiRegister = new Ot_Api_Register();

            $vr = new Ot_Config_Register();

            $params = $this->_getAllParams();

            if (isset($params['type']) && in_array(strtolower($returnType), array('json', 'php'))) {
                $returnType = strtolower($params['type']);
            }
            
            if (!isset($params['endpoint']) || empty($params['endpoint'])) {
                return $this->_validOutput(array('message' => 'Welcome to the ' . $vr->getVar('appTitle')->getValue() . ' API.  You will need an API key to get any further. Visit ' . Zend_Registry::get('siteUrl') . '/account to get one.'), $returnType);
            }

            $endpoint = $params['endpoint'];

            $thisEndpoint = $apiRegister->getApiEndpoint($endpoint);

            if (is_null($thisEndpoint)) {
                return $this->_errorOutput('Invalid Endpoint', $returnType, 404);
            }

            if (!isset($params['key']) || empty($params['key'])) {
                return $this->_errorOutput('You must provide an API key', $returnType, 403);
            }        

            $apiApp = new Ot_Model_DbTable_ApiApp();

            $thisApp = $apiApp->getAppByKey($params['key']);

            if (is_null($thisApp)) {
                return $this->_errorOutput('Invalid API key', $returnType, 403);
            }

            $otAccount = new Ot_Model_DbTable_Account();
            $thisAccount = $otAccount->getByAccountId($thisApp->accountId);

            if (is_null($thisAccount)) {
                return $this->_errorOutput('No user found for this API key', $returnType, 403);
            }
            
            $acl = new Ot_Acl('remote');

            if (count($thisAccount->role) > 1) {

                $roles = array();
                // Get role names from the list of role Ids
                foreach ($thisAccount->role as $r) {
                    $roles[] = $acl->getRole($r);
                }

                // Create a new role that inherits from all the returned roles
                $roleName = implode(',', $roles);

                $thisAccount->role = $roleName;

                $acl->addRole(new Zend_Acl_Role($roleName), $roles);

            } elseif (count($thisAccount->role) == 1) {
                $thisAccount->role = array_pop($thisAccount->role);
            }

            if (!$acl->hasRole($thisAccount->role)) {
                $thisAccount->role = $vr->getVar('defaultRole')->getValue();
            }

            $role = $thisAccount->role;

            if ($role == '' || !$acl->hasRole($role)) {
                $role = $vr->getVar('defaultRole')->getValue();
            }

            // the api "module" here is really a kind of placeholder
            $aclResource = 'api_' . strtolower($thisEndpoint->getName());

            Zend_Auth::getInstance()->getStorage()->write($thisAccount);
            
        } catch (Exception $e) {
            return $this->_errorOutput($e->getMessage(), $returnType);
        }

        $data = array();
        
        $apiObject = $thisEndpoint->getEndpointObj();

        if ($this->_request->isPost()) {

            if (!$acl->isAllowed($role, $aclResource, 'post')) {
                return $this->_errorOutput('You do not have permission to access this endpoint with POST', $returnType, 403);
            }

            try {
                $data = $apiObject->post($params);
            } catch (Exception $e) {
                return $this->_errorOutput($e->getMessage(), $returnType);
            }

        } else if ($this->_request->isPut()) {

            if (!$acl->isAllowed($role, $aclResource, 'put')) {
                return $this->_errorOutput('You do not have permission to access this endpoint with PUT', $returnType, 403);
            }

            try {
                $data = $apiObject->put($params);
            } catch (Exception $e) {
                return $this->_errorOutput($e->getMessage(), $returnType);
            }


        } else if ($this->_request->isDelete()) {

            if (!$acl->isAllowed($role, $aclResource, 'delete')) {
                return $this->_errorOutput('You do not have permission to access this endpoint with DELETE', $returnType, 403);
            }

            try {
                $data = $apiObject->delete($params);
            }  catch (Exception $e) {
                return $this->_errorOutput($e->getMessage(), $returnType);
            }

        } else {

            if (!$acl->isAllowed($role, $aclResource, 'get')) {
                return $this->_errorOutput('You do not have permission to access this endpoint with GET', $returnType, 403);
            }

            try {
                $data = $apiObject->get($params);
            }  catch (Exception $e) {
                return $this->_errorOutput($e->getMessage(), $returnType);
            }
        }
            
        return $this->_validOutput($data, $returnType);
    }

    protected function _validOutput($data, $returnType)
    {               
        if (!is_array($data)) {
            $data = (array)$data;
        }
        
        $ret = array(
            'data' => $data,
            'status' => 'success'
        );
        
        return $this->_output($ret, $returnType, 200);
    }
    
    protected function _errorOutput($errorMessage, $returnType, $code = 500)
    {
        $ret = array(
            'status'       => 'failure',
            'errorMessage' => $errorMessage,
        );
        
        return $this->_output($ret, $returnType, $code);
    }
    
    protected function _output($data, $returnType, $code = 200) 
    {
        $this->getResponse()->setHttpResponseCode($code);
        
        switch ($returnType) {
            case 'php':
                header('Content-type: text/php');
                echo serialize($data);
                break;
            default:
                header('Content-Type: application/json');
                echo Zend_Json::encode($data);
                break;
        }

        return true;
    }
}
