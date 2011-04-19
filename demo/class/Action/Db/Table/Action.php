<?php
class Action_Db_Table_Action extends Hi_Db_Table_Action
{
    protected $_name = 'user';
    
    protected $_titles = array(
        'userName' => '用户名',
        'password' => '密码',
        'joinTime' => '注册时间'
    );

    protected function _getFilterConfig()
    {
        return array(
            'userName' => array('type' => 'text', 'operator' => '%like%'),
            'joinTime' => array('type' => 'between', 'leftOpen' => true, 'rightOpen' => true)
        );
    }
}