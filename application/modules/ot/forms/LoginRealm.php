<?php
class Ot_Form_LoginRealm extends Twitter_Bootstrap_Form_Vertical
{
    public function __construct($realm, $autoLogin = false, $allowSignup = false, $options = array())
    {
        parent::__construct($options);

        $this->setAttrib('id', 'login_' . $realm);

        if (!$autoLogin) {

            // Create and configure username element:
            $username = $this->createElement('text', 'username', array('label' => 'ot-login-form:username'));
            $username->setRequired(true)->addFilter('StringTrim');

            // Create and configure password element:
            $password = $this->createElement('password', 'password', array('label' => 'ot-login-index:password'));
            $password->addFilter('StringTrim')->setRequired(true);

            $password->setDescription('<a href="' . $this->getView()->url(array('action' => 'forgot', 'realm' => $realm), 'login', true) . '">' . $this->getView()->translate("ot-login-index:linkForgot") . '</a>');
            $password->getDecorator('Description')->setEscape(false);

            $this->addElements(array($username, $password));
        }

        $buttons = array();

        $this->addElement('submit', 'submit', array(
            'buttonType' => Twitter_Bootstrap_Form_Element_Submit::BUTTON_PRIMARY,
            'label'      => 'ot-login-index:login',
            'class'      => 'pull-left',
            'style'      => 'margin-right: 10px;'
        ));
        $buttons[] = 'submit';

        if ($allowSignup) {

            $this->addElement('button', 'signup_' . $realm, array(
                'label'         => 'ot-login-index:signUp',
                'type'          => 'button',
                'class'         => 'pull-left signup'
            ));

            $buttons[] = 'signup_' . $realm;
        }

        $redirectUriHidden = $this->createElement('hidden', 'redirectUri');
        $redirectUriHidden->setValue($_SERVER['REQUEST_URI']);
        $redirectUriHidden->setDecorators(array(array('ViewHelper', array('helper' => 'formHidden'))));

        $this->addElement($redirectUriHidden);

        $realmHidden = $this->createElement('hidden', 'realm');
        $realmHidden->setValue($realm);
        $realmHidden->setDecorators(array(array('ViewHelper', array('helper' => 'formHidden'))));

        $this->addElement($realmHidden);

        $this->addDisplayGroup(
            $buttons,
            'actions',
            array(
                'disableLoadDefaultDecorators' => false,
                'decorators' => array()
            )
        );

        return $this;

    }
}
