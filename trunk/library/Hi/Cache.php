<?php
/**
 * 缓存前端
 * 
 * @author iamlaobie
 */
class Hi_Cache
{
    protected $_backend;
    
    /**
     * 获取缓存对象的快捷方式，自动从配置文件中读取配置选项
     * 
     * @param string $type
     * @param array $options
     */
    static public function factory ($type = null)
    {
        if(empty($type)){
            $config = Hi_Store::get('config');
            if(! $config instanceof Hi_Config){
                throw new Hi_Cache_Exception('系统配置丢失');
            }
            $type = $config->cachetype;
        }
        
        if ($type == 'memcached') {
            $memConfig = Hi_Store::get('config')->memcached;
            $options = array();
            foreach ($memConfig as $memc) {
                $options['hosts'][] = $memc->host . ':' . $memc->port;
            }
        } else {
            $options['dir'] = Hi_Env::$PATH_CACHE;
        }
        return new self($type, $options);
    }
    
    /**
     * 构造一个缓存对象
     * 
     * @param string $type 缓存的类型，当前memcached和file可选
     * @param array $options
     * @throws Hi_Cache_Exception
     */
    public function __construct ($type = 'file', $options = array())
    {
        $ts = array('file', 'memcached');
        $type = strtolower($type);
        if (! in_array($type, $ts, true)) {
            throw new Hi_Cache_Exception(
            'The cache type is "file" or "memcached"');
        }
        
        if($type == 'memcached'){
            $options['appid'] = Hi_Env::$APPID;
        }
        
        $cacheClass = 'Hi_Cache_' . ucwords($type);
        $options['type'] = $type;
        $key = md5(serialize($options));
        $this->_backend = Hi_Store::get($key);
        if (($this->_backend instanceof $cacheClass)) {
            return;
        }
        $this->_backend = new $cacheClass($options);
        Hi_Store::set($key, $this->_backend);
    }
    /**
     * 保存一个缓存
     * 
     * @param $id
     * @param $content
     * @param $lifetime
     * @return bool
     */
    public function set ($id, $content, $lifetime = null)
    {
        if(!Hi_Env::$CACHEABLE){
            return false;
        }
        return $this->_backend->set($id, $content, $lifetime);
    }
    
    /**
     * 取缓存内容
     * 
     * @param string $id
     * @param bool $unserialize
     */
    public function get($id, $unserialize = false)
    {
        if(!Hi_Env::$CACHEABLE){
            return false;
        }
        return $this->_backend->get($id, $unserialize);
    }

    /**
     * 删除一个缓存
     * @param $id
     * @return unknown_type
     */
    public function delete ($id)
    {
        if(!Hi_Env::$CACHEABLE){
            return false;
        }
        return $this->_backend->delete($id);
    }
    
	/**
     * 判断缓存是否存在
     * @param $id
     * @return unknown_type
     */
    public function exist ($id)
    {
        if(!Hi_Env::$CACHEABLE){
            return false;
        }
        return $this->_backend->exist($id);
    }
    
    /**
     * 缓存后台方法的代理
     * 
     * @param unknown_type $method
     * @param unknown_type $args
     * @throws Hi_Cache_Exception
     */
    public function __call($method, $args)
    {
        if(!method_exists($this->_backend, $method)){
            throw new Hi_Cache_Exception("Hi_Cache不存在方法{$method}");
        }
        
        return call_user_func_array(array($this->_backend, $method), $args);
    }
}