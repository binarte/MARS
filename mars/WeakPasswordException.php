<?php

namespace mars;

class WeakPasswordException extends \Exception {

	public const Empty = 0;
	public const TooShort = 1;
	public const Numeric = 2;
	public const TooFewChars = 3;
	
	function __construct ($code,$len= null, \Throwable $previous = NULL){
		$code = (int) $code;
		switch ($code){
			case self::Empty:
				$msg = 'Password cannot be empty';
				break;
			case self::TooShort:
				$msg = "Password  must be at least $len characters long";
				break;
			case self::Numeric:
				$msg = 'Password cannot be numeric';
				break;
			case self::TooFewChars:
				$msg = "Password must have at least $len unique characters";
				break;
			default:
				$msg = "Unknown error '$code' attempting to define password";
		}
		parent::__construct($msg,$code,$previous);
	}
	
	
}