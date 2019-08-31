<?php

namespace mars;

class System {
	private static $logDir;
	static function setLogDir($path){
		$path = rtrim($path,'/').'/';
		self::$logDir = $path;
	}

	private static $classPaths = [];
	static function addClassPath($path){
		$path = rtrim($path,'/').'/';
		self::$classPaths[] = $path;
	}
	
	static function loadClass($class) {
		$cpath = str_Replace('\\','/',$class).'.php';
		
		foreach (self::$classPaths as $path){
			$file = $path.$cpath;
			if (file_exists ($file) ) {
				require_once($file);
				if (class_exists ( $class, false ) or interface_exists ( $class, false ) ) {
					return true;
				}
			}
		}
		
		throw new ClassLoadException($class,ClassLoadException::FileNotFound);
	}
	
	static function handleUncaughtException(\Throwable $ex) {
		function addToXml(\SimpleXMLElement $to, $what, $name = null){
			$item = $to->addChild('item');
			if (is_int($name)){
				$item['i'] = $name;
			} elseif($name){
				$item['name'] = str_replace("\0",'\\000',$name);
			}
			
			if (is_Object($what) ) {
				$item['class'] = get_class($what);
				foreach ((array) $what as $name=>$value) {
					addToXml($item,$value,str_replace("\0",'-',$name));
				}
			} else {
				$item['type'] = getType($what);
				if (is_Bool($what) ){
					$item['value'] = $what ? 'true' : 'false';
				} elseif (is_array($what) ) {
					foreach ($what as $name=>$value) {
						addToXml($item,$value,$name);
					}
				} elseif (is_string($what) ) {	
					$item[0] = addcslashes($what,"\0");
				} elseif (!is_null($what) ) {
					$item['value'] = (string) $what;
				} 
			}
		}

		$trace = $ex->getTrace();
		$line = $ex->getLine();
		$file = $ex->getFile();
		$log = new \SimpleXMLElement('<LogEntry/>');
		$log['date'] = (new \DateTime)->format(\DateTime::W3C);
		$log['class'] = get_Class($ex);
		$log['file'] = $file;
		$log['line'] = $line;
		$log['code'] = $ex->getCode();
		$log->message = $ex->getMessage();
		if ($ex instanceof DatabaseException){
			$log->sql = $ex->getSql();
		}

		foreach ($trace as $tr){
			if ($file == @$tr['file'] and $line == @$tr['line']){
				continue;
			}
			if(!$log->backTrace) {
				$log->addChild('backTrace');
			}
			$txml = $log->backTrace->addChild('trace');
			$txml['file'] = @$tr['file'];
			$txml['line'] = @$tr['line'];
			if (@$tr['class']){
				$txml['class'] = $tr['class'];
			}
			$txml['function'] = @$tr['function'];
			if (isset ($tr['args']) ) {
				foreach ($tr['args'] as $arg){
					if(!$txml->args) {
						$txml->addChild('args');
					}
					addToXml($txml->args,$arg);
				}
			}
		}

		if (function_exists('\\getAllHeaders') ) {
			$svars = [];
			foreach ($_SERVER as $name=>$var){
				if (stripos($name,'HTTP_') !== 0) {
					$svars[$name] = $var;
				}
			}
			$headers = \getAllHeaders();
		} else {
			$svars = $_SERVER;
			$headers = [];
		}
		
		foreach (self::$classPaths as $path){
			$log->classPaths->path[] = $path;
		}
		
		foreach ([
			'get'=> @$_GET,
			'post'=> @$_POST,
			'files'=> @$_FILES,
			'cookie'=> @$_COOKIE,
			'session'=> @$_SESSION,
			'headers'=> $headers,
			'server'=> $svars,
		] as $src=>$values) if($values) foreach ($values as $param=>$value) {
			if (!$log->params->$src){	
				if(!$log->params){
					$log->addChild('params');
				}
				$log->params->addChild($src);
			}
			addToXml($log->params->$src,$value,$param);
		}
		
		if (!is_dir(self::$logDir) ) mkdir(self::$logDir,0755,true);
		
		$log = $log->saveXML();
		if (self::$logdb){
			self::$logdb->rollback();
			self::$logdb->logError($log);
		} else {			
			echo $log;
			die;
		}
		
		echo '500 internal server error',"\n";
		die;
	}
	
	static function handleError ($errno, $errstr, $errfile, $errline) {
		if (error_reporting() & $errno) {
			throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
		}
	}
	
	static private $logdb;
	static function setLogDatabase(Database $db){
		self::$logdb = $db;
	}
}