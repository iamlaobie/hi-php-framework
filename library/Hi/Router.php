<?php
/**
 * 动作路由器，通过正则表达式可以重写uri到指定的位置
 * 
 * @author tiandiou
 *
 */
class Hi_Router
{
    /**
     * 路由器集合
     * 
     * @var array
     */
    protected static $_routers = array();
    /**
     * 不允许实例该类
     * 
     * @return null
     */
    protected function __construct ()
    {}
    
    /**
     * 添加一个路由器
     * 
     * @param string $regex 检查和提取uri片段的正则表达式
     * @param string $target 路由目标, 用$1、$2 ... $n来引用$regex定义的子表达式
     * @param array $params 需要从uri中提取的参数，用$1、$2 ... $n来引用$regex定义的子表达式
     */
    static public function add ($regex, $target, $params = array())
    {
        $reverse = array();
        foreach($params as $param => $reg){
            $reverse[$reg][] = $param;
        }
        
        self::$_routers[] = array($regex, $target, $reverse);
    }
    
    /**
     * 从uri中按照已经定义的路由器提取位置
     * 
     * @param $uri
     * @return array
     */
    static public function route ($uri)
    {
        $uri = substr($_SERVER['REQUEST_URI'], 1);
        foreach (self::$_routers as $router) {
            if (! preg_match($router[0], $uri, $regs)) {
                continue;
            }
            print_r($regs);
            $target = $router[1];
            $params = $router[2];

            
            foreach ($regs as $key => $val) {
                $refKey = '$' . $key;
                $target = str_replace($refKey, $val, $target);
                
                if(empty($params)){
                   continue; 
                }
                
                if($_SERVER['REQUEST_METHOD'] == 'POST'){
                    $reqParams = & $_POST;
                }else{
                    $reqParams = & $_GET;
                }
                
                if(array_key_exists($refKey, $params)){
                    foreach($params[$refKey] as $name){
                        $reqParams[$name] = $val;    
                    }
                    
                }
            }
            return $target;
        }
        return null;
    }
}
