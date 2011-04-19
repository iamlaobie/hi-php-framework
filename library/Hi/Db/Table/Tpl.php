<?php
class Hi_Db_Table_Tpl
{
    protected $_table;
    
    protected $_titles;

    public function __construct(Hi_Db_Table $table, $titles = array())
    {
        $this->_table = $table;
        $this->_titles = $titles;
    }
    
    public function index($columns = array(), $filters = array())
	{
	    if(empty($columns)){
	        $info = $this->_table->info();
	        $columns = array_keys($info);
	    }

	    $titles = $this->_titles;
	    $cfg = $data = array();
	    $pk = $this->_table->getPk();
	    
	    $ths = '        <th class=\'hi_table_th\'><input type="checkbox" name="hi_table_checkAll" id="checkAll" /></th>' . "\n";
	    $tds = '        <td class=\'hi_table_td\'><input type="checkbox" name="hi_table_checItem[]" value="{$hiTableRow.'.$pk.'}" id="checkItem_{$hiTableRow.'.$pk.'}"/></td>' . "\n";
        
        foreach ($columns as $col) {
            if (isset($titles[$col]) && ! empty($titles[$col])) {
                $title = $titles[$col];
            } else {
                $title = $col;
            }
            
            $ths .= "        <th class='hi_table_th'>{$title}</th>\n";
            $tds .= "        <td class='hi_table_td'>{\$hiTableRow.{$col}}</td>\n";
        }
        $ths .= "        <th class='hi_table_th'>操作</th>\n";
        
	    $baseUri = $this->_getBaseUri();
	    $backUrl = urlencode($_SERVER['REQUEST_URI']);
        $actions = "[<a class='hi_table_action' href='{$baseUri}form?{$pk}={\$hiTableRow.{$pk}}&backurl={$backUrl}'>修改</a>]&nbsp;&nbsp;";
        $actions .= "[<a onclick='return confirm(\"确认要删除？\")' class='hi_table_action' href='{$baseUri}delete?{$pk}={\$hiTableRow.{$pk}}&backurl={$backUrl}'>删除</a>]&nbsp;&nbsp;";
        
        $tds .= "        <td class='hi_table_td'>{$actions}</td>";
        
        $dbTableName = $this->_table->getName();
        $table = "<table class='hi_table' id='hi_table_{$dbTableName}'>\n" .
                   "    <tr class='hi_table_thead'>\n" .
                   "{$ths}".
                   "    </tr>\n" .
                   '    {foreach from=$hiTableRows item=hiTableRow}' . "\n" . 
                   "    <tr class='hi_table_tr'>\n" .
                   "{$tds}\n" .
                   "    </tr>\n" .
                   "    {/foreach}\n" .
                 "</table>\n\n";
        
        $toolBar = "<div style='width:100%' class='hi_table_toolbar'>\n";
        $toolBar .= "    <div style='float:left' class='hi_table_selector'>\n" . 
                    "         <a id='hi_table_selector_all' href='javascript:;'>全选</a>&nbsp;&nbsp;\n" .
                    "		  <a id='hi_table_selector_reverse' href='javascript:;'>反选</a>&nbsp;&nbsp;\n" . 
                    "         <a id='hi_table_selector_no' href='javascript:;'>不选</a>\n" . 
                    "    </div>\n";
        $toolBar .= "    <div style='float:right' class='hi_table_pagerbar'>{\$pagerBar}</div>\n";
        $toolBar .= "</div>\n";
        
        if(!empty($filters)){
            $filterForm = $this->filterForm($filters);
        }
        
        $adder = "<div style='float:right'>[<a href='{$baseUri}form'>添加</a>]</div>\n";
                   
        return  $filterForm . $adder . $table . $toolBar;
    }
    
