<?php
namespace mars;
require_once __DIR__.'/class/mars/System.php';
System::addClassPath(__DIR__.'/class');

\spl_AutoLoad_Register (__NAMESPACE__.'\\System::loadClass');
\set_Exception_Handler (__NAMESPACE__.'\\System::handleUncaughtException');
\set_Error_Handler (__NAMESPACE__.'\\System::handleError');

System::setLogDir(__DIR__.'/log');
$db = new Database(CONFIGFILE);
System::setLogDatabase($db);
foreach ($db->classPaths() as $path) {
	System::addClassPath($path);
}
date_default_timezone_set('utc');
