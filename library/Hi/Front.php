<?php
class Hi_Front
{
    private static $_instance;
    /**
     * 当前请求需要要加载类的文件
     * @var array
     */
    private static $_classes = array();
    private function __construct ()
    {}
    static public function getInstance ()
    {
        if (! self::$_instance instanceof self) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    /**
     * 初始化框架
     *
     * @param unknown_type $appPath
     */
    public function init ($appPath)
    {
        define('DS', DIRECTORY_SEPARATOR);
        define('PS', PATH_SEPARATOR);
        $libs = dirname(dirname(__FILE__));
        
        //注册自动加载并加载框架运行的必须类
        $this->_autoload($libs);
        $dr = $_SERVER['DOCUMENT_ROOT'] . DS;
        $dr = Hi_Tool_Dir::standard($dr);
        
        $appPath = realpath($appPath);
        define('DOC_ROOT', $dr);
        Hi_Env::$DOC_ROOT = $dr;
        $this->_setApp($appPath);
        if (class_exists('Bootstrap') &&
         is_subclass_of('Bootstrap', 'Hi_Bootstrap')) {
            Bootstrap::boot();
        }
    }
    public function dispatch ($uri = null)
    {
        $dispatcher = new Hi_Dispatcher();
        $dispatcher->dispatch($uri);
    }
    /**
     * 初始化应用
     *
     * @param string $path
     */
    private function _setApp ($path)
    {
        $this->_checkPath($path);
        $path .= DS;
        $cf = $path . 'config.xml';
        $config = new Hi_Config($cf);
        Hi_Store::set('config', $config);
        
        $classPath = $path . DS . 'class';
        Hi_Env::$PATH_APP = $path;
        Hi_Env::$PATH_CLASS = $classPath;
        $incPath = $classPath . PS . get_include_path();
        set_include_path($incPath);
        if (isset($config->timezone)) {
            date_default_timezone_set($config->timezone);
        }
        Hi_Env::$ENTRY_FILE = $_SERVER['SCRIPT_FILENAME'];
        Hi_Env::$APPID = $config->appid;
        Hi_Env::$DEBUG = (bool) $config->debug;
        
        Hi_Env::$DEBUG = ($config->debug == 'on' || $config->debug == 'true');
        Hi_Env::$CACHEABLE = ($config->cacheable != 'false' || !empty($config->cacheable));
        
        //设置全局的临时文件路径、缓存路径、日志路径
        $tmpPath = $config->tmp;
        if (empty($tmpPath)) {
            $tmpPath = sys_get_temp_dir();
        }
        $tmpPath = Hi_Tool_Dir::standard($tmpPath);
        if (substr($tmpPath, - 1) != DS) {
            $tmpPath .= DS;
        }
        Hi_Env::$PATH_TMP = $tmpPath;
        $cachePath = $config->cache;
        if (empty($cachePath)) {
            $cachePath = $tmpPath . 'cache' . DS . Hi_Env::$APPID;
        }
        Hi_Tool_Dir::create($cachePath);
        Hi_Env::$PATH_CACHE = $cachePath;
        $logPath = $config->log;
        if (empty($logPath)) {
            $logPath = $tmpPath . 'logs';
        }
        Hi_Env::$PATH_LOG = $logPath;
    }
    /**
     * 从包含路径中自动加载类
     *
     * @param string $className
     * @return bool
     */
    static public function loadClass ($className)
    {
        $file = str_replace('_', DS, $className) . '.php';
        $incPath = get_include_path();
        $incPath = explode(PS, $incPath);
        foreach ($incPath as $ip) {
            $ip = str_replace('/', DS, $ip);
            $ip = str_replace('\\', DS, $ip);
            if (substr($ip, - 1) != DS) {
                $ip .= DS;
            }
            $abFile = $ip . $file;
            if (is_file($abFile)) {
                include_once $abFile;
                if (class_exists($className, false)) {
                    self::$_classes[] = $abFile;
                    break;
                }
            }
        }
        return true;
    }
    /**
     * 注册自动加载方法
     *
     */
    private function _autoload ($libs)
    {
        set_include_path(get_include_path() . PS . $libs);
        $hiLib = $libs . DS . 'Hi' . DS;
        include_once $hiLib . 'Config.php';
        include_once $hiLib . 'Env.php';
        include_once $hiLib . 'Store.php';
        include_once $hiLib . 'Router.php';
        include_once $hiLib . 'Action.php';
        include_once $hiLib . 'Bootstrap.php';
        include_once $hiLib . 'Dispatcher.php';
        include_once $hiLib . 'Tool' . DS . 'Dir.php';
        include_once $hiLib . 'Cache' . DS . 'Interface.php';
        include_once $hiLib . 'Cache.php';
        include_once $hiLib . 'Cache' . DS . 'File.php';
        
        spl_autoload_register(array('Hi_Front', 'loadClass'), true);
        
        //预先加载该uri对应的类
        $cache = Hi_Cache::factory('file');
        $req = parse_url($_SERVER['REQUEST_URI']);
        $key = $req['path'] . '::classes';
        $files = $cache->get($key, true);
        if ($files) {
            for ($i = 0, $n = count($files); $i < $n; $i ++) {
                include_once $files[$i];
            }
        }
    }
    
    /**
     * 检查应用的部署路径是不是合法
     * 
     * @param string $path
     */
    private function _checkPath($path)
    {
        if (! is_dir($path) || !is_readable($path)) {
            throw new Hi_Exception('应用路径必须是一个可读的路径');
        }
        
        $path = Hi_Tool_Dir::standard($path);
        if (substr($path, - 1) != DS) {
            $path .= DS;
        }
        
        if (! is_file($path . 'config.xml')) {
            throw new Hi_Exception('在应用的部署路径下没有找到配置文件 "config.xml"');
        }
        
        if (! is_dir($path . 'class') || !is_readable($path . 'class')) {
            throw new Hi_Exception('没有找到class路径');
        }
        
        $classPath = $path . 'class';
        $ad = $classPath . DS . 'Action';
        if (! is_dir($ad)) {
            throw new Hi_Exception('没有找到Action路径');
        }
    }
    
    /**
     * 析构时将本次请求所加载的类写入到文件缓存，下次预先加载
     */
    public function __destruct ()
    {
        $req = parse_url($_SERVER['REQUEST_URI']);
        $key = $req['path'] . '::classes';
        $cache = Hi_Cache::factory('file');
        if (! $cache->exist($key)) {
            $cache->set($key, self::$_classes, 3600);
        }
    }
}