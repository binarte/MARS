<?php
namespace mars;
require_once __DIR__.'/mars/System.php';
\spl_AutoLoad_Register (__NAMESPACE__.'\\System::loadClass');
System::addClassPath(__DIR__);
System::setLogDir(__DIR__.'/log');
System::initSession(new Database(CONFIGFILE));
