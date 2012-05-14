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
        
        $register = new Ot_Api_Register();
        
        $params = $this->_getAllParams();
        
        $endpoint = $params['endpoint'];
                  
        $thisEndpoint = $register->getApiEndpoint($endpoint);
        
        if (!isset($params['key']) || empty($params['key'])) {
            throw new Ot_Exception('You must provide an API key');
        }
        
        
        $returnType = 'json';
        if (isset($params['type']) && in_array(strtolower($returnType), array('json', 'php'))) {
            $returnType = strtolower($params['type']);
        }
        
        $apiApp = new Ot_Model_DbTable_ApiApp();
        
        $thisApp = $apiApp->getAppByKey($params['key']);
        
        $otAccount = new Ot_Model_DbTable_Account();
        $thisAccount = $otAccount->find($thisApp->accountId);
        
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

        } else if (count($thisAccount->role) == 1) {
            $thisAccount->role = $thisAccount->role[0];
        }

        if (!$acl->hasRole($thisAccount->role)) {
            $thisAccount->role = (string)$config->user->defaultRole->val;
        }

        $role = $thisAccount->role;
        
        if ($role == '' || !$acl->hasRole($role)) {
            $role = (string)$config->user->defaultRole->val;
        }
        
        // the api "module" here is really a kind of placeholder
        $aclResource = 'api_' . strtolower($thisEndpoint->getName());

        if (!is_null($thisEndpoint)) {

            $data = array();

            if ($this->_request->isPost()) {
                
                if (!$acl->isAllowed($role, $aclResource, 'post')) {
                    return $this->_output(array('error' => 'You do not have permission to access this endpoint with POST', 'status' => 'failure'), $returnType);
                }
                       
                try {
                    $data = $thisEndpoint->getMethod()->post($params);
                } catch (Ot_Exception $e) {
                    return $this->_output(array('error' => $e->getMessage(), 'status' => 'failure'), $returnType);
                }
                
            } else if ($this->_request->isPut()) {
                
                if (!$acl->isAllowed($role, $aclResource, 'put')) {
                    return $this->_output(array('error' => 'You do not have permission to access this endpoint with PUT', 'status' => 'failure'), $returnType);
                }
                
                try {
                    $data = $thisEndpoint->getMethod()->put($params);
                } catch (Ot_Exception $e) {
                    return $this->_output(array('error' => $e->getMessage(), 'status' => 'failure'), $returnType);
                }
                
                
            } else if ($this->_request->isDelete()) {
                
                if (!$acl->isAllowed($role, $aclResource, 'delete')) {
                    return $this->_output(array('error' => 'You do not have permission to access this endpoint with DELETE', 'status' => 'failure'), $returnType);
                }
                
                try {
                    $data = $thisEndpoint->getMethod()->delete($params);
                }  catch (Ot_Exception $e) {
                    return $this->_output(array('error' => $e->getMessage(), 'status' => 'failure'), $returnType);
                }
                
            } else {
                
                if (!$acl->isAllowed($role, $aclResource, 'get')) {
                    return $this->_output(array('error' => 'You do not have permission to access this endpoint with GET', 'status' => 'failure'), $returnType);
                }
                
                try {
                    $data = $thisEndpoint->getMethod()->get($params);
                }  catch (Ot_Exception $e) {
                    return $this->_output(array('error' => $e->getMessage(), 'status' => 'failure'), $returnType);
                }
            }

        } else {
            return $this->_output(array('error' => 'API endpoint could not be found', 'status' => 'failure'));
        }

        if (!is_array($data)) {
            $data = (array)$data;
        }
        
        
        $ret = array(
            'data' => $data,
            'status' => 'success'
        );
        
        return $this->_output($ret, $returnType);
    }
    
    
    protected function _output($data, $returnType) {
        
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
    
    
    /*
    protected $_class = 'Internal_Api';
        
    protected $_parameters = array();
        
    public function indexAction()
    {
        $api = new Ot_Api();
        $allMethods = $api->describe();

        $this->view->api = $allMethods;
        
        $this->_helper->pageTitle('ot-api-index:title');
    }
    
    public function xmlAction()
    {
        $this->_helper->viewRenderer->setNeverRender();
        $this->_helper->layout->disableLayout();
        
        $access = new Ot_Api_Access();
        
        $request = Oauth_Request::fromRequest();
        
        if (!$access->validate($request, $this->_request->getParam('method'))) {
            return $access->raiseError($access->getMessage(), Ot_Api_Access::API_XML);
        }
        
        $server = new Zend_Rest_Server();
        $server->setClass($this->_class);
        $server->handle($request->getParameters());
    }
    
    public function jsonAction()
    {
        $this->_helper->viewRenderer->setNeverRender();
        $this->_helper->layout->disableLayout();
        
        $access = new Ot_Api_Access();
 
        $request = Oauth_Request::fromRequest();
        
        if (!$access->validate($request, $this->_request->getParam('method'))) {
            return $access->raiseError($access->getMessage(), Ot_Api_Access::API_JSON);
        }
                
        $server = new Zend_Rest_Server();

        $server->setClass($this->_class);
        $server->returnResponse(true); // if this is true, it doesn't send headers or echo, and returns the response instead
        
        $jsoncallback = "";
        
        if ($request->getParameter('jsoncallback') != '') {
            $htmlEntityFilter = new Zend_Filter_HtmlEntities();
            $jsoncallback = $htmlEntityFilter->filter($request->getParameter('jsoncallback'));
        }
        
        $response = $server->handle($request->getParameters());
        
        if (!headers_sent()) {
        	// headers haven't been sent yet, but there's a Content-Type: text/xml in there because
        	// we're using zend rest server to grab xml to parse to json
        	$headers = $server->getHeaders();
            foreach ($headers as $header) {
            	if($header == 'Content-Type: text/xml') {
            	   $header = 'Content-Type: application/json';
            	}
                header($header);
            }
        }

        if ($jsoncallback == "") {
            echo Zend_Json::fromXml($response);
        } else {
            echo $jsoncallback . '(' . Zend_Json::fromXml($response) . ')';
        }
    }
    */
}
