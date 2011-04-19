<?php
class Action_Cache extends Hi_Action
{
    private $_cache;
    
    public function prepare()
    {
        if($this->__type == 'mc'){
            $this->_setMcCache(); 
        }else{
            $this->_setFileCache();
        }
    }
    
    public function get()
    {
        echo $this->_cache->get('test');
    }
    
    public function set()
    {
        $this->_cache->set('test', 'test');
    }
    
    public function delete()
    {
        $this->_cache->delete('test');
    }
    
    private function _setFileCache()
    {
        $this->_cache = Hi_Cache::factory('file');
    }
    
    private function _setMcCache()
    {
        $this->_cache = Hi_Cache::factory('memcached');
    }
}