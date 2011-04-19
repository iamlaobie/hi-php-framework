<?php
/**
 * 
 * 提供key,value形式的数据寄存
 * @author iamlaobie
 *
 */
class Hi_Store
{
    private static $_data;
    
    private function __construct ()
    {
    
    }
    
    /**
     * 从数据寄存中取数据，没有取到数据时返回$default
     *
     * @param string $key
     * @param mix $default
     */
    static public function get ($key, $default = null)
    {
        if (isset(self::$_data[$key])) {
            return self::$_data[$key];
        }
        return $default;
    }
    
    /**
     * 
     * 寄存数据
     * @param string $key
     * @param mix $val
     */
    static public function set ($key, $val)
    {
        self::$_data[$key] = $val;
    }
}