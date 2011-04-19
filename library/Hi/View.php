<?php
class Hi_View extends Hi_View_Smarty
{
    protected $_layout = null;
    
    public function layout ($layout)
    {
        $this->_layout = $layout;
        return $this;
    }
    /**
     * 取模板渲染后的html
     * 
     * @param $template
     * @return string
     */
    public function fetch ($template, $compress = false)
    {
        if (! empty($this->_layout)) {
            $ts = explode(',', $template);
            foreach ($ts as $key => $val) {
                $val = trim($val);
                $this->assign('tpl' . $key, $val);
            }
            $html = parent::fetch($this->_layout);
        } else {
            $html = parent::fetch($template);
        }
        if ($compress) {
            $html = preg_replace(array("/[\r\n]/", "/\s{2,}/"), array('', ' '), 
            $html);
        }
        return $html;
    }
    public function display ($template, $compress = false)
    {
        echo $this->fetch($template, $compress);
    }
    /**
     * 静态化一个html页面
     * 
     * @param $template 模板
     * @param $filename 现对于documnet_root的缓存路径
     * @return string 静态化文件的绝对路径
     */
    public function html ($template, $filename, $compress = false, 
    $autoRedirect = true)
    {
        $html = $this->fetch($template, $compress);
        $opt = array('dir' => DOC_ROOT, 'ext' => '', 'filenameEncode' => false, 
        'dirLevel' => 0);
        $cache = new Hi_Cache('file', $opt);
        $file = $this->_cache->set($filename, $html);
        if ($autoRedirect) {
            if (substr($filename, 0, 1) != '/') {
                $filename = '/' . $filename;
            }
            header('location:' . $filename);
        }
    }
    
    /**
     * 注册一个modifier
     * 
     * @param string $modifier
     * @param array $callback
     */
    public function createModifier($modifier, $callback)
    {
        if(!preg_match('/^[_a-z]([_a-z0-9])*$/i', $modifier)){
            throw new Hi_View_Exception('modifier的名称由字母、数字和下划线组成，并且以下划线或者字母开头');
        }
        
        if(!is_array($callback) || sizeof($callback) != 2){
            throw new Hi_View_Exception('无效的callback');
        }
        
        $key = 'Hi_View_Modifier_' . $modifier;
        Hi_Store::set($key, $callback);
        
        $func = 'smarty_modifier_' . $modifier;
        $meta = '
        	function '. $func . '(){
        		$modifier = Hi_Store::get("'.$key.'");
				$args = func_get_args();
				return call_user_func_array($modifier, $args);
        	}
        ';
        eval($meta); 
        
        $this->register_modifier($modifier, $func);
    }
}