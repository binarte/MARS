<?php

const CONFIGFILE = 'config.ini';
require 'mars.php';

$sql = 'Select "content" From [[*errorLog]] Order By "id" Desc Limit 1';
$res = $db->query($sql)->fetch_row();
if ($res) {
	header('Content-Type: text/xml');
	echo $res[0];
}
