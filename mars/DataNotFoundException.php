<?php

namespace mars;

class DataNotFoundException extends \Exception {

	public const Empty = 0;
	public const TooShort = 1;
	public const Numeric = 2;
	public const TooFewChars = 3;
	
	function __construct (DatabaseObject $obj,$id,\Throwable $previous = NULL){
		if (is_array($id) ){
			$istr = '';
			foreach ($id as $k=>&$v){
				$v = (string) $v;
				if ($istr){
					$istr .= ', ';
				}
				$istr = "\"$k\"='$v'";
			}
		} else {
			$id = (int) $id;
			$istr = (string) $id;
		}		
		
		parent::__construct('Could not find data of type \''.get_class($obj). '\' with the id '.$istr,0,$previous);
		$this->obj = $obj;
		$this->id = $id;
	}
	
	private $id;
	function getId(){
		return $this->id;
	}
	
	private $obj;
	function getObject(){
		return $this->obj;
	}
	
	
}