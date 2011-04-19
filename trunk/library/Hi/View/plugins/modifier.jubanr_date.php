<?php
function smarty_modifier_jubanr_date ($date)
{
    $stamp = strtotime($date);
    $isThisYear = false;
    if (date('Y', $stamp) == date('Y')) {
        return date('m-d H:i', $stamp);
    }
    return date('Y-m-d H:i', $stamp);
}