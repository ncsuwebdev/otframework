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
 * @package    Image_IndexController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Main image index controller
 *
 * @package    Image_IndexController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */
class Ot_ImageController extends Zend_Controller_Action
{
    /**
     * shows the image
     *
     */
    public function indexAction()
    {      
        $this->_helper->viewRenderer->setNeverRender();
        $this->_helper->layout->disableLayout();
                    
        $imageId = $this->_getParam('imageId', null);
        
        if (is_null($imageId)) {
            //$this->getResponse()->setRawHeader('HTTP/1.1 404 Not Found');
            header('HTTP/1.1 404 Not Found');
            return;
        }
        
        $image = new Ot_Model_DbTable_Image();

        $thisImage = $image->find($imageId);     
                
        if (!is_null($thisImage)) {
            
            header("Content-type: " . $thisImage->contentType);
            echo $thisImage->source;
        }
    }
}