<?php
class Hi_Db_Table_Row
{
    protected $_db;
    /**
     * 所属的表
     * @var Hi_Db_Table
     */
    protected $_table;
    /**
     * 当前表的主键
     * @var unknown_type
     */
    protected $_primaryKey;
    /**
     * 当前被操作的数据
     * @var array
     */
    protected $_row = array();
    /**
     * 当前被操作行的id
     * 
     * @var unknown_type
     */
    protected $_id = null;
    public function __construct ($table, $id = null, $db = 'default')
    {
        if ($db instanceof Hi_Db_Adapter_Abstract) {
            $this->_db = $db;
        } else {
            $this->_db = Hi_Db::get($db);
        }
        if ($table instanceof Hi_Db_Table) {
            $this->_table = $table;
        } else {
            $this->_table = new Hi_Db_Table($table, $db);
        }
        $this->_primaryKey = $this->_table->getPrimaryKey();
        if ($id !== null) {
            $this->setId($id);
        }
    }
    /**
     * 将data填充到$this->_row中
     * 
     * @param $data
     * @return unknown_type
     */
    public function fill ($data)
    {
        $this->reset();
        $fields = $this->_table->info(false);
        foreach ($data as $key => $val) {
            //过滤主键
            if ($key == $this->_table->getPrimaryKey() &&
             ! empty($val)) {
                $this->_id = $val;
            }
            //过滤不存在的字段
            if (array_key_exists(strtolower($key), $fields)) {
                $this->_row[$key] = $val;
            }
        }
    }
    /**
     * 取当前数据
     * @return unknown_type
     */
    public function get ()
    {
        return $this->_row;
    }
    /**
     * 清空当前数据
     * @return unknown_type
     */
    public function reset ()
    {
        $this->_row = array();
    }
    /**
     * 设置当前的数据的主键
     * @param $val
     * @param $fill 是否从数据库中查出数据填充为当前的数据
     * @return unknown_type
     */
    public function setId ($val, $fill = true)
    {
        if ($val === null) {
            return;
        }
        $this->_id = $val;
        if (! $fill) {
            return;
        }
        $row = $this->_table->row($this->_id);
        if (empty($row)) {
            throw new Hi_Db_Table_Exception('主键不存在');
        }
        $this->_row = $row;
    }
    /**
     * 取当前主键
     * @return unknown_type
     */
    public function getId ()
    {
        return $this->_id;
    }
    /**
     * 更新$id对应的记录
     * @param $id
     * @param $data
     * @return int
     */
    public function update ($id = null, $data = array())
    {
        if (is_array($data) && ! empty($data)) {
            $this->fill($data);
        }
        if ($id !== null) {
            $this->_id = $id;
        }
        $pk = $this->_table->getPrimaryKey();
        unset($this->_row[$pk]);
        $cond = $this->_table->getCondition();
        $cond->add($pk, $this->_id);
        return $this->_table->update($this->_row);
    }
    /**
     * 将数据插入表中
     * 
     * @param $data
     * @return unknown_type
     */
    public function insert ($data = array())
    {
        if (! empty($data)) {
            $this->fill($data);
        }
        if ($this->_table->isAutoIncrenment()) {
            unset($this->_row[$this->_table->getPrimaryKey()]);
        }
        return $this->_table->insert($this->_row);
    }
    /**
     * 删除id指向的记录
     * @param $id
     * @return unknown_type
     */
    public function delete ($id = null)
    {
        $this->setId($id);
        if ($this->_id === null) {
            throw new Hi_Db_Table_Exception("需要被删除行的id");
        }
        $cond = $this->_table->getCondition();
        $cond->add($this->_primaryKey, $this->_id);
        $this->_table->delete();
    }
    /**
     * 取字段值的代理
     * @param $key
     * @return mix
     */
    public function __get ($key)
    {
        $info = $this->_table->info();
        if (! isset($info[$key])) {
            throw new Hi_Db_Table_Exception("字段[{$key}]不存在");
        }
        if (isset($this->_row[$key])) {
            return $this->_row[$key];
        }
        return null;
    }
    /**
     * 设置字段值的代理
     * @param $key
     * @param $val
     * @return bool
     */
    public function __set ($key, $val)
    {
        $info = $this->_table->info();
        if (! isset($info[$key])) {
            throw new Hi_Db_Table_Exception("字段[{$key}]不存在");
        }
        $this->_row[$key] = $val;
        return true;
    }
}