<?php
/**
 * 应用引导器
 * 
 * 框架初始化完成后，如果在当前的环境中检测到类bootstrap，
 * 并且该类继承了Hi_Bootstrap，则调用该类的boot方法
 * 
 * @author Administrator
 *
 */
abstract class Hi_Bootstrap
{
    abstract public static function boot ();
}