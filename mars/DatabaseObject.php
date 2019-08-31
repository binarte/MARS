<?php
namespace mars;

abstract class DatabaseObject {
    public const P_Access = 1;
    public const P_Create = 2;
    public const P_Update = 4;
    public const P_Delete = 8;
    static function permissionName($permission) {
        $out = '';
        foreach ([
            self::P_Access => 'access',
            self::P_Create => 'create',
            self::P_Update => 'update',
            self::P_Delete => 'delete',
        ] as $id=>$name){
            if ($permission & $id){
                if ($out){
                    $out .= ', ';
                }
                $out .= $name;
                $permission -= $id;
            }
        }
        
        if ($permission){
            if ($out){
                $out .= ', ';
            }
            $out .= 'unknown';            
        }
        if (!$out){
            return 'none';
        }
        return $out;
    }
    
	private static $tz;
	/**
	 * 
	 * @return \DateTimeZone
	 */
	protected static function tz(){
		if (self::$tz === null) {
			self::$tz = new \DateTimeZone('utc');
		}
		return self::$tz;
	}
	
	public const T_Object = 1;
	public const T_Text = 2;
	public const T_Binary = 3;
	public const T_Integer = 4;
	public const T_Float = 5;
	public const T_Timestamp = 6;
	public const T_DateTime = 7;
	public const T_Boolean = 8;

	
	private $id;
	private $saved;
	private $added;
	private $updated;

	protected $_ids = [];
	protected $_db;
	
	function __construct(Database $db){
		$this->_db = $db;
	}
	
	protected function query($sql){
		try {
			return $this->_db->query($sql);
		} catch (DatabaseException $ex) {
			switch($ex->getCode() ){
			case Database::ERR_MissingTable:
			case Database::ERR_MissingFields:
				$this->_db->createTable(get_class($this), $this->getFields() , $this->getIndexes() );
				return $this->query($sql);			
			}			
			throw $ex;
		}
	}
	
	function open($id,$throwex = false){
		if ($this->saved){
			return false;
		}
		$sql = 'Select * From [['.get_class($this).']] Where ';
		if (is_array ($id) ) {
			$start = false;
			foreach ($id as $key=>$value) {
				$this->__set($key,$value);
				if ($start){
					$sql .= ', ';
				}
				$start = true;
				$sql .= $this->_db->escapeField($key). ' = '. $this->_db->escape($value);
			}
		} else {			
			$sql .= '"id" = '.(int)$id;
		}
		$res = $this->query($sql)->fetch_Assoc();
		if (!$res) {
			if ($throwex){
				throw new DataNotFoundException($this,$id);
			}
			return false;
		}
		
		$this->saved = true;
		$this->added = new \DateTimeImmutable($res['added'], self::tz());
		$this->updated = new \DateTimeImmutable($res['updated'], self::tz());
		$this->id = (int) $res['id'];
		
		foreach ($this->getFields() as $fname=>$field){
			$value = $res[$fname];
			if ($field['type'] == self::T_Object) {
				$value = (int) $value;
				$this->_ids[$fname] = $value ? $value : null;
				continue;
			}
			
			if ($value !== null){
				switch ($field['type']){
					case self::T_Binary:
						$value = bin2hex($value);
						break;
					case self::T_Boolean:
						$value = (bool) $value;
						break;
					case self::T_Integer:
						$value = (int) $value;
						break;
					case self::T_Float:
						$value = (float) $value;
						break;
					case self::T_Timestamp:
					case self::T_DateTime:
						$value = new \DateTimeImmutable($value,self::tz() );
						break;
				}
			}
			$this->$fname = $value;
		}
		
		return true;
	}
	
	private function checkNullable($fname,$field){
		if ($this->$fname === null){
			if (!@$field['nullable']){
				throw new ClassPropertyException($this,$fname,null,ClassPropertyException::InvalidValue);
			}
		}
	}
	
	private function sqlValue($fname,$field) {
		if ($field['type'] == self::T_Object){
			$value = $this->$fname;
			if ($value) {
				if (!$value->saved){
					$value->save();
				}
				return $value->id;
			}
			return $this->_db->escape(null);
		} elseif ($field['type'] == self::T_Binary) {
			return 'UNHEX('.$this->_db->escape($this->$fname).')';
		}
		return $this->_db->escape($this->$fname);
	}
	
	function save(){
		if ($this->saved) {
			$this->checkPermission(self::P_Update);
			$sql = 'Update [['.get_class($this).']] Set ';
			$start = false;
			foreach ($this->getFields() as $fname=>$field) {
				$this->checkNullable($fname,$field);

				if ($start) {
					$sql .= ', ';
				}
				$start = true;
				
				$sql .= $this->_db->escapeField($fname).'='.$this->sqlValue($fname,$field);
			}
			
			$sql .= 'Where "id" = '.$this->id;
			$this->query($sql);
			return false;
		} else {
			$this->checkPermission(self::P_Create);
			$sql = 'Insert Into [['.get_class($this).']] (';
			$values = '';
			foreach ($this->getFields() as $fname=>$field) {
				$this->checkNullable($fname,$field);
				if ($values) {
					$values .= ', ';
					$sql .= ', ';
				}
				$sql .= $this->_db->escapeField($fname);
				$values .= $this->sqlValue($fname,$field);
			}
			$sql .= ') Values ('.$values.')';
			$this->query($sql);
			$this->saved = true;
			$this->id = $this->_db->insertId();
			return true;
		}
	}
	
	
	
