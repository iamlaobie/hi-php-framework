<?php
class Hi_Db_Table
{
    protected $_db;
    protected $_name;
    protected $_primaryKey = '';
    protected $_isAutoIncrement = false;
    protected $_uniqueKeys = array();
    /**
     * 原始的desc table
     * 
     * @var unknown_type
     */
    protected $_info = array();

    protected $_condition = null;
    public function __construct ($name, $db = 'default')
    {
        $this->_name = $name;
        $this->_db = Hi_Db::get($db);
        $this->info();
        $this->_condition = new Hi_Db_Table_Condition($this);
    }
    /**
     * 过滤条件表达式工具代理
     * 
     * @return Hi_Db_Table_Condition
     */
    public function getCondition ()
    {
        return $this->_condition;
    }
    /**
     * 设置表的条件
     * @param Hi_Db_Table_Condition $condition
     * @return unknown_type
     */
    public function setCondition (Hi_Db_Table_Condition $condition)
    {
        $this->_condition = $condition;
        return $this;
    }
    /**
     * 通过filter的条件取一行数据，如果$id不为空，取对应的数据
     * 
     * @param $id
     * @return array
     */
    public function row ($id = null)
    {
        if ($id !== null) {
            $this->_condition->reset();
            $this->_condition->add($this->_primaryKey, $id);
        }
        $sql = $this->sql('*');
        return $this->_db->fetchRow($sql);
    }
    /**
     * 取该表对应的行对象
     * 
     * @param $id
     * @return Hi_Db_Table_Row
     */
    public function rowObject ($id = null)
    {
        return new Hi_Db_Table_Row($this, $id, $this->_db);
    }
    /**
     * 取数据集
     * 
     * @param $limit
     * @param $offset
     * @param $sort
     * @return array
     */
    public function rows ($limit = null, $offset = 0, $sort = null)
    {
        $sql = $this->sql('*', $sort);
        return $this->_db->fetchAll($sql, $limit, $offset);
    }
    /**
     * 取带分页的数据集
     * 
     * @param $pagerClass 用来分页的类，必须实现接口Ezphp_Db_Table_Pager_Interface
     * @param $sort
     * @return object
     */
    public function pager ($sort = null, $pagerClass = 'Hi_Tool_Pager')
    {
        $pager = new $pagerClass();
        $pager->setTotal($this->count());
        $rows = $this->rows($pager->getLimit(), $pager->getOffset(), $sort);
        $pager->setRows($rows);
        return $pager;
    }
    /**
     * 组装sql语句
     * 
     * @param $fields
     * @param $sort
     * @return unknown_type
     */
    public function sql ($fields = '*', $sort = null)
    {
        $sql = "SELECT {$fields} FROM {$this->_name} ";
        if (! $this->_condition->isEmpty()) {
            $sql .= ' WHERE ' . $this->_condition;
        }
        if (! empty($sort)) {
            if (is_array($sort)) {
                $sort = implode(',', $sort);
            }
            $sql .= " ORDER BY {$sort}";
        }
        return $sql;
    }
    /**
     * 取filter对应的数据条数
     * 
     * @param $distinct 是否是DISTINCT模式
     * @return number
     */
    public function count ($field = '*', $distinct = false)
    {
        $dis = '';
        if ($field != '*' && $distinct) {
            $dis = 'DISTINCT';
        }
        $sql = "SELECT  COUNT({$dis} {$field}) FROM {$this->_name}";
        if (! $this->_condition->isEmpty()) {
            $sql .= " WHERE " . $this->_condition;
        }
        return $this->_db->fetchOne($sql);
    }
    /**
     * 更新记录
     * @param $data
     * @param $where
     * @return unknown_type
     */
    public function update ($data, $where = null)
    {
        $ft = $this->_condition->add($where)->__toString();
        return $this->_db->update($this->_name, $data, $ft);
    }
    /**
     * 插入数据
     * 
     * @param $data
     * @return unknown_type
     */
    public function insert ($data)
    {
        if ($data instanceof Hi_Db_Table_Row) {
            $data = $data->get();
        }
        return $this->_db->insert($this->_name, $data);
    }
    /**
     * 删除数据
     * @param $where
     * @return unknown_type
     */
    public function delete ($where = null)
    {
        $this->_condition->add($where);
        return $this->_db->delete($this->_name, $this->_condition);
    }
    /**
     * 获取表的信息，相当于执行 desc table
     * 
     * @param $caseSensitive<bool> 字段名是否大小写敏感
     * @return array
     */
    public function info ()
    {
        if (! empty($this->_info)) {
            return $this->_info;
        }
        
        $cacheKey = "db_table_info_{$this->_name}";
        $cache = Hi_Cache::factory();
        $info = $cache->get($cacheKey, true);
        if($info){
            $this->_info = $info;
            return $info;
        }  
        
        $sql = "DESC {$this->_name}";
        try {
            $this->_info = $this->_db->fetchAssoc($sql, 'Field');
            $pks = array();
            foreach ($this->_info as $key => $val) {
                if ($val['Key'] == 'PRI') {
                    $pks[] = $key;
                    if ($val['Extra'] == 'auto_increment') {
                        $this->_isAutoIncrement = true;
                    }
                }
                if ($val['Key'] == 'UNI') {
                    $this->_uniqueKeys[] = $key;
                }
            }
            if (sizeof($pks) > 1) {
                throw new Hi_Db_Table_Exception('该类暂时还不支持联合主键的表');
            }
            $this->_primaryKey = $pks[0];
            if(isset($cache)){
                $cache->set($cacheKey, $this->_info);
            }
            return $this->_info;
        } catch (Hi_Db_Exception $e) {
            throw new Hi_Db_Table_Exception($e->getMessage(), $e->getCode());
        }
    }
    /**
     * 取表的主键字段名
     * @return string
     */
    public function getPk ()
    {
        return $this->_primaryKey;
    }
    /**
     * 取表的主键字段名
     * @return string
     */
    public function getPrimaryKey ()
    {
        return $this->_primaryKey;
    }
    /**
     * 取表的但字段的唯一索引字段名
     * @return string
     */
    public function getUks ()
    {
        return $this->_uniqueKeys;
    }
    /**
     * 取表的但字段的唯一索引字段名
     * @return string
     */
    public function getUniqueKeys ()
    {
        return $this->_uniqueKeys;
    }
    /**
     * 取表名
     * 
     * @return string
     */
    public function getName ()
    {
        return $this->_name;
    }
    /**
     * 取数据库连接对象
     * 
     * @return object
     */
    public function getDb ()
    {
        return $this->_db;
    }
    /**
     * 判断表是否有自增主键
     * @return bool
     */
    public function isAutoIncrenment ()
    {
        return $this->_isAutoIncrement;
    }
    /**
     * 取某个字段信息
     * @param $name
     * @param $caseSensitive
     * @return array
     */
    public function getField ($name, $caseSensitive = true)
    {
        return $caseSensitive ? $this->_info[$name] : $this->_caseInsensitiveInfo[$name];
    }
}