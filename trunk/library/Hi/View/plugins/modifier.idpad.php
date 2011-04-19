<?php
function smarty_modifier_idpad ($string, $size, $path = "/user/icon/")
{
    $s = str_pad($string, 4, '0', STR_PAD_LEFT);
    $d = '';
    for ($i = 0; $i < 2; $i ++) {
        $start = $i * 2;
        $d .= substr($s, $start, 2) . DIRECTORY_SEPARATOR;
    }
    return $path . str_replace("\\", "/", $d . $s . $size . ".jpeg");
} 