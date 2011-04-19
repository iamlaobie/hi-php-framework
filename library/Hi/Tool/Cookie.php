<?php
/**
 * COOKIE管理类
 *
 * @author 刘军 <liujun@tomonline-inc.com | 2542>
 * @since 2008-07-23
 */
class Hi_Tool_Cookie
{
    /**
     * 单例模式的实例
     *
     * @var object
     */
    private static $_instance = null;
    /**
     * 原始的cookie数据
     *
     * @var array
     */
    private $_cookieData;
    /**
     * cookie的key的前缀
     *
     * @var string
     */
    private $_prefix = 'hi_';
    /**
     * 用于加密cookie的des密钥
     * 
     * @var unknown_type
     */
    private $_secret = '00000000';
    /**
     * 单例模式，构造函数私有
     *
     */
    private function __construct ($secret, $prefix = 'hi_')
    {
        $this->_cookieData = $_COOKIE;
        $this->_secret = $secret;
        $this->_prefix = $prefix;
    }
    /**
     * 取得cookie的实例
     *
     * @param $secret 加解密的密钥
     * @return object
     */
    public static function getInstance ($secret, $prefix = 'hi_')
    {
        if (null == self::$_instance) {
            self::$_instance = new self($secret, $prefix);
        }
        return self::$_instance;
    }
    /**
     * 通过$key从cookie中取得对应的值
     * 如果$key == null,返回整个cookie数组
     * 
     * @param string $key
     * @return string
     */
    public function get ($key = null)
    {
        if (is_null($key)) {
            return $this->_cookieData;
        }
        $key = $this->_prefix . $key;
        if (isset($this->_cookieData[$key])) {
            return $this->_cookieData[$key];
        }
        return null;
    }
    /**
     * 取一个$key对应的值，并且解密
     *
     * @param string $key
     * @return string
     */
    public function getDecrypt ($key)
    {
        $val = $this->get($key);
        if (! empty($val)) {
            return Hi_Tool_Des::decode($this->_secret, $val);
        }
        return null;
    }
    /**
     * 取$key对应的cookie值
     *
     * @param string $key
     * @return string
     */
    public function __get ($key)
    {
        return $this->get($key);
    }
    /**
     * 写cookie
     *
     * @param string $key
     * @param string $value
     * @param int $lifetime 
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @return bool
     */
    public function set ($key, $value, $lifetime, $path = '/', $domain = '', 
    $secure = false)
    {
        if (empty($domain)) {
            $domain = $_SERVER['HTTP_HOST'];
            $x = explode(':', $domain); //server中有可能含有端口号
            $domain = $x[0];
        }
        $domain = '.' . $domain;
        $expire = time() + $lifetime + 8 * 3600;
        $key = $this->_prefix . $key;
        return setcookie($key, $value, $expire, $path, $domain, $secure);
    }
    /**
     * 设置加密cookie
     *
     * @param string $key
     * @param string $value
     * @param int $lifetime
     * @return bool
     */
    public function setEncrypt ($key, $value, $lifetime = 18000)
    {
        $encryptValue = Hi_Tool_Des::encode($this->_secret, $value);
        return $this->set($key, $encryptValue, $lifetime);
    }
    /**
     * 设置明文的cookie
     *
     * @param string $key
     * @param string $value
     * @param int $expire
     * @return bool
     */
    public function setLiteral ($key, $value, $lifetime = 18000)
    {
        return $this->set($key, urlencode($value), $lifetime);
    }
    /**
     * 清除$key对应的cookie
     *
     * @param string $key
     * @return bool
     */
    public function clear ($key)
    {
        return $this->set($key, '', 0);
    }
}