	final function checkPermission($permission){
		if (!$this->hasPermission($permission) ) {
			throw new AccessDeniedException($this,$permission);
		}
	}
	
	function hasPermission($permission){
		return true;
	}
	
	final function canDelete(){
		return $this->hasPermission(self::PERM_DELETE);
	}
	
	final function canAccess(){
		return $this->hasPermission(self::PERM_ACCESS);
	}
	
	final function canCreate(){
		return $this->hasPermission(self::PERM_CREATE);
	}
	
	final function canUpdate(){
		return $this->hasPermission(self::PERM_UPDATE);
	}
	
	private function checkProperty($var){
		if (!is_string($var)){
			throw new InvalidInputException(InvalidInputException::WrongType,0,'string');
		}
		if ($var == ''){
			throw new InvalidInputException(InvalidInputException::EmptyValue,0);
		}
		if ($var['0'] == '_'){
			throw new ClassPropertyException($this,$var,null,ClassPropertyException::Access);
		}
	}

	function getFields(){
		$out = [];
		for($cl = get_Class($this); $cl; $cl = get_Parent_Class($cl) ) {
			if (isset($cl::$fieldInfo) ) {			
				$out = array_merge($out,$cl::$fieldInfo);
			}
		}
		return $out;
	}

	function getIndexes(){
		$out = [];
		for($cl = get_Class($this); $cl; $cl = get_Parent_Class($cl) ) {
			if (isset($cl::$indexes) ) {			
				$out = array_merge($out,$cl::$indexes);
			}
		}
		return $out;
	}
	
	function getFieldInfo($field){
		for($cl = get_Class($this); $cl; $cl = get_Parent_Class($cl) ) {
			if (isset($cl::$fieldInfo[$field]) ) {
				return $cl::$fieldInfo[$field];
			}
		}
		return false;
	}
	
	function __get($var){
		$this->checkProperty($var);
	
		$gm = 'get_'.$var;
		if (method_exists ($this,$gm) ) {
			return $this->$gm();
		}
		
		if (!property_exists($this,$var) ) {
			throw new ClassPropertyException($this,$var,null,ClassPropertyException::Missing);
		}

		if ($this->$var === null) {
			$fld = $this->getFieldInfo($var);
			if ($fld['type'] == self::T_Object) {
				$i = @$this->_ids[$var];
				if (!$i) {
					return null;
				}
				$this->$var = $this->db->open($fld['class'],$i);
			}
		}

		return $this->$var;
	}
	
	function __set($var,$value){
		$this->checkProperty($var);
		
		$sm = 'set_'.$var;
		if (method_exists ($this,$sm) ) {
			return $this->$sm($value);
		}
		if (!property_exists($this,$var) ) {
			throw new ClassPropertyException($this,$var,null,ClassPropertyException::Missing);
		}
		
		$fld = $this->getFieldInfo($var);
		if (!$fld or @$fld['read-only']){
			throw new ClassPropertyException($this,$var,null,ClassPropertyException::ReadOnly);
		}
		if ($value === null) {
			if (@$fld['nullable']){
				$this->$var = null;
				return;
			} elseif (@$fld['default']){
				$this->$var = $fld['default'];
				return;
			}
			throw new ClassPropertyException($this,$var,null,ClassPropertyException::InvalidValue);
		}
		
		switch ($fld['type']){
			case self::T_Object:
				if ($fld['class'] != get_class($value) ){
					throw new ClassPropertyException($this,$var,$value,ClassPropertyException::WrongClass);
				}
				$this->$var = $value;
				return;
			case self::T_Integer:
				$value = (int) $value;
				if (
					(isset ($fld['max']) and $value > $fld['max']) or
					(isset ($fld['min']) and $value < $fld['min'])
				){
					throw new ClassPropertyException($this,$var,$value,ClassPropertyException::OutOfRange);
				}
				$this->$var = $value;
				return;
			case self::T_Float:
				$value = (float) $value;
				if (
					(isset ($fld['max']) and $value > $fld['max'])or
					(isset ($fld['min']) and $value < $fld['min'])
				) {
					throw new ClassPropertyException($this,$var,$value,ClassPropertyException::OutOfRange);
				}
				$this->$var = $value;
				return;
			case self::T_Text:
				$value = (string) $value;
				if (isset ($fld['maxlength']) and mb_StrLen($value,'utf-8') > $fld['maxlength']) {
					throw new ClassPropertyException($this,$var,$value,ClassPropertyException::TooLong);
				}
				$this->$var = $value;
				return;			
			case self::T_Timestamp:
			case self::T_DateTime:
				if ($value instanceof \DateTimeInterface){
					if ($value instanceof \DateTimeImmutable){
						$value = \DateTime::createFromImmutable($value);
					} else {
						$value = clone $value;
					}
					$value->setTimezone(self::tz());
					$this->$var = \DateTimeImmutable::createFromMutable($value);
				} elseif (is_numeric($value) ) {
					$this->$var = new \DateTimeImmutable('@'.$value,self::tz());
				} else {
					$this->$var = new \DateTimeImmutable($value,self::tz());
				}
				return;				
			case self::T_Boolean:
				$this->$var = (bool) $value;
				return;		

		}
	}
}