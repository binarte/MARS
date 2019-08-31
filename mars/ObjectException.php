<?php

namespace mars;

class ObjectException extends \Exception {
	public const PropertyNotFound = 1;
	public const ReadOnlyProperty = 2;
	
	function __construct (ObjectBase $caller, $param, $code,\Throwable $previous = NULL){
		$cl = get_Class($caller);
		$code = (int) $code;
		switch ($code){
			case self::PropertyNotFound:
				$msg = "Property '$param' not found in '$cl'";
				break;
			case self::ReadOnlyProperty:
				$msg = "Property '$param' is read-only in '$cl'";
				break;
			default:
				$msg = "Unknown error '$code' in '$param' for a '$cl'";
		}
		parent::__construct($msg,$code,$previous);
		$this->caller = $caller;
		$this->param = $param;
	}
	
	private $caller;
	final function getCaller(){
		return $this->caller;
	}
	
	private $param;
	final function getParam(){
		return $this->param;
	}
	
}
