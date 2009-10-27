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
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Handles calls to the API
 *
 * @package    
 * @subpackage Api_IndexController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 */
class Ot_ApiController extends Zend_Controller_Action  
{
	
	protected $_class = 'Internal_Api';
	
	protected $_parameters = array();
	
	public function init()
	{
        $this->_helper->viewRenderer->setNeverRender();
        $this->_helper->layout->disableLayout();
	}
	
	public function indexAction()
	{
	    $this->_helper->redirector->gotoUrl('/api/documentation');
	}
	
    public function soapAction()
    {
        $server = new SoapServer(null, array('uri' => "soapservice"));
        $server->setClass('Ot_Api_Soap');
        $server->handle();
    }
    
    public function xmlAction()
    {
        $access = new Ot_Api_Access();
        
        $request = Oauth_Request::fromRequest();
        
        if (!$access->validate($request, $this->_request->getParam('method'))) {
            $access->raiseError($access->getMessage());
        }
        
    	$server = new Zend_Rest_Server();
    	$server->setClass($this->_class);
    	$server->handle($request->getParameters()); 
    }
    
    public function jsonAction()
    {
        $access = new Ot_Api_Access();
 
        $request = Oauth_Request::fromRequest();
        
        if (!$access->validate($request, $this->_request->getParam('method'))) {
            $access->raiseError($access->getMessage());
        }
                
    	$server = new Zend_Rest_Server();

    	$server->setClass($this->_class);
    	$server->returnResponse(true);
    	
        $jsoncallback = "";
        
        if ($request->getParameter('jsoncallback') != '') {
           $jsoncallback = $request->getParameter('jsoncallback');
        }
        
        $response = $server->handle($request->getParameters());

        if ($jsoncallback == "") {
            echo Zend_Json::fromXml($response);
        } else {
            echo $jsoncallback . '(' . Zend_Json::fromXml($response) . ')';
        }
    }
}
