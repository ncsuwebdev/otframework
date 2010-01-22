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
 * @package    Ot_Form_Tempate
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    BSD License
 * @version    SVN: $Id: $
 */

/**
 * Create default form templates from commonly used forms
 *
 * @package   Ot_Form_Template
 * @category  Library
 * @copyright Copyright (c) 2007 NC State University Office of Information Technology
 *
 */
class Ot_Form_Template
{
	/**
	 * Creates a generic delete form
	 *
	 * @param string $formId
	 * @param string $deleteLabel
	 * @param string $cancelLabel
	 * @return Zend_Form object
	 */
	public static function delete($formId = null, $deleteLabel = 'form-button-delete',
	   $cancelLabel = 'form-button-cancel')
	{
        $form = new Zend_Form();
        $form->setAttrib('id', $formId);
        
        $submit = $form->createElement('submit', 'deleteButton', array('label' => $deleteLabel));
        $submit->setDecorators(array(
                   array('ViewHelper', array('helper' => 'formSubmit'))
                 ));
        
        $cancel = $form->createElement('button', 'cancel', array('label' => $cancelLabel));
        $cancel->setAttrib('id', 'cancel');
        $cancel->setDecorators(array(
                   array('ViewHelper', array('helper' => 'formButton'))
                ));
                             
        $form->addElements(array($submit, $cancel));  

        return $form;
	}
}