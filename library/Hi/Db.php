<?php
class Hi_Db
{
    /**
     * 取得一个数据数据库连接对象
     *
     * @param mix $params
     * @return object
     */
    public static function get ($db = 'default', $new = false)
    {
        if (empty($db)) {
            $db = 'default';
        }
        $configs = Hi_Store::get('config')->db->{$db};
        if (empty($configs)) {
            throw new Hi_Db_Exception('没有找到对应的数据库配置');
        }
        foreach ($configs as $cs) {
            $params[] = get_object_vars($cs);
        }
        //为每个数据库链接分配key
        $key = 'db-' . md5(serialize($params));
        //如果不强制新建连接，从连接缓存中选取
        if (! $new) {
            $db = Hi_Store::get($key);
            if ($db instanceof Hi_Db_Adapter_Abstract) {
                return $db;
            }
        }
        try {
            if (! is_array($params)) {
                throw new Hi_Db_Exception('数据库配置错误');
            }
            $adapter = ucwords($params[0]['type']);
            $adapterClass = "Hi_Db_Adapter_{$adapter}";
            $adapter = new $adapterClass($params);
            Hi_Store::set($key, $adapter);
            return $adapter;
        } catch (Hi_Db_Exception $e) {
            throw $e;
        }
    }
}