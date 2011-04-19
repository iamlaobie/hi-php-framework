<?php
abstract class Hi_Db_Table_Action extends Hi_Action
{
    /**
     * 数据表名，如果为空，取class名作为表名
     * 
     * @var string
     */
    protected $_name;
    /**
     * 数据表对象
     * 
     * @var object
     */
    protected $_table;
    /**
     * 呈现与数据库字段对应
     * 
     * @var array
     */
    protected $_titles = array();
    /**
     * 存放自动生成的模板的路径（相对于smarty的template_dir的相对路径）
     * 
     * @var string
     */
    protected $_subTplDir = 'HiDbTable';
    /**
     * 模板的layout
     * 
     * @var string
     */
    protected $_layout = 'layout/hi_table.html';
    /**
     * 映射关系
     * 
     * @var unknown_type
     */
    protected $_mappers = array();
    /**
     * 自动输出给浏览器
     * 
     * @var bool
     */
    protected $_autoResponse = true;

    /**
     * 字段名是否大小写敏感
     * 
     * @var bool
     */
    protected $_caseSensitive = true;
    
    public function __construct ($action)
    {
        parent::__construct( $action);
        if (empty($this->_name)) {
            $this->_name = substr(get_class($this), 7);
        }
        $this->_table = new Hi_Db_Table($this->_name);
        $this->_table->caseSensitive = $this->_caseSensitive;
    }
    /**
     * 子类可以覆盖该方法对表单数据进行预处理
     * 
     * @param $row
     * @return array
     */
    protected function _readyFormData ($row)
    {
        return $row;
    }
    /**
     * 子类可以覆盖该方法对列表数据进行预处理
     * 
     * @param $rows
     * @return array
     */
    protected function _readyListData ($rows)
    {
        return $rows;
    }
    /**
     * 准备写入数据
     * 
     * @param $row
     * @return 
     */
    protected function _readySaveData ($row)
    {
        return $row;
    }
    /**
     * 取列表配置，子类通过重写该方法配置列表，默认配置为数据库表结构
     * 
     * @return array
     */
    protected function _getListColumns ()
    {
        $info = $this->_table->info();
        return array_keys($info);
    }
    /**
     * 取表单配置，子类通过重写该方法配置列表，默认配置为数据库表结构
     * 
     * @return array
     */
    protected function _getFormConfig ()
    {
        return $this->_table->info($this->_caseSensitive);
    }
    
    protected function _getFilterConfig()
    {
        return array();
    }

    /**
     * 某张表的管理入口
     */
    public function index ()
    {
        $this->_setView();
        $this->_createIndexTpl();
        $this->_applyFilter();
        $pager = $this->_table->pager();
        $data = $this->_readyListData($pager->data);
        foreach ($this->_mappers as $key => $value) {
            $this->_view->assign($key . 'Mapper', $value);
        }
        
        $this->_view->assign('hiTableRows', $data);
        $this->_view->assign('pagerBar', $pager->build());
        $this->_view->layout($this->_layout)
                    ->display($this->_tplRelativeFile('index'));
    }
    
    /**
     * 显示编辑数据的表单
     */
    public function form ()
    {
        $this->_setView();
        $this->_createFormTpl();
        $formConfig = $this->_getFormConfig();
        foreach ($formConfig as $key => $val) {
            if (preg_match('/^(is|has|have)/i', $key) &&
             (! isset($val['type']) || empty($val['type']))) {
                $this->_view->assign("{$key}Src", array('1' => '是', '0' => '否'));
            }
        }
        $pk = $this->_table->getPrimaryKey();
        if (isset($_GET[$pk]) && ! empty($_GET[$pk])) {
            $row = $this->_table->row($_GET[$pk]);
            $row = $this->_readyFormData($row);
            $this->_view->assign('hiTableRow', $row);
        }
        $this->_view->assign('backurl', $_SERVER['HTTP_REFERER']);
        $this->_view->layout($this->_layout)
                    ->display($this->_tplRelativeFile('form'));
    }
    
    /**
     * 保存数据到数据库中
     */
    public function save ()
    {
        $data = $this->_readySaveData($_REQUEST);
        $row = new Hi_Db_Table_Row($this->_table);
        $row->fill($data);
        if ($data['rawPrimaryKey']) {
            $row->setId($data['rawPrimaryKey'], false);
            $row->update();
        } else {
            $row->insert();
        }
        if (empty($this->__backurl)) {
            $class = get_class($this);
            $this->_redirect('/' . str_replace('_', '/', substr($class, 8))) .
             '/index';
        } else {
            $this->_redirect($this->__backurl);
        }
    }
    
