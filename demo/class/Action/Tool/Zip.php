<?php
class Action_Tool_Zip extends Hi_Action
{
    public function create()
    {
        $r = Hi_Tool_Zip::create('d:/tmp.zip', 'd:/tmp/cache');
        var_dump($r);
    }
    
    public function extract()
    {
        Hi_Tool_Zip::extract('d:/tmp.zip', 'd:/tmpx');
    }
}