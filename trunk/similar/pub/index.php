<?php
include 'd:/www/hi/library/Hi/Front.php';

$front = Hi_Front::getInstance();
$front->init(realpath('../'));
$front->dispatch();