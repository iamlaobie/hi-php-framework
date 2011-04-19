<?php
class Hi_Config
{
    protected $_config;
    public function __construct ($file)
    {
        $this->_config = simplexml_load_file($file);
    }
    public function __isset ($key)
    {
        return isset($this->_config->$key);
    }
    public function __get ($key)
    {
        if (isset($this->_config->$key)) {
            return $this->_parse($this->_config->$key);
        }
        return null;
    }
    private function _parse ($node)
    {
        $children = $node->children();
        if (count($children) == 0) {
            return (string) $node;
        }
        return $node;
    }
}
