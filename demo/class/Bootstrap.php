<?php
class Bootstrap extends Hi_Bootstrap
{
    public static function boot()
    {
        /**
         * 注册一个路由器，对当前请求的uri进行重写
         */
        Hi_Router::add('/thisisatest\/(.+?)\/([^\/]+)\/?/', '/$1/$2', $params = array('x' => '$2', 'y' => '$3'));
    }
}