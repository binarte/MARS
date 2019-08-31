<?php


const CONFIGFILE = 'config.ini';
require('mars.php');


$test = $db->create('\\Test');
$test->owner = $db->open('\\User',['username' => 'test'] );
$test->save();

$test->owner->password = 'foobarbaz';
$test->owner->save();
var_dump ($test);