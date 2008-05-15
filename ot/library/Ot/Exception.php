<?php
class Ot_Exception extends Exception 
{
	protected $_title = '';
	
	public function getTitle()
	{
		return $this->_title;
	}
}
?>