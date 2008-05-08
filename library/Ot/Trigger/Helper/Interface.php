<?php
interface Ot_Trigger_Helper_Interface {
	
	public function addSubForm();
	
	public function addProcess($data);
	
	public function editSubForm($triggerActionId);
	
	public function editProcess($data);
	
	public function deleteProcess($triggerActionId);
	
	public function get($triggerActionId);
	
	public function dispatch($data);
	
}