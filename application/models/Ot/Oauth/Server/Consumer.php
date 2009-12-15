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
 * @package    Ot_Oauth_Consumer
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Model to do deal with Oauth consumers
 *
 * @package    Ot_Oauth_Server_Consumer
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 *
 */
class Ot_Oauth_Server_Consumer extends Ot_Db_Table
{
    /**
     * Name of the table in the database
     *
     * @var string
     */
    protected $_name = 'tbl_ot_oauth_server_consumer';

    /**
     * Primary key of the table
     *
     * @var string
     */
    protected $_primary = 'consumerId';
    
    public function getConsumerByKey($consumerKey)
    {
    	$where = $this->getAdapter()->quoteInto('consumerKey = ?', $consumerKey);
    	
    	$result = $this->fetchAll($where);
    	
    	if ($result->count() != 1) {
    		return null;
    	}
    	
    	return $result->current();
    }
    

    
    public function getConsumersForRegisteredAccounnt($accountId)
    {
    	$where = $this->getAdapter()->quoteInto('registeredAccountId = ?', $accountId);
    	
    	return $this->fetchAll($where, 'name');
    }
    
    public function deleteConsumer($consumerId)
    {
    	$dba = $this->getAdapter();
    	
    	$dba->beginTransaction();
    	
    	$thisConsumer = $this->find($consumerId);
    	if (is_null($thisConsumer)) {
    		return;
    	}
    	
    	$where = $dba->quoteInto('consumerId = ?', $consumerId);
    	
    	try {
    		$this->delete($where);
    	} catch (Exception $e) {
    		$dba->rollback();
    		throw $e;
    	}
    	
    	$st = new Ot_Oauth_Server_Token();
    	
    	try {
    		$st->delete($where);
    	} catch (Exception $e) {
    		$dba->rollback();
    		throw $e;
    	}
    	
    	if (isset($thisConsumer->imageId) && $thisConsumer->imageId != 0) {
    		$image = new Ot_Image();
    		try {
	    		$image->deleteImage($thisConsumer->imageId);
    		} catch (Exception $e) {
    			$dba->rollback();
    			throw $e;
    		}
	    }    	
    	
    	$dba->commit();
    }
    
    public function insert(array $data)
    {
    	$data = array_merge($data, $this->_generateConsumerKeySecret());
    	
    	return parent::insert($data);
    }
    
    public function resetConsumerKeySecret($consumerId)
    {
    	$data = array(
    		'consumerId' => $consumerId,
    	);
    	
    	$data = array_merge($data, $this->_generateConsumerKeySecret());
    	
    	return $this->update($data, null);
    }
    
    protected function _generateConsumerKeySecret()
    {
    	return array('consumerKey' => md5(time()), 'consumerSecret' => md5(md5(time() + time())));
    }
    
    /**
     * Gets the form for adding and editing a ticket
     *
     * @param array $values
     * @return Zend_Form
     */
    public function form($values = array())
    {
        $form = new Zend_Form();
        $form->setAttrib('id', 'consumerForm')
             ->setAttrib('enctype', 'multipart/form-data')
             ->setDecorators(array(
                     'FormElements',
                     array('HtmlTag', array('tag' => 'div', 'class' => 'zend_form')),
                     'Form',
             ));
             
        $image = $form->createElement('file', 'image', array('label' => 'Application Icon:'));
        $image->addValidator('Count', false, 1)     // ensure only 1 file
              ->addValidator('Size', false, 204800) // limit to 200K
              ->addValidator('Extension', false, 'jpg,jpeg,png'); // only JPEG, PNG
                           
        $name = $form->createElement('text', 'name', array('label' => 'Application Name:'));
        $name->setRequired(true)
              ->addFilter('StringTrim')
              ->addFilter('StripTags')
              ->setAttrib('maxlength', '128')
              ->setValue((isset($values['name']) ? $values['name'] : ''));
              
        $description = $form->createElement('textarea', 'description', array('label' => 'Description:'));
        $description->setRequired(true)
              ->addFilter('StringTrim')
              ->addFilter('StripTags')
              ->setValue((isset($values['description']) ? $values['description'] : ''));
              
        $website = $form->createElement('text', 'website', array('label' => 'Application Website:'));
        $website->setRequired(false)
              ->addFilter('StringTrim')
              ->addFilter('StripTags')
              ->setAttrib('maxlength', '255')
              ->setValue((isset($values['website']) ? $values['website'] : ''));   
              
        $callbackUrl = $form->createElement('text', 'callbackUrl', array('label' => 'Callback URL:'));
        $callbackUrl->setRequired(false)
              ->addFilter('StringTrim')
              ->addFilter('StripTags')
              ->setAttrib('maxlength', '255')
              ->setValue((isset($values['callbackUrl']) ? $values['callbackUrl'] : ''));      

        $submit = $form->createElement('submit', 'submitButton', array('label' => 'Submit'));
        $submit->setDecorators(array(
                   array('ViewHelper', array('helper' => 'formSubmit'))
                 ));

        $cancel = $form->createElement('button', 'cancel', array('label' => 'Cancel'));
        $cancel->setAttrib('id', 'cancel');
        $cancel->setDecorators(array(
                   array('ViewHelper', array('helper' => 'formButton'))
                ));
        
        $form->addElements(array($image, $name, $description, $website, $callbackUrl));

        $form->setElementDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array('HtmlTag', array('tag' => 'div', 'class' => 'elm')),
                  array('Label', array('tag' => 'span')),
              ))
             ->addElements(array($submit, $cancel));
                
        
        $image->addPrefixPath('Ot_Form_Decorator', 'Ot/Form/Decorator', 'decorator');
        $image->addDecorator('File');
       	$image->addDecorator('Imageupload', 
       		array(
       			'id'        => 'applicationIconImage',
       			'src'       => $values['imagePath'],
       		));

        if (isset($values['consumerId'])) {

            $consumerId = $form->createElement('hidden', 'consumerId');
            $consumerId->setValue($values['consumerId']);
            $consumerId->setDecorators(array(
                array('ViewHelper', array('helper' => 'formHidden'))
            ));

            $form->addElement($consumerId);
        }
        return $form;
    }
}