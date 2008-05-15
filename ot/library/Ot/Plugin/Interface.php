<?php
interface Ot_Plugin_Interface {
    
    public function addSubForm();
    
    public function addProcess($data);
    
    public function editSubForm($id);
    
    public function editProcess($data);
    
    public function deleteProcess($id);
    
    public function get($id);
    
    public function dispatch($data);
    
}