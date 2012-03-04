<?php
class Hi_Tool_Array
{
    public static function mapAssoc($key, $assoc)
    {
        return $assoc[$key];
    }
    
    /**
     * 从二位表中取出某一列的所有值
     * 
     * @param unknown_type $array
     * @param unknown_type $col
     */
    public static function colume($array, $col)
    {
        $result = array();
        for($i = 0, $n = count($array); $i < $n; $i++){
            $result[] = $array[$i][$col];
        }
        return $result;
    }
    
    /**
     * 将$array2中key与array1的key相同的行合并
     * 
     * @param unknown_type $array1
     * @param unknown_type $array2
     */
    public static function mergeByKey($array1, $array2)
    {
        foreach($array1 as $key => & $row){
            if(isset($array2[$key])){
                $row = array_merge($row, $array2[$key]);
            }
        }
        
        return $array1;
    } 
    
    /**
     * 从二维数组中取两列作为key-value关联数组
     * 
     * @param unknown_type $array
     */
    public static function toKeyValue($array, $keyField = 0, $valueField = 1)
    {
        $new = array();
        for($i = 0, $n = count($array); $i < $n; $i++){
            $new[$array[$i][$keyField]] = $array[$i][$valueField];
        }
        return $new;
    }
}