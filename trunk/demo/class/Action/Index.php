<?php 
class Action_Index extends Hi_Action
{
    public function index()
    {
        echo 'Hello, world!';
        $cache = Hi_Cache::factory('file');
        echo $cache->get('xxxx');
    }
}