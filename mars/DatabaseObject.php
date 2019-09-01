<?php
namespace mars;

abstract class DatabaseObject extends ObjectBase
{
   
    public const P_Access = 1;

    public const P_Create = 2;

    public const P_Update = 4;

    public const P_Delete = 8;

    static function permissionName($permission)
    {
        $out = '';
        foreach ([
            self::P_Access => 'access',
            self::P_Create => 'create',
            self::P_Update => 'update',
            self::P_Delete => 'delete'
        ] as $id => $name) {
            if ($permission & $id) {
                if ($out) {
                    $out .= ', ';
                }
                $out .= $name;
                $permission -= $id;
            }
        }
        
        if ($permission) {
            if ($out) {
                $out .= ', ';
            }
            $out .= 'unknown';
        }
        if (! $out) {
            return 'none';
        }
        return $out;
    }


    protected $id;

    protected $saved;

    protected $added;

    protected $updated;

    protected $_ids = [];

    /**
     * 
     * @var Database
     */
    protected $_db;

    function __construct(Database $db = null)
    {
        if ($db === null) {
            $this->db = self::$defaultDb;
        } else {
            $this->_db = $db;
        }
    }

    protected function query($sql)
    {
        try {
            return $this->_db->query($sql);
        } catch (DatabaseException $ex) {
            switch ($ex->getCode()) {
                case Database::ERR_MissingTable:
                case Database::ERR_MissingFields:
                    $this->_db->createTable(get_class($this), $this->getFields(), $this->getIndexes());
                    $this->createOtherTables();
                    return $this->query($sql);
            }
            throw $ex;
        }
    }

    function open($id, $throwex = false)
    {
        if ($this->saved) {
            return false;
        }
        $sql = 'Select * From [[' . get_class($this) . ']] Where ';
        if (is_array($id)) {
            $start = false;
            foreach ($id as $key => $value) {
                $this->__set($key, $value);
                if ($start) {
                    $sql .= ', ';
                }
                $start = true;
                $sql .= $this->_db->escapeField($key) . ' = ' . $this->_db->escape($value);
            }
        } else {
            $sql .= '"id" = ' . (int) $id;
        }
        $res = $this->query($sql)->fetch_Assoc();
        if (! $res) {
            if ($throwex) {
                throw new DataNotFoundException($this, $id);
            }
            return false;
        }
        
        $this->saved = true;
        $this->added = new \DateTimeImmutable($res['added'], self::tz());
        $this->updated = new \DateTimeImmutable($res['updated'], self::tz());
        $this->id = (int) $res['id'];
        
        foreach ($this->getFields() as $fname => $field) {
            $value = $res[$fname];
            if ($field['type'] == self::T_DbObject) {
                $value = (int) $value;
                $this->_ids[$fname] = $value ? $value : null;
                continue;
            }
            
            if ($value !== null) {
                switch ($field['type']) {
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
                        $value = new \DateTimeImmutable($value, self::tz());
                        break;
                }
            }
            $this->$fname = $value;
        }
        
        return true;
    }

    private function checkNullable($fname, $field)
    {
        if ($this->$fname === null) {
            if (! @$field['nullable']) {
                throw new ClassPropertyException($this, $fname, null, ClassPropertyException::InvalidValue);
            }
        }
    }

    private function sqlValue($fname, $field)
    {
        if ($field['type'] == self::T_DbObject) {
            $value = $this->$fname;
            if ($value) {
                if (! $value->saved) {
                    $value->save();
                }
                return $value->id;
            }
            return $this->_db->escape(null);
        } elseif ($field['type'] == self::T_Binary) {
            return 'UNHEX(' . $this->_db->escape($this->$fname) . ')';
        }
        return $this->_db->escape($this->$fname);
    }

    function save()
    {
        if ($this->saved) {
            $this->checkPermission(self::P_Update);
            $sql = 'Update [[' . get_class($this) . ']] Set ';
            $start = false;
            foreach ($this->getFields() as $fname => $field) {
                $this->checkNullable($fname, $field);
                
                if ($start) {
                    $sql .= ', ';
                }
                $start = true;
                
                $sql .= $this->_db->escapeField($fname) . '=' . $this->sqlValue($fname, $field);
            }
            
            $sql .= 'Where "id" = ' . $this->id;
            $this->query($sql);
            return false;
        } else {
            $this->checkPermission(self::P_Create);
            $sql = 'Insert Into [[' . get_class($this) . ']] (';
            $values = '';
            foreach ($this->getFields() as $fname => $field) {
                $this->checkNullable($fname, $field);
                if ($values) {
                    $values .= ', ';
                    $sql .= ', ';
                }
                $sql .= $this->_db->escapeField($fname);
                $values .= $this->sqlValue($fname, $field);
            }
            $sql .= ') Values (' . $values . ')';
            $this->query($sql);
            $this->saved = true;
            $this->id = $this->_db->insertId();
            return true;
        }
    }

    final function checkPermission($permission)
    {
        if (! $this->hasPermission($permission)) {
            throw new AccessDeniedException($this, $permission);
        }
    }

    function hasPermission($permission)
    {
        return true;
    }

    final function canDelete()
    {
        return $this->hasPermission(self::PERM_DELETE);
    }

    final function canAccess()
    {
        return $this->hasPermission(self::PERM_ACCESS);
    }

    final function canCreate()
    {
        return $this->hasPermission(self::PERM_CREATE);
    }

    final function canUpdate()
    {
        return $this->hasPermission(self::PERM_UPDATE);
    }

    function getFields()
    {
        $out = [];
        for ($cl = get_Class($this); $cl; $cl = get_Parent_Class($cl)) {
            if (isset($cl::$fieldInfo)) {
                $out = array_merge($out, $cl::$fieldInfo);
            }
        }
        return $out;
    }

    function getIndexes()
    {
        $out = [];
        for ($cl = get_Class($this); $cl; $cl = get_Parent_Class($cl)) {
            if (isset($cl::$indexes)) {
                $out = array_merge($out, $cl::$indexes);
            }
        }
        return $out;
    }

    protected function getVar($var){
        
        $fld = $this->getFieldInfo($var);
        if ($fld['type'] == self::T_DbObject) {
            $i = @$this->_ids[$var];
            if (! $i) {
                return;
            }
            $this->$var = $this->db->open($fld['class'], $i);
        }
    }
    
    protected function createOtherTables(){}
 
}