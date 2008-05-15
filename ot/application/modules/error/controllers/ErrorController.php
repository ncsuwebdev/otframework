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
 * @package    ErrorController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 * @license    BSD License
 * @see        http://itdapps.ncsu.edu
 * @version    SVN: $Id: $
 */

/**
 * Processes any error thrown inthe application
 *
 * @package    ErrorController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 */
class Error_ErrorController extends Internal_Controller_Action
{
    /**
     * Error action to process any errors coming from the application
     *
     */
    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');
        
        $this->getResponse()->clearBody();
        
        switch ($errors->type) {
	        case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
	        case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
	            // 404 error -- controller or action not found
	            $this->getResponse()->setRawHeader('HTTP/1.1 404 Not Found');
	            $this->view->title   = 'ERROR! 404 Error: Page not found';
	            $this->view->message = 'The requested page was not found';
	            break;
	        default:
	            $exception = $errors->exception;
	            if ($exception instanceof Ot_Exception) {
	            	$this->view->title = 'ERROR! ' . $exception->getTitle();
	            } else {
	            	$this->view->title         = 'ERROR! Processing request failed.';
	            	$this->view->showTrackback = true;
	            	$this->view->trackback     = $exception->getTrace();
	            }
	            
	            $this->view->message = $exception->getMessage();
	            break;
        }
    }
}