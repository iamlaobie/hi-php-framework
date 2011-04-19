<?php
class Action_Db_Adapter extends Hi_Action
{
    public function fetchAll()
    {
        $db = Hi_Db::get();
        $rows = $db->fetchAll('select * from user');
        var_export($rows);
    }
    
    public function insert()
    {
        $db = Hi_Db::get();
        $data = array(
            'title' => 'test',
            'uri' => 'test/test'
        );
        echo $db->insert('user_resource', $data);
    }
    
    public function delete()
    {
        $db = Hi_Db::get();
        echo $db->delete('user_resource', 'resourceID = 1');
    }
    
    public function update()
    {
        $db = Hi_Db::get();
        $data = array(
            'title' => 'test1',
            'uri' => 'test1/test1'
        );
        echo $db->update('user_resource', $data, 'resourceID = 2');
    }
    
    public function getConnection()
    {
        $db = Hi_Db::get();
        $pdo = $db->getConnection();
        var_dump($pdo);
    }
    
}