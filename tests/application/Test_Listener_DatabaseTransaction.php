<?php

abstract class Our_Test_ControllerTestCase extends Zend_Test_PHPUnit_ControllerTestCase
{
    function setUp()
    {
        Zend_Registry::get('database')->rollBack();
        parent::setUp();
        Zend_Registry::get('database')->beginTransaction();
    }
}
  