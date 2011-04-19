<?php
class Hi_Log
{
    /**
     * 保存日誌內容到文件
     * 
     * @param $logFile
     * @param $content
     * @return bool
     */
    public static function save ($logFile, $content)
    {
        if (! Hi_Tool_Dir::isAbsolute($logFile)) {
            $logFile = Hi_Env::$PATH_LOG . DS . $logFile;
        }
        $dir = dirname($logFile);
        Hi_Tool_Dir::check($dir);
        if (! is_string($content)) {
            $content = var_export($content, true);
        }
        $content = date('Y-m-d H:i:s') . "\t" . $content . "\n";
        $fp = fopen($logFile, 'a');
        fwrite($fp, $content, strlen($content));
        fclose($fp);
        return true;
    }
    /**
     * 按小時保存日志
     * 
     * @param $content
     * @param $name
     * @return unknown_type
     */
    public static function hour ($content, $name = null)
    {
        $hour = date('Y-m-d-H');
        $logFile = $name . '_' . $hour . '.log';
        return self::save($logFile, $content);
    }
    /**
     * 按天保存日志
     * @param $content
     * @param $name
     * @return unknown_type
     */
    public static function day ($content, $name = null)
    {
        $date = date('Y-m-d');
        $logFile = $name . '_' . $date . '.log';
        self::save($logFile, $content);
    }
    /**
     * 按月保存日志
     * @param $content
     * @param $name
     * @return unknown_type
     */
    public static function month ($content, $name = null)
    {
        $month = date('Y-m');
        $logFile = $name . '_' . $month . '.log';
        self::save($logFile, $content);
    }
    /**
     * 按一定的周期将日志名加1
     * 
     * @param $name
     * @param $period hour,day,month可选
     * @return unknown_type
     */
    public static function count ($name, $period = null)
    {
        $time = null;
        if (! empty($period)) {
            $periods = array('hour' => 'Y-m-d-H', 'day' => 'Y-m-d', 
            'month' => 'Y-m');
            if (! array_key_exists($periods, $period)) {
                throw new Hi_Exception('日志周期错误');
            } else {
                $time = date($periods[$period]);
            }
        }
        $file = 'count_' . $name;
        if (empty($time)) {
            $file .= '_' . $time;
        }
        if (! is_file($file)) {
            $cnt = 1;
        } else {
            $cnt = file_get_contents($file);
            $cnt = intval($cnt) + 1;
        }
        file_put_contents($file, $cnt);
    }
}