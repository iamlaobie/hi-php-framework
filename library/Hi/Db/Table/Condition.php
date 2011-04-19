<?php
/**
 * 为数据查询生成where部分的过滤器
 * 
 * @author Administrator
 *
 */
class Hi_Db_Table_Condition
{
    protected $_table;
    protected $_filters = array();
    public function __construct ($table)
    {
        if (is_string($table)) {
            $table = new Hi_Db_Table($table);
        } elseif (! $table instanceof Hi_Db_Table) {
            throw new Hi_Db_Exception('参数$table必须为Ezphp_Db_Table的实例');
        }
        $this->_table = $table;
    }
    public function reset ()
    {
        $this->_filters = array();
    }
    public function delete ($field)
    {
        foreach ($this->_filters as $k => $f) {
            if ($f[0] == $field) {
                unset($this->_filters[$k]);
            }
        }
    }
    public function isEmpty ()
    {
        return empty($this->_filters);
    }
    /**
     * 添加一个过滤条件
     * 
     * @param $field 字段
     * @param $val 字段值
     * @param $operator 运算符 默认为 等于（=）
     * @return bool
     */
    public function add ($field, $val = null, $operator = '=')
    {
        if (empty($field)) {
            return $this;
        }
        $info = $this->_table->info();
        if ($val !== null && ! array_key_exists($field, $info)) {
            throw new Hi_Db_Exception(
            "表" . $this->_table->getName() . "不存在字段{$field}");
        }
        $operator = trim($operator);
        $this->_filters[] = array($field, $val, $operator);
        return $this;
    }
    public function __toString ()
    {
        $s = '1';
        foreach ($this->_filters as $filter) {
            $s .= ' AND ' . $this->build($filter);
        }
        return $s;
    }
    public function build ($filter)
    {
        //条件值为空，表示第0个单元为一个完整的过滤表达式
        if ($filter[1] === null) {
            return '(' . $filter[0] . ')';
        }
        $val = "'{$filter[1]}'";
        if (strtolower($filter[2]) == 'in' || strtolower($filter[2]) == 'not in') {
            if (is_array($filter[1])) {
                $val = join("','", $filter[1]);
                $val = "('{$val}')";
            } else {
                $val = "'{$val}'";
            }
        }
        if (preg_match('/(%?)like(%?)/i', $filter[2], $regs)) {
            $val = $filter[1];
            if (substr($filter[2], 0, 1) == '%') {
                $val = '%' . $val;
            }
            if (substr($filter[2], - 1) == '%') {
                $val = $val . '%';
            }
            $filter[2] = 'like';
            $val = "'{$val}'";
        }
        return "({$filter[0]} {$filter[2]} {$val})";
    }
    /**
     * table的方法的代理
     * 
     * @param $func
     * @param $args
     * @return unknown_type
     */
    public function __call ($func, $args)
    {
        $allow = array('row', 'rows', 'pager', 'sql');
        if (! in_array($func, $allow, true)) {
            throw new Hi_Db_Table_Exception(
            'Hi_Db_Table_Condition不存在方法：' . $func);
        }
        $this->_table->setCondition($this);
        return call_user_func_array(array($this->_table, $func), $args);
    }
}