    public function filterForm ($config)
    {
        if (! is_array($config)) {
            return '';
        }
        $html = '';
        foreach ($config as $key => $val) {
            $field = $this->_table->getField($key);
            $title = $key;
            if (isset($this->_titles[$key])) {
                $title = $this->_titles[$key];
            }
            
            if(!isset($val['type']) || empty($val['type'])){
                $val['type'] = 'text';
            }
            
            if (strtolower($val['type']) == 'between') {
                if ($field['Type'] == 'date' || $field['Type'] == 'datetime') {
                    $type = 'date';
                    $attr['readOnly'] = 'true';
                } else {
                    $type = 'text';
                }
                $html .= "     {$title}大于：" .
                 $this->_createField('date', $key . 'MoreThan', 
                '$' . $key . 'MoreThan', null) . "<br /><br />\n";
                $html .= "     {$title}小于：" .
                 $this->_createField('date', $key . 'LessThan', 
                '$' . $key . 'LessThan', null) . "<br /><br />\n";
                
                 $html .= "{literal}\n<script>$('#{$key}MoreThan').datepicker({dateFormat:'yy-mm-dd'})\n";
                 $html .= "$('#{$key}LessThan').datepicker({dateFormat:'yy-mm-dd'})\n</script>{/literal}";
            } else {
                $html .= "     {$title}：" .
                 $this->_createField($val['type'], $key, '$' . $key) .
                 "<br /><br />\n";
            }
        }
        $html .= "<input type='submit' value='查找' /><br /><br />\n";
        return "<form>\n{$html}\n</form>";
    }
    
    protected function _createField ($type, $name, $value = null, $id = null, 
    $attr = array())
    {
        if (empty($id)) {
            $id = $name;
        }
        $strAttr = '';
        if (is_array($attr)) {
            foreach ($attr as $key => $val) {
                $strAttr .= " {$key}='{$val}'";
            }
        } else {
            $strAttr = (string) $attr;
        }
        if ($type == 'hidden' || $type == 'text' || $type == 'password') {
            $tpl = '<input type="%s" name="%s" id="%s" value="{%s}" %s />';
            return sprintf($tpl, $type, $name, $id, $value, $strAttr);
        }
        if ($type == 'radio') {
            return '{html_radios name="' . $name .
             '" separator="&nbsp;&nbsp;" options=$' . $name . 'Src selected=' .
             $value . '|addslashes}';
        }
        if ($type == 'textarea') {
            return '<textarea style="height:150px;width:400px;" name="' . $name .
             '" id="' . $id . '" ' . $strAttr . '>{' . $value . '}</textarea>';
        }
        if ($type == 'date') {
            $tpl = '<input type="%s" name="%s" id="%s" value="{%s}" %s />';
            $field = sprintf($tpl, 'text', $name, $id, $value, $strAttr);
            return $field;
        }
        if ($type == 'checkbox') {
            return '{html_checkboxes name="' . $name .
             '" separator="&nbsp;&nbsp;" options=$' . $name . 'Mapper checked=' .
             $value . '|addslashes}';
        }
        if ($type == 'select') {
            return '{html_options name="' . $name . '" options=$' . $name .
             'Mapper selected=' . $value . '}';
        }
        return '';
    }
    
    public function js ($formConfig)
    {
        $tableInfo = $this->_table->info();
        $pk = $this->_table->getPrimaryKey();
        $baseUri = $this->_getBaseUri();
        $tableName = $this->_table->getName();
        $js = "<!--建议调试结束后，将此段js代码用js文件引入-->";
        $js .= "<script>\n\n";
        $js .= '$(document).ready(function(){' . "\n";
        
        //开始拼接validator
        $js .= "$('#hiTableForm_{$tableName}').validate({\n";
        
        $msgs = $rules = array();
        $datepickers = '';
        foreach ($formConfig as $key => $val) {
            //日期类型和text不做校验
            if (strpos($tableInfo[$key]['Type'], 'text') !== false) {
                continue;
            }
            
            if (strpos($tableInfo[$key]['Type'], 'date') !== false) {
                $datepickers .= "$('#{$key}').datepicker({dateFormat:'yy-mm-dd'})\n";
                continue;
            }
            
            $msg = $rule = array();
            if ($tableInfo[$key]['Null'] == 'No') {
                $rule[] = 'required:true';
                $msg[] = "required:'请填写{$key}'";
            }
            if (strpos($tableInfo[$key]['Type'], 'int') !== false) {
                $rule[] = 'number:true';
                $msg[] = "number:'请输入数字'";
            }
            if (preg_match('/char\((\d+)\)/i', $tableInfo[$key]['Type'], $regs)) {
                $rule[] = 'maxlength:' . $regs[1];
                $msg[] = "maxlength:'{$key}最长可输入{$regs[1]}个字符'";
                if (preg_match('/e[_-]?mail/i', $key)) {
                    $rule[] = 'email:true';
                    $msg[] = "email:'请输入正确的email地址'";
                }
            }
            if ($key == $pk ||
             in_array($key, $this->_table->getUniqueKeys(), true)) {
                $uri = $baseUri . 'isValid';
                $rule[] = 'remote:{url:"' . $uri . '", data: {' . $key .
                 ' : function(){return $("#' . $key . '").val();},' . $pk .
                 ' : function(){return $("#rawPrimaryKey").val();}}}';
                $msg[] = "remote:'{$key}已经存在，请重新输入'";
            }
            $msgs[$key] = join(",", $msg);
            $rules[$key] = join(",", $rule);
        }
        $strRules = "\n    rules:{\n";
        $strMsgs = "\n    messages:{\n";
        foreach ($rules as $field => $rule) {
            $strRules .= "        {$field}:{{$rule}},\n";
            $strMsgs .= "        {$field}:{{$msgs[$field]}},\n";
        }
        $strRules = substr($strRules, 0, - 2) . "\n";
        $strMsgs = substr($strMsgs, 0, - 2) . "\n";
        $strRules .= "    },\n";
        $strMsgs .= "    }\n";
        $js .= $strRules . "\n\n" . $strMsgs;
        $js .= "\n});\n\n"; //拼接validator结束
        $js .= "{$datepickers}\n";
        $js .= "\n});\n";
        $js .= "\n\n</script>\n";
        return $js;
    }
    
