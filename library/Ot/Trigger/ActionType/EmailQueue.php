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
 * @package    Ot_Trigger_Plugin_EmailQueue
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Trigger plugin to queue an email when an action happens
 *
 * @package    Ot_Trigger_Plugin_EmailQueue
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */
class Ot_Trigger_ActionType_EmailQueue extends Ot_Trigger_ActionType_Abstract
{
    protected $_form = 'Ot_Form_TriggerActionTypeEmailQueue';
    
    protected $_dbtable = 'Ot_Model_DbTable_TriggerActionTypeEmailQueue';
    
    /**
     * Action called when a trigger is executed.
     *
     * @param array $data
     */
    public function dispatch(array $data)
    {
        $eq = new Ot_Model_DbTable_EmailQueue();
        
        $mail = new Zend_Mail();

        $to = explode(',', $data['to']);
        
        $toFiltered = array();
        foreach ($to as $t) {
            if (trim($t) != '') {
                $toFiltered[] = trim($t);
            }
        }
                
        if (count($toFiltered) == 0) {
            return; 
        }
                
        foreach ($toFiltered as $t) {
            $mail->addTo($t);
        }
        
        $mail->setFrom($data['from'], $data['fromName']);
        $mail->setSubject($data['subject']);
        $mail->setBodyText($data['body']);
        
        $eData = array(
            'zendMailObject' => $mail,
            'attributeName'  => 'triggerActionId',
            'attributeId'    => $data['triggerActionId'],
        );
        
        $eq->queueEmail($eData);
    }
}