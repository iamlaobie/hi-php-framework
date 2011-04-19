<?php
interface Hi_Cache_Interface
{
    /**
     * 
     * 将缓存内容写入缓存介质
     * @param string $id
     * @param mix $content
     */
    public function set ($id, $content);
    /**
     * 从缓存中加载id的缓存内容
     * 
     * @param string $id
     */
    public function get ($id);

    /**
     * 删除缓存
     * 
     * @param strin $id
     */
    public function delete ($id);
    
    /**
     * 判断缓存是否存在
     * 
     * @param strin $id
     */
    public function exist($id);
}