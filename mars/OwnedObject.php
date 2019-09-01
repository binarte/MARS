<?php
namespace mars;

abstract class OwnedObject extends DatabaseObject {
	static protected $fieldInfo = [
		'owner' => [
			'type' => self::T_DbObject,
			'class' => __NAMESPACE__.'\\User',
			'cascade' => true,
		],
		'otherPermissions' => [
			'type' => self::T_Integer,
			'min' => 0,
			'max' => 0xFF,
		],
	];
	
	protected $owner;
	protected $permissions = 0xFFF;
	
	function hasPermission($permission){
		if (isset ($this->_ids['owner']) and System::user()->id != $this->_ids['owner']){
			if (($this->permissions & $permission) != $permission) {
				return false;
			}
		}
		
		return parent::hasPermission($permission);
	}

}