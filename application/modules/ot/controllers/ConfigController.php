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
 * @package    Ot_ConfigController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of
 *             Information Technology
 * @license    BSD License
 * @version    SVN: $Id: $
 */

/**
 * Allows the user to manage all application-wide configuration variables.
 *
 * @package    Ot_ConfigController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of
 *             Information Technology
 */
class Ot_ConfigController extends Zend_Controller_Action
{
    /**
     * Shows all configurable options
     */
    public function indexAction()
    {
        $this->view->acl = array(
            'edit' => $this->_helper->hasAccess('edit')
        );

        $register = new Ot_Config_Register();

        $this->view->headScript()->appendFile($this->view->baseUrl() . '/public/scripts/ot/modernizr.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/public/scripts/ot/jquery.iphone.password.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/public/scripts/ot/caret.js');

        $vars = $register->getVars();

        $varsByModule = array();

        foreach ($vars as $v) {
            if (!isset($varsByModule[$v['namespace']])) {
                $varsByModule[$v['namespace']] = array();
            }

            $varsByModule[$v['namespace']][] = $v['object'];

        }

        $form = new Ot_Form_Config();
        $form->getElement('section')->setValue($this->_getParam('selected'));

        if ($this->_request->isPost()) {
            if ($form->isValid($_POST)) {

                foreach ($varsByModule as $key => $value) {
                    foreach ($value as $v) {
                        $val = (!is_null($form->getElement($v->getName()))) ? $form->getElement($v->getName())->getValue() : $v->getDefaultValue();

                        $v->setValue($val);

                        $register->save($v);
                    }
                }

                $this->_helper->messenger->addSuccess($this->view->translate('msg-info-configUpdated', ''));

                $this->_helper->redirector->gotoRoute(array('controller' => 'config', 'selected' => $form->getElement('section')->getValue()), 'ot', true);
            }
        }

        $this->view->assign(array(
            'form'     => $form,
        ));

        $this->_helper->pageTitle('ot-config-index:title');
    }

    public function importAction()
    {
        $form = new Ot_Form_ImportConfigCsv();

        if ($this->_request->isPost()) {
            if ($form->isValid($_POST)) {
                if (!$form->config->receive()) {
                    throw new Exception("Error receiving the file");
                }

                $location = $form->config->getFileName();

                $options = array();

                if (($handle = fopen($location, "r")) !== FALSE) {
                    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        $options[] = $data;
                    }

                    fclose($handle);
                }

                unlink($location);

                $vr = new Ot_Config_Register();
                $vars = $vr->getVars();

                foreach ($options as $o) {
                    list($key, $value) = $o;

                    if (isset($vars[$key])) {

                        $vars[$key]['object']->setRawValue($value);

                        $vr->save($vars[$key]['object']);
                    }
                }


                $this->_helper->messenger->addSuccess($this->view->translate('msg-info-configUpdated', ''));

                $this->_helper->redirector->gotoRoute(array('controller' => 'config'), 'ot', true);

            }
        }

        $this->_helper->pageTitle('Import CSV Config File');

        $this->view->assign(array(
            'form'     => $form,
        ));
    }

    public function exportAction()
    {
        $this->_helper->viewRenderer->setNeverRender();
        $this->_helper->layout->disableLayout();

        header('Content-type: text/csv');
        header('Content-disposition: attachment;filename=configExport-' . date('c') . '.csv');

        $vr = new Ot_Config_Register();

        $options = $vr->getVars();

        $data = array();

        foreach ($options as $key => $o) {

            $value = $o['object']->getRawValue();

            $data[] = array($key, $value);
        }

        $tmpfname = tempnam("/tmp", "FOO");

        $handle = fopen($tmpfname, "w");

        foreach ($data as $d) {
            fputcsv($handle, $d);
        }

        echo file_get_contents($tmpfname);

        fclose($handle);

        unlink($tmpfname);

    }

}