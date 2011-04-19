<?php
include 'D:\www\hi\library\Hi\Front.php';

$front = Hi_Front::getInstance();
$front->init('D:\www\hi\demo');
$front->dispatch();