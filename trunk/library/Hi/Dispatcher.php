<?php
/**
 * 将请求分发到目标代码
 *
 * @author 刘军<tiandiou@163.com>
 * @since 2008-08-27
 */
class Hi_Dispatcher
{
    private $_class = null;
    private $_action = null;
    public function __construct ()
    {}
    /**
     * 调度
     * 
     * @param $uri
     * @return unknown_type
     */
    public function dispatch ($uri = null)
    {
        try {
            $this->_setAction($uri);
            if (empty($this->_class)) {
                throw new Hi_Exception("Error request, Can not deal with it!");
            }

            $actionObj = new $this->_class($this->_action);
            if (! $actionObj instanceof Hi_Action) {
                throw new Hi_Exception(
                'The Action class must be the instance of Hi_Action');
            }
            $action = $this->_action;
            $actionObj->prepare($this->_action);
            $actionObj->$action();
            $actionObj->complete($this->_action);
        } catch (Hi_Exception $e) {
            throw $e;
        }
    }
    
    /**
     * 根据URI获取逻辑处理类以及方法
     *
     * 最理想的调用格式为 do=控制器命名空间(参数1)/控制器(参数2)/控制器方法(参数3)
     * 如果do == null，控制器命名空间 = '',控制器 = index, 控制器方法 = index
     * 如果do只有一个参数，控制器命名空间 = '',控制器 = index, 控制器方法 = 参数1
     * 如果do有两个参数，如果 参数1 对应的 控制器 存在，参数2 就是控制器方法；
     * 如果 参数1 对应的 控制器 不存在，参数1为控制器空间，参数2 控制器，控制器方法为 index
     *
     * @return unknown
     */
    private function _setAction ($uri = null)
    {
        //扫描自定义的路由器
        $routedUri = Hi_Router::route($uri);
        if (! empty($routedUri)) {
            $uri = $routedUri;
        }
        
        if (empty($uri)) {
            $uri = $this->_getUriFromRequest();
        }

        if (empty($uri)) {
            $this->_class = 'Action_Index';
            $this->_action = 'Index';
            return true;
        }
        
        //去掉开头和结尾的斜线
        $uri = preg_replace('/\/$/', '', $uri);
        $uri = preg_replace('/^\//', '', $uri);
        
        if (! preg_match('/^[a-z]+(\/[a-z][a-z0-9]+\/?)*$/i', $uri)) {
            header('location:/');
            exit();
        }

        $doArray = explode('/', $uri);
        $act = 'index';
        while ($doArray) {
            $ss = implode(' ', $doArray);
            $ss = ucwords($ss);
            $class = str_replace(' ', '_', $ss);
            $class = 'Action_' . $class;
            if (class_exists($class)) {
                $this->_class = $class;
                break;
            }
            if (class_exists($class . '_Index')) {
                $this->_class = $class . '_Index';
                break;
            }
            $act = array_pop($doArray);
        }
        $this->_action = $act;
        return true;
    }
    /**
     * 从uri中提取访问路径
     * 
     * @return string
     */
    private function _getDoFromUrl ()
    {
        $uri = substr($_SERVER['REQUEST_URI'], 1);
        if (empty($uri) || $uri == 'index.php') {
            return;
        }
        $uri = parse_url($uri);
        return $uri['path'];
    }
    /**
     * 根据不同的运行环境取uri
     * 
     * @return unknown_type
     */
    private function _getUriFromRequest ()
    {
        $do = null;
        if (php_sapi_name() == 'cli') {
            if (! isset($_SERVER['argv'][1])) {
                exit('Error Request');
            }
            $do = $_SERVER['argv'][1];
        } elseif (isset($_GET['do'])) {
            $do = $_GET['do'];
        } else {
            $do = $this->_getDoFromUrl();
        }
        return $do;
    }
}
