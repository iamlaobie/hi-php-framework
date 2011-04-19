<?php
class Hi_Tool_Pager implements Hi_Db_Table_Pager_Interface
{
    /**
     * 在一些场景下，不能直接操作pager的对象，可在预知构造实例之前设置该值
     * 
     * @var 
     */
    public static $limit = 35;
    /**
     * 每页条数
     *
     * @var int
     */
    protected $_limit;
    /**
     * 当前页
     *
     * @var int
     */
    protected $_index;
    /**
     * 是否还有下一页
     *
     * @var bool
     */
    protected $_hasNext = true;
    /**
     * 是否还有上一页
     *
     * @var bool
     */
    protected $_hasPrev = true;
    /**
     * 记录条数
     *
     * @var int
     */
    protected $_recordCount;
    /**
     * 总页数
     *
     * @var int
     */
    protected $_pageCount;
    /**
     * 偏移量
     *
     * @var int
     */
    protected $_offset = null;
    /**
     * 数据集合
     * 
     * @var array
     */
    public $data;
    public function __construct ($recordCount = 9999, $limit = null, $index = null)
    {
        if (! empty($limit)) {
            $_GET['_pg'] = $limit;
        }
        if (! empty($index)) {
            $_GET['_pi'] = $index;
        }
        $this->_recordCount = $recordCount;
        $this->_setup();
    }
    protected function _setup ()
    {
        if (isset($_GET['pi'])) {
            $_GET['_pi'] = $_GET['pi'];
        }
        @ $pg = intval($_GET['_pg']);
        $limit = empty($pg) ? self::$limit : $pg;
        $this->_limit = $limit;
        $this->_index = empty($_GET['_pi']) ? 1 : intval($_GET['_pi']);
        $this->setPageCount();
    }
    public function limit ($sql)
    {
        $sql .= " limit " . $this->getLimit();
        $sql .= " offset " . $this->getOffset();
        return $sql;
    }
    public function setTotal ($total)
    {
        $this->_recordCount = $total;
        $this->setPageCount();
    }
    public function setRows (array $rows)
    {
        $this->data = $rows;
    }
    public function getRows ()
    {
        return $this->data;
    }
    public function getLimit ()
    {
        return $this->_limit;
    }
    public function getIndex ()
    {
        return $this->_index;
    }
    public function setPageCount ()
    {
        $this->_pageCount = ceil($this->_recordCount / $this->_limit);
        if ($this->_pageCount <= $this->_index) {
            $this->_hasNext = false;
        }
        if ($this->_index <= 1) {
            $this->_hasPrev = false;
        }
    }
    public function getPageCount ()
    {
        return $this->_pageCount;
    }
    public function getRecordCount ()
    {
        return $this->_recordCount;
    }
    public function setOffset ()
    {
        if ($this->_index > $this->_pageCount && $this->_pageCount > 0) {
            $this->_index = $this->_pageCount;
        }
        if ($this->_index < 1) {
            $this->_index = 1;
        }
        return $this->_offset = ($this->_index - 1) * $this->_limit;
    }
    public function getOffset ()
    {
        if (is_null($this->_offset)) {
            $this->setOffset();
        }
        return $this->_offset;
    }
    public function build ()
    {
        $href = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $href = preg_replace('/&*pi=\d*/', '', $href);
        $href = str_replace('%', '#', $href);
        if (! strstr($href, '?')) {
            $href .= '?';
        }
        if (substr($href, - 1) != '?') {
            $href .= '&';
        }
        $withoutIndexHref = $href;
        $href .= 'pi={$pi}';
        $href = "<a href='{$href}'>";
        $fpager = "共 %s 条 %s 页 | 每页 %s 条 | 当前第%s页 | %s上一页%s | %s下一页%s | 到第<input onkeyup='if(event.keyCode == 13){window.location.href=\"{$withoutIndexHref}pi=\" + this.value;}' type='text' style='width:15px;' id='pageText' value='{$this->_index}'>页";
        if ($this->_hasPrev === true) {
            $phrefhead = str_replace('{$pi}', $this->_index - 1, $href);
            $phrefend = '</a>';
        } else {
            $phrefhead = '';
            $phrefend = '';
        }
        if ($this->_hasNext === true) {
            $nhrefhead = str_replace('{$pi}', $this->_index + 1, $href);
            $nhrefend = '</a>';
        } else {
            $nhrefhead = '';
            $nhrefend = '';
        }
        $fpager = sprintf($fpager, $this->_recordCount, $this->_pageCount, 
        $this->_limit, $this->_index, $phrefhead, $phrefend, $nhrefhead, 
        $nhrefend);
        return str_replace('#', '%', $fpager);
    }
    protected function _href ()
    {
        $href = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $href = preg_replace('/&?\_pi=\d*/', '', $href);
        $href = preg_replace('/&?\_pg=\d*/', '', $href);
        if (! strstr($href, '?')) {
            $href .= '?';
        }
        if (substr($href, - 1) != '?') {
            $href .= '&';
        }
        return $href;
    }
    protected function _piHref ()
    {
        $href = $this->_href();
        return $href . "_pg={$this->_limit}&";
    }
    protected function _pgHref ()
    {
        $href = $this->_href();
        return $href . "_pi={$this->_index}&";
    }
    public function build2 ()
    {
        $href = $this->_piHref();
        $left = $this->_index - 2;
        if ($left < 1) {
            $left = 1;
        }
        if ($left + 4 > $this->_pageCount) {
            $left -= $this->_pageCount - $left;
        }
        if ($left < 1) {
            $left = 1;
        }
        $s = '&nbsp;';
        for ($i = $left; $i <= $left + 4; $i ++) {
            if ($this->_index != $i) {
                $s .= "<a href='{$href}_pi={$i}'>{$i}</a>&nbsp;";
            } else {
                $s .= '<b>' . $i . '</b>&nbsp;';
            }
            if ($i >= $this->_pageCount) {
                break;
            }
        }
        $l = '';
        if ($left != 1) {
            $x = $left > 3 ? 3 : $left;
            for ($i = 1; $i < $x; $i ++) {
                $l .= "<a href='{$href}_pi={$i}'>{$i}</a>&nbsp;";
            }
            $x = intval(($x + $left) / 2);
            $s = $l . "<a href='{$href}_pi={$x}'>...</a>" . $s;
        }
        $r = '';
        if ($left + 4 < $this->_pageCount) {
            for ($i = $this->_pageCount; $i > $this->_pageCount - 2; $i --) {
                if ($i <= $left + 4) {
                    break;
                }
                $r = "<a href='{$href}_pi={$i}'>{$i}</a>&nbsp;" . $r;
            }
            if (true) {
                $x = intval(($i + $left + 5) / 2);
                $s = $s . "<a href='{$href}_pi={$x}'>...</a>&nbsp;";
            }
            $s .= $r;
        }
        return $s . $this->_createPerpageDiv();
    }
    protected function _createPerpageDiv ()
    {
        $href = $this->_pgHref();
        $d = array(10, 20, 35, 50, 100);
        $str = '';
        foreach ($d as $v) {
            $str .= "<a style=\'text-decoration:none;width:100%;\' href=\'{$href}_pg={$v}\'>{$v}</a><br />";
        }
        $js = "\n<script>
		function showpgdiv(e)
	{
		var pgdiv = document.getElementById('pgdiv');
		if(pgdiv){
			pgdiv.style.display='block';
			return;
		}
		var scrollY = window.pageYOffset || document.documentElement.scrollTop;
		var pgdiv = document.createElement('div');
		pgdiv.id = 'pgdiv';
		pgdiv.style.width='25px';
		pgdiv.style.position='absolute';
		pgdiv.style.left=e.clientX - 9 + 'px';
		pgdiv.style.top=e.clientY + scrollY - 80 + 'px';
		pgdiv.style.border='1px solid #cccccc';
		pgdiv.style.background='#fff';

		pgdiv.innerHTML='{$str}';

		var handler = function(){
			pgdiv.style.display='none';
		};

		//启动定时器，2秒后隐藏div
		var _timeout = setTimeout(handler, 1000);
		pgdiv.onmouseout = function(){
			_timeout = setTimeout(handler, 1000);
		};

		//如果鼠标在div上，清除定时器
		pgdiv.onmouseover = function(){
			clearTimeout(_timeout);
		}


		document.body.appendChild(pgdiv);

	}
		</script>";
        $p = "每页<a href='javascript:void(0);' onmouseover='showpgdiv(event);'>{$this->_limit}</a>条";
        return $p . $js;
    }
}
?>