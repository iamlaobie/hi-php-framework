<?php
include '../../library/Hi/Front.php';

$front = Hi_Front::getInstance();
$front->init('../../demo');
$front->dispatch();