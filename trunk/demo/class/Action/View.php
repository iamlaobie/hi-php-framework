<?php
class Action_View extends Hi_Action
{
    public function prepare()
    {
        $this->_setView();
    }
    
    public function display()
    {
        $this->_view->assign('value', 'Hello,world!');
        $this->_view->display('index.php');
    }
    
    public function layout()
    {
        //设置布局
        $this->_view->layout('layout/default.php');
        $this->_view->assign('value', 'Hello,world!');
        $this->_view->assign('value2', 'Hello,every one!');
        
        //显示两个模板，在模板中用变量$tpl0取index.php，$tpl1取index2.php
        //第二个参数的表示压缩，从生成的html中将多余的空白符去掉
        $this->_view->display('index.php,index2.php',  true);
    }
    
    public function file()
    {
        //设置布局
        $this->_view->setLayout('layout/default.php');
        $this->_view->assign('value', 'Hello,world!');
        $this->_view->assign('value2', 'Hello,every one!');
        
        //显示两个模板，在模板中用变量$tpl0取index.php，$tpl1取index2.php
        //第二个参数的表示压缩，从生成的html中将多余的空白符去掉
        $html = $this->_view->file('index.php,index2.php', 'index.html', true);
        
    }
    
    public function modifier()
    {
        $this->_view->createModifier('xxx', array($this, 'upper'));
        $this->_view->assign('value', 'test modifer');
        $this->_view->display('index.php');
    }
    
    public function upper($s)
    {
        return strtoupper($s);
    }
    
    
}