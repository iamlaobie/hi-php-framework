<?php
class Action_Segment extends Hi_Action
{
    private $_db;
    private $_scws;
    
    public function __construct()
    {
        $this->_db = Hi_Db::get();
        $this->_scws = scws_open();
        scws_add_dict($this->_scws, 'D:\www\jubanr_bak\scws-db\dict.utf8.xdb', SCWS_XDICT_XDB);
    }
    
    public function index()
    {
        $r1 = $this->_do($a1);
        $r2 = $this->_do($a2);
        $w1 = Hi_Tool_Array::colume($r1, 'times');
        $w2 = Hi_Tool_Array::colume($r2, 'times');
        echo $this->_correlation($w1, $w2);
    }
    
    public function xyz()
    {
        $sql = "SELECT id FROM article_content where id = 198336";
        $ids = $this->_db->fetchCol($sql, 'id');
        foreach($ids as $id){
            $cw = $this->_getArticleWords($id); 
            $words = array_keys($cw);
            $wl = ceil(count($words) * 0.8);
            $ww = "'" . join("','", $words) . "'";
            $sql = "SELECT COUNT(*) AS cnt, article_id FROM article_words WHERE word IN ({$ww}) AND article_id <> {$id} GROUP BY article_id HAVING cnt > {$wl}";
            $r = $this->_db->fetchAll($sql);   
            if(count($r) == 0){
                continue;
            }
            
            for($i = 0, $n = sizeof($r); $i < $n; $i++){
                $relWords = $this->_getArticleWords($r[$i]['article_id']);
                $t = array();
                foreach($words as $word){
                    if(!isset($relWords[$word])){
                        $t[$word] = 0;
                    }else{
                        $t[$word] = $relWords[$word];
                    }
                }
                $cor = $this->_correlation(array_values($cw), array_values($t));
                //if($cor >= 0.95 && $cor <= 0.98){
                    echo $id . ',' . $r[$i]['article_id'] . ',' . $cor . "\n";
                //}
            }
        }

    }
    
    private function _getArticleWords($id)
    {
        $sql = "SELECT word,times FROM article_words WHERE article_id = {$id} ORDER BY times DESC";
        $cw = $this->_db->fetchKeyValue($sql, 'word', 'times'); 
        return $cw;
    }
    
    
    private function _do($content, $len = 20)
    {
        scws_send_text($this->_scws, $content);
        $words = scws_get_tops($this->_scws, $len);
        return $words;
    }
    
    private function _correlation($w1, $w2)
    {
        $x = $y = $z = 0;
        for($i = 0, $n = sizeof($w1); $i < $n; $i++){
           $x += $w1[$i] * $w2[$i];
           $y += $w1[$i] * $w1[$i];
           $z += $w2[$i] * $w2[$i];
        } 
        $c = $x / sqrt($y * $z);
        return $c;
    }
    
    public function setWords()
    {
        $sql = "SELECT id, title, content FROM article_content ORDER BY id DESC";
        $page = 0;
        while(true){
            echo date('Y-m-d H:i:s') . "\n";
            $offset = $page * 1000;
            $xsql = $sql . " LIMIT 1000 OFFSET {$offset}";
            echo $xsql . "\n";
            $rows = $this->_db->fetchAll($xsql);
            if(empty($rows)){
                break;
            }
            foreach($rows  as $row){
                $txt = $row['title'] . $row['content'];
                $len = ceil(mb_strlen($txt, 'UTF-8') * 0.02);
                if($len > 20){
                    $len = 20;
                }else if ($len < 5){
                    $len = 5;
                }
                
                $words = $this->_do($txt, $len);
                $vs = '';
                foreach($words as $w){
                    $vs .= "(NULL, {$row['id']}, '{$w['word']}', {$w['times']}, CURRENT_TIMESTAMP),";
                }
                $isql = "INSERT INTO article_words VALUES" . substr($vs, 0, -1) . " ON DUPLICATE KEY UPDATE in_time = CURRENT_TIMESTAMP";
                $this->_db->query($isql);
            }
            $page += 1;
            echo date('Y-m-d H:i:s') . "\n";
        }
    }
    
}