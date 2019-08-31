<?php
namespace mars;

abstract class ObjectBase {

	function __get($param){
		$met = 'get_'.$param;
		if (method_exists ($this , $met) ) {
			return $this->$met();
		}
		if (property_exists($this,$param) ) {
			return $this->$param;
		}
		throw new ObjectException($this,$param,ObjectException::PropertyNotFound);
	}
	
	function __isset($param){
		if (property_exists($this,$param) ) {
			return isset($this->$param);
		}
		return null;
	}
	
	function __set($param,$value){
		$met = 'set_'.$param;
		if (method_exists ($this , $met) ) {
			return $this->$met($value);
		}
		if (property_exists($this,$param) ) {
			throw new ObjectException($this,$param,ObjectException::ReadOnlyProperty);
		}
		throw new ObjectException($this,$param,ObjectException::PropertyNotFound);
	}
	
	function __unset($param){
		$met = 'unset_'.$param;
		if (method_exists ($this , $met) ) {
			return $this->$met();
		}
		return $this->__set($param,null);
	}
}