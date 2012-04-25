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
 * @package    Ot_Model_DbTable_ApiApp
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Model to do deal with Api apps
 *
 * @package    Ot_Model_DbTable_ApiApp
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 *
 */
class Ot_Model_DbTable_ApiApp extends Ot_Db_Table
{
    /**
     * Name of the table in the database
     *
     * @var string
     */
    protected $_name = 'tbl_ot_api_app';

    /**
     * Primary key of the table
     *
     * @var string
     */
    protected $_primary = 'appId';
    
    public function getAppByKey($key)
    {
        $where = $this->getAdapter()->quoteInto('apiKey = ?', $key);

        $result = $this->fetchAll($where);

        if ($result->count() != 1) {
                return null;
        }

        return $result->current();
    }    

    public function getAppsForAccount($accountId)
    {
        $where = $this->getAdapter()->quoteInto('accountId = ?', $accountId);

        return $this->fetchAll($where, 'name');
    }    
    
    public function insert(array $data)
    {
        $data['apiKey'] = $this->_generateApiKey();
        
        $data = array_merge($data);

        return parent::insert($data);
    }
    
    
    public function delete($appId)
    {
        $where = $this->getAdapter()->quoteInto('appId = ?', $appId);

        return parent::delete($where);
    }
    
    private function _generateApiKey()
    {
        return sha1(time() + microtime() + rand(1, 1000000));
    }
    
    public function regenerateApiKey($appId)
    {
        $data = array(
            'appId' => $appId,
            'apiKey'   => $this->_generateApiKey()
        );        
        
        return parent::update($data);
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
        $form->setAttrib('id', 'apiAppForm')->setAttrib('enctype', 'multipart/form-data')->setDecorators(
            array(
                'FormElements',
                array('HtmlTag', array('tag' => 'div', 'class' => 'zend_form')),
                'Form',
            )
        );
             
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
              ->setAttrib('style', 'height: 100px; width: 350px;')
              ->setValue((isset($values['description']) ? $values['description'] : ''));
              
        $website = $form->createElement('text', 'website', array('label' => 'Application Website:'));
        $website->setRequired(false)
              ->addFilter('StringTrim')
              ->addFilter('StripTags')
              ->setAttrib('maxlength', '255')
              ->setValue((isset($values['website']) ? $values['website'] : ''));   

        $submit = $form->createElement('submit', 'submitButton', array('label' => 'Submit'));
        $submit->setDecorators(array(array('ViewHelper', array('helper' => 'formSubmit'))));

        $cancel = $form->createElement('button', 'cancel', array('label' => 'Cancel'));
        $cancel->setAttrib('id', 'cancel');
        $cancel->setDecorators(array(array('ViewHelper', array('helper' => 'formButton'))));
        
        $form->addElements(array($image, $name, $description, $website));

        $form->setElementDecorators(
            array(
                'ViewHelper',
                'Errors',
                array('HtmlTag', array('tag' => 'div', 'class' => 'elm')),
                array('Label', array('tag' => 'span')),
            )
        )->addElements(array($submit, $cancel));
                
        $image->addPrefixPath('Ot_Form_Decorator', 'Ot/Form/Decorator', 'decorator');
        $image->addDecorator('File');
        $image->addDecorator('Imageupload', array('id' => 'applicationIconImage', 'src' => $values['imagePath']));

        if (isset($values['appId'])) {

            $appId = $form->createElement('hidden', 'appId');
            $appId->setValue($values['appId']);
            $appId->setDecorators(array(array('ViewHelper', array('helper' => 'formHidden'))));

            $form->addElement($appId);
        }
        return $form;
    }
}