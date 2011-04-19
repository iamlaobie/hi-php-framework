<?php
/**
 * 控制器抽象类
 * 
 * @author tiandiou
 *
 */
abstract class Hi_Action
{
    protected $_view = null;
    protected $_config;
    /**
     * 本次请求需要调用的方法
     * 
     * @var string
     */
    protected $_action;
    protected $_models = array();
    /**
     * 预处理方法，在真正请求的方法被执行前，该方法将被调用
     * 子类可重写该方法对请求进行预处理
     * 
     * @return unknown_type
     */
    public function prepare ()
    {}
    /**
     * 在真正请求的方法被执行之后，该方法被调用
     * 
     * @return unknown_type
     */
    public function complete ()
    {}
    public function __construct ($action)
    {
        $this->_config = Hi_Store::get('config');
        $this->_action = $action;
    }
    /**
     * 如果需要覆盖此函数，请不要忘记在子类中parent::__get($key)
     *
     * @param string $key
     * @return mix
     */
    public final function __get ($key)
    {
        if (preg_match('/^__([0-9a-z_]+)/i', $key, $regs)) {
            $key = $regs[1];
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $raw = $_POST;
            } else {
                $raw = $_GET;
            }
            if (isset($raw[$key])) {
                return $raw[$key];
            }
            return null;
        }
        throw new Hi_Exception("参数{$key}不存在");
    }
    public function __isset ($key)
    {
        if ($this->$key !== false && $this->$key !== null) {
            return true;
        }
        return false;
    }
    protected function _setView ()
    {
        if ($this->_view instanceof Hi_View) {
            return $this->_view;
        }
        $sc = $this->_config->smarty;
        if (empty($sc->template)) {
            $tplDir = Hi_Env::$PATH_APP . DS . 'templates';
        } else {
            $tplDir = $sc->template;
        }
        if (! is_dir($tplDir)) {
            throw new Hi_Exception('没有找到模板位置');
        }
        $this->_view = new Hi_View();
        $this->_view->template_dir = $tplDir;
        if (empty($sc->template_c) || ! is_dir($sc->template_c)) {
            $this->_view->compile_dir = Hi_Env::$PATH_TMP . 'tempalte_c';
            Hi_Tool_Dir::create($this->_view->compile_dir);
        } else {
            $this->_view->template_c = $sc->template_c;
        }
        $this->_view->caching = (int) $sc->cache;
        $this->_view->debugging = ($sc->debug == 'true' ? true : false);
        return $this->_view;
    }
    public function __call ($function, $param)
    {
        throw new Hi_Exception('错误的请求，请检查参数');
    }
    protected function _redirect ($url = null)
    {
        if ($url === null) {
            $url = $_SERVER['HTTP_REFERER'];
        }
        if (empty($url)) {
            $url = '/';
        }
        header('location: ' . $url);
        exit();
    }
    protected function _jsRedirect ($url)
    {
        echo "<script>window.location.href='{$url}';</script>";
        exit();
    }
}
