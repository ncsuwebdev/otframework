<?php
/**
 * @package    Messenger
 * @category   Library
 * @copyright
 *
 * @version    SVN: $Id: $
 */

/**
 * Adds ability to do contextual flash messenger objects
 *
 * @package    Messenger
 * @category   Library
 */
class Ot_Action_Helper_Messenger extends Zend_Controller_Action_Helper_FlashMessenger
{
    const MSG_SUCCESS = 'success';

    const MSG_ERROR = 'error';

    const MSG_WARNING = 'warning';

    const MSG_INFO = 'info';

    /**
     * addMessage() - Add a message to flash message
     *
     * @param  string $message
     * @return Zend_Controller_Action_Helper_FlashMessenger Provides a fluent interface
     */
    public function addMessage($message, $type = self::MSG_SUCCESS)
    {
        $msg = new stdClass();
        $msg->type = $type;
        $msg->message = $message;

        return parent::addMessage($msg);
    }

    public function addSuccess($message)
    {
        return $this->addMessage($message, self::MSG_SUCCESS);
    }

    public function addError($message)
    {
        return $this->addMessage($message, self::MSG_ERROR);
    }

    public function addWarning($message)
    {
        return $this->addMessage($message, self::MSG_WARNING);
    }

    public function addInfo($message)
    {
        return $this->addMessage($message, self::MSG_INFO);
    }
}