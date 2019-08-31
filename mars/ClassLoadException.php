<?php

namespace mars;

class ClassLoadException extends \Exception {
	public const FileNotFound = 1;
	public const ClassNotFound = 2;
	private $classname;

	function __construct($classname, $code, \Throwable $previous = NULL) {
		$code = (int)$code;
		switch ($code) {
		case self::FileNotFound :
			$msg = "Could not find class file for '$classname'";
			break;
		case self::ClassNotFound :
			$msg = "'$classname' not found in class file";
			break;
		default :
			$msg = "Unknown error '$code' attempting to load '$classname'";
		}
		parent::__construct ( $msg, $code, $previous );
		$this->classname = $classname;
	}

	final function getClassname() {
		return $this->classname;
	}
}