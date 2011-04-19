<?php
class Hi_Tool_ISBN
{
    public static function to10 ($isbn)
    {
        $isbn = str_replace('-', '', $isbn);
        if (strlen($isbn) != 13 || ! preg_match('/^\d+$/', $isbn)) {
            return $isbn;
        }
        $sum = 0;
        $num = substr($isbn, 3, 9);
        for ($i = 10, $p = 0; $i > 1; $i --, $p ++) {
            $sum += $i * intval($num[$p]);
        }
        $m = $sum % 11;
        $check = 11 - $m;
        return $num . $check;
    }
    public static function to13 ($isbn)
    {
        $isbn = str_replace('-', '', $isbn);
        if (strlen($isbn) != 10 || ! preg_match('/^\d+$/', $isbn)) {
            return $isbn;
        }
        $sum = 0;
        $num = '978' . substr($isbn, 0, 12);
        for ($i = 0; $i < 12; $i ++) {
            $n = $num[$i];
            if (($i + 1) % 2 == 0) {
                $sum += $n * 3;
            } else {
                $sum += $n;
            }
        }
        $m = $sum % 10;
        $check = 10 - $m;
        return $num . $check;
    }
}