    public function form ($config)
    {
        $tableInfo = $this->_table->info();
        $tableName = $this->_table->getName();
        $pk = $this->_table->getPrimaryKey();
        $header = $data = array();
        $table = '';
        foreach ($config as $key => $val) {
            if(!isset($tableInfo[$key]) || $tableInfo[$key]['Extra'] == 'auto_increment'){
                continue;
            }
            
            if (isset($this->_titles[$key])) {
                $title = $this->_titles[$key];
            } else {
                $title = $key;
            }
            if (! isset($val['type']) || empty($val['type'])) {
                if (preg_match('/^(is|has|have)/i', $key)) {
                    $val['type'] = 'radio';
                } elseif (strpos($tableInfo[$key]['Type'], 'text') !== false) {
                    $val['type'] = 'textarea';
                } elseif (strpos($tableInfo[$key]['Type'], 'date') !== false) {
                    $val['type'] = 'date';
                } else {
                    $val['type'] = 'text';
                }
            }
            $field = $this->_createField($val['type'], $key, '$hiTableRow.' . $key);
            
            $table .= "    <tr>\n";
            $table .= "        <td class='hi_form_left'>{$title}：</td>\n";
            $table .= '        <td class="hi_form_right">' . $field . '</td>' . "\n";
            $table .= "    </tr>\n";
        }
        $table .= "    <tr>\n";
        $table .= "        <td class='hi_form_left'></td>\n";
        $table .= "        <td class='hi_form_right'>\n
        						<input type='submit' class='hi_form_submit' name='btnSubmit' id='btnSubmit' value='提交' />\n
        						<input type='button' onclick='window.location.href=\"{\$backurl}\";' class='hi_form_cancel' name='btnCancel' id='btnCancel' value='取消' />
        						<input type='hidden' name='backurl' id='backurl' value='{\$backurl}' />\n
        						<input type='hidden' name='rawPrimaryKey' id='rawPrimaryKey' value='{\$hiTableRow.{$pk}}' />\n
        				   </td>\n";
        $table .= "    </tr>";
        $uri = preg_replace('/\/form.*/i', 
        						"/save?{$pk}=" . '{$row.' . $pk . '}', $_SERVER['REQUEST_URI']);
        $table = "\n\n<form action='{$uri}' name='hiTableForm_{$tableName}' id='hiTableForm_{$tableName}' method='post'>\n<table>\n{$table}\n</table>\n</form>\n\n";
        $js = $this->js($config);
        $table .= "{literal}\n<style>\n.error{color:red;}\n.hi_form_left{text-align:right;}\n.hi_form_right{text-align:left;}\n</style>\n{$js}{/literal}\n";
        $table = '<script type="text/javascript" src="/static/js/common/jquery.validate.js"></script>' .
                 "\n" . $table;
        return $table;
    }
    

    
    private function _getBaseUri ()
    {
        $url = parse_url($_SERVER['REQUEST_URI']);
        $path = $url['path'];
        $path = preg_replace('/\/$/', '', $path);
        if(preg_match('/(index|form)$/i', $path)){
            $path = substr($path, 0, strrpos($path, '/'));
        }
        return $path . '/';
    }
}