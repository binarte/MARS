<?php

namespace mars;

class ClassPropertyException extends \Exception {

	public const Missing = 1;
	public const InvalidValue = 2;
	
	function __construct ($object, $property,$value, $code,\Throwable $previous = NULL){
		$code = (int) $code;
		$property = (string) $property;
		$clname = get_class($object);
		$svalue = var_export($value,1);
		switch ($code){
			case self::Missing:
				$msg = "Class '$clname' does not have a property '$property'";
				break;
			case self::InvalidValue:
				$msg = "Property '$property' of '$clname' cannot be set to '$svalue'";
				break;
			default:
				$msg = "Unknown error '$code' attempting to set '$property' on '$clname' to '$svalue'";
		}
		parent::__construct($msg,$code,$previous);
		$this->object_ = $object;
		$this->property = $property;
	}
	
	private $object_;
	final function getObject(){
		return $this->object_;
	}
	
	private $property;
	final function getProperty(){
		return $this->property;
	}
	
}