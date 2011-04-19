<?php
class Hi_Cache_Memcached implements Hi_Cache_Interface
{
    protected $_mc = null;
    protected $_lifetime = 0;
    /**
     * 为了避免不同应用的缓存冲突
     * 
     * @var string
     */
    protected $_appId = '';
    
    /**
     * 构造函数
     * 
     * @param unknown_type $options
     */
    public function __construct ($options = array())
    {
        if (! isset($options['hosts']) || empty($options['hosts'])) {
            $options['hosts'] = '127.0.0.1:11211';
        }
        if (! class_exists('memcache') && ! class_exists('memcached')) {
            throw new Hi_Cache_Exception(
            'Can not found the memcached extension!');
        }
        if (class_exists('memcache')) {
            $this->_mc = new Memcache();
        } else {
            $this->_mc = new Memcached();
        }
        $this->setOptions($options);
    }
    /**
     * 设置缓存选项
     * 选项：
     * lifetime：缓存生存期
     * hosts：memcached服务器，当有多台mc时，该配置项为一数组
     * 
     * @param array $options
     */
    public function setOptions ($options)
    {
        if (is_string($options['hosts'])) {
            $options['hosts'] = array($options['hosts']);
        }
        foreach ($options['hosts'] as $host) {
            $ha = explode(':', $host);
            if (! isset($ha[1]) || empty($ha[1])) {
                $ha[1] = 11211;
            }
            list ($h, $p) = $ha;
            $this->_mc->addServer($h, $p);
        }
        if (isset($options['lifetime'])) {
            $this->_lifetime = intval($options['lifetime']);
        }
        
        if (isset($options['appid'])) {
            $this->_appId = $options['appid'];
        }
    }
    /**
     * (non-PHPdoc)
     * @see Hi_Cache_Interface::save()
     */
    public function set ($id, $content, $lifetime = null)
    {
        if ($lifetime === null) {
            $lifetime = $this->_lifetime;
        }
        $id = $this->_getRealId($id);
        $this->_mc->set($id, $content, $lifetime);
    }
    /**
     * (non-PHPdoc)
     * @see Hi_Cache_Interface::get()
     */
    public function get ($id, $unserialise = false)
    {
        $id = $this->_getRealId($id);
        return $this->_mc->get($id);
    }

    /**
     * (non-PHPdoc)
     * @see Hi_Cache_Interface::delete()
     */
    public function delete ($id)
    {
        $id = $this->_getRealId($id);
        return $this->_mc->delete($id);
    }
    
    /**
     * (non-PHPdoc)
     * @see Hi_Cache_Interface::exist()
     */
    public function exist($id)
    {
        return (bool) $this->get($id);
    }
    
    protected function _getRealId($id)
    {
        if(empty($this->_appId)){
            return $id;
        }
        
        return $this->_appId . '::' . $id;
    }
    
    /**
     * memcached客户端代理
     * 
     * @param unknown_type $method
     * @param unknown_type $args
     * @throws Hi_Cache_Exception
     */
    public function __call($method, $args)
    {
        if(!method_exists($this->_mc, $method)){
            throw new Hi_Cache_Exception("Hi_Cache不存在方法{$method}");
        }
        
        return call_user_func_array(array($this->_backend, $method), $args);
    }
}