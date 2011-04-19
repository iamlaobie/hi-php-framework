<?php
class Action_Db_Table extends Hi_Action
{
    public function row()
    {
        $table = new Hi_Db_Table('user_resource');
        $row = $table->row(2);
        print_r($row);
    }
    
    public function rowObject()
    {
        $table = new Hi_Db_Table('user_resource');
        $row = $table->rowObject(2);
        print_r($row);
    }
    
    public function rows()
    {
        $table = new Hi_Db_Table('user_resource');
        
        //取道表的过滤工具对象，并过滤出resourceID > 1的记录
        $cond = $table->getCondition();
        $cond->add('resourceID', 1, '>');
        $row = $table->rows();
        print_r($row);
    }
    
    public function pager()
    {
        Hi_Tool_Pager::$limit = 10;
        $table = new Hi_Db_Table('user_resource');
        $pager = $table->pager();
        print_r($pager->data);
        echo $pager->build();
    }
    
}