    /**
     * 判断将要写入数据库的数据是否重复
     */
    public function isValid ()
    {
        $data = $_REQUEST;
        $pk = $this->_table->getPrimaryKey();
        $id = $field = '';
        if (isset($data[$pk]) && ! empty($data[$pk])) {
            $id = $data[$pk];
        }
        unset($data[$pk]);
        $uks = $this->_table->getUniqueKeys();
        foreach ($data as $key => $val) {
            if (in_array($key, $uks, true)) {
                $field = $key;
                break;
            }
        }
        //校验pk是否存在
        if (! $field && $id) {
            $row = $this->_table->row($id);
            $result = empty($row) ? 'true' : 'false';
        }else{
            $this->_table->getCondition()->add($field, $val);
            $row = $this->_table->row();
    
            //在更新的时候，唯一索引存在，但是属于同一id，这时候也是合法的
            if (empty($row) || (! empty($id) && $row[$pk] == $id)) {
                $result = 'true';
            } else {
                $result = 'false';
            }
        }
        
        //兼容callback模式
        if(isset($data['callback']) && !empty($data['callback'])){
            echo "{$data['callback']}({$result});";
        }else{
            echo $result;
        }
    }
    
    /**
     * 用主键作为标识删除一行记录
     */
    public function delete ()
    {
        $pk = $this->_table->getPrimaryKey();
        $this->_table->getCondition()->add($pk, $_GET[$pk]);
        $this->_table->delete();
        $this->_redirect();
    }
    
    /**
     * 取$action对应的模板文件绝对路径
     * 
     * @param string $action
     */
    protected function _tplFile ($action)
    {
        $this->_setView();
        $relativeFile = $this->_tplRelativeFile($action);
        return $this->_view->template_dir . DS . $relativeFile;
    }
    
    /**
     * 取$action对应的模板文件相对路径
     * 
     * @param string $action
     */
    protected function _tplRelativeFile ($action)
    {
        $this->_setView();
        Hi_Tool_Dir::create(
        $this->_view->template_dir . DS . $this->_subTplDir);
        return $this->_subTplDir . DS . $this->_table->getName() .
         '.' . $action . '.html';
    }
    
    /**
     * 生成某个表操作入口的模板文件
     */
    protected function _createIndexTpl ()
    {
        $tpl = $this->_tplFile('index');
        if (is_file($tpl)) {
            return;
        }
        $tableTpl = new Hi_Db_Table_Tpl($this->_table, $this->_titles);
        $listConfig = $this->_getListColumns();
        $formConfig = $this->_getFilterConfig();
        $table = $tableTpl->index($listConfig, $formConfig);

        $filterForm = $tableTpl->filterForm($formConfig);
        
        file_put_contents($tpl, $table);
    }
    
    /**
     * 生成某个表的表单
     */
    protected function _createFormTpl()
    {
        $tpl = $this->_tplFile('form');
        if (is_file($tpl)) {
            return;
        }
        $formConfig = $this->_getFormConfig();
        $tableTpl = new Hi_Db_Table_Tpl($this->_table, $this->_titles);
        $tplString = $tableTpl->form($formConfig);
        file_put_contents($tpl, $tplString);
    }
    
    /**
     * 将提交的过滤条件应用到table的过滤器上
     */
    protected function _applyFilter ()
    {
        $fc = $this->_getFilterConfig();
        foreach ($_GET as $key => $val) {
            $rawKey = $key;
            if (! isset($fc[$key]) &&
             (substr($key, - 8) == 'MoreThan' || substr($key, - 8) == 'LessThan')) {
                $key = substr($key, 0, - 8);
            }
            if (! isset($fc[$key])) {
                continue;
            }
            $filter = $fc[$key];
            //是否允许空字符串作为条件，默认情况下不允许
            if (! isset($filter['allowEmptyString']) ||
             ! $filter['allowEmptyString']) {
                $field = $this->_table->getField($key);
                if (! preg_match('/(?:char|text|enum)/i', $field['Type']) &&
                 $val === '') {
                    continue;
                }
            }
            //检查值是否在忽略列表中
            if (isset($filter['ignore']) && ! is_array($val)) {
                if (! is_array($filter['ignore'])) {
                    $filter['ignore'] = array($filter['ignore']);
                }
                if (in_array($val, $filter['ignore'])) {
                    continue;
                }
            }
            $cond = $this->_table->getCondition();
            if ($filter['type'] == 'between') {
                $betweenType = substr($rawKey, - 8);
                if ($betweenType == 'MoreThan') {
                    if (isset($filter['leftOpen']) && $filter['leftOpen']) {
                        $operator = '>';
                    } else {
                        $operator = '>=';
                    }
                } else {
                    if (isset($filter['rightOpen']) && $filter['rightOpen']) {
                        $operator = '<';
                    } else {
                        $operator = '<=';
                    }
                }
                $cond->add($key, $val, $operator);
            } else {
                if (empty($filter['operator'])) {
                    $filter['operator'] = '=';
                }
                $cond->add($key, $val, $filter['operator']);
            }
            $this->_view->assign($rawKey, $val);
        }
    }
}