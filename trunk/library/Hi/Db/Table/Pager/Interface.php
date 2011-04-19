<?php
/**
 * 数据表的分页接口
 * 
 * @author iamlaobie
 * @since 2010-12-11
 * @package Hi_Db
 *
 */
interface Hi_Db_Table_Pager_Interface
{
    /**
     * 设置总条数
     * 
     * @return unknown_type
     */
    public function setTotal ($total);
    /**
     * 取要从数据库中取出的条数
     * 
     * @return int
     */
    public function getLimit ();
    /**
     * 偏移量
     * 
     * @return int
     */
    public function getOffset ();
    /**
     * 设置当前页的数据
     * 
     * @param $rows array
     * @return unknown_type
     */
    public function setRows (array $rows);
}