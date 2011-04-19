<?php
/**
 * db adapter 的适配抽象类
 * 
 * @author tiandiou
 * @since 2010-09-01
 *
 */
abstract class Hi_Db_Adapter_Abstract
{
    /**
     * 连接的pdo对象的集合
     * 
     * @var object | null
     */
    protected $_pdos;
    /**
     * 当前正在使用的pdo连接对象
     * 
     * @var unknown_type
     */
    protected $_pdo;
    /**
     * 数据连接参数，一般是配置文件中db的一个子节点
     * array(
     * 'host' => $host,
     * ...
     * 'rw' => $rw
     * )
     * 
     * @var array
     */
    protected $_params;
}