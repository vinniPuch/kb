<?php

class Data {
    
    private $data = array();
   
    public function __construct() {
        
    }
    
    public function __set($key, $value) {
        
        $this->data[$key] = $value;
       
    }
    
    public function __get($key) {
        
        if( isset($this->data[$key])) {
            return $this->data[$key];
        }
        return null;
    }
    
    protected function setData($data) {
        
        $this->data = $data;
        
    }
    
    protected function getData() {
        
        return $this->data;
        
    }
    
    protected function unsetData() {
        $this->data = array();
    }
    
}
