<?php
namespace mars;

abstract class ObjectBase
{

    public const T_DbObject = 1;

    public const T_Text = 2;

    public const T_Binary = 3;

    public const T_Integer = 4;

    public const T_Float = 5;

    public const T_Timestamp = 6;

    public const T_DateTime = 7;

    public const T_Boolean = 8;

    public const T_Json = 9;

    public const T_Xml = 10;

    protected static $defaultDb;

    static function setDefaultDb(Database $db)
    {
        self::$defaultDb = $db;
    }

    private static $tz;

    /**
     *
     * @return \DateTimeZone
     */
    static function tz()
    {
        if (self::$tz === null) {
            self::$tz = new \DateTimeZone('utc');
        }
        return self::$tz;
    }

    protected function checkProperty($var)
    {
        if (! is_string($var)) {
            throw new InvalidInputException(InvalidInputException::WrongType, 0, 'string');
        }
        if ($var == '') {
            throw new InvalidInputException(InvalidInputException::EmptyValue, 0);
        }
        if ($var['0'] == '_') {
            throw new ClassPropertyException($this, $var, null, ClassPropertyException::Access);
        }
    }

    function __get($var)
    {
        $this->checkProperty($var);
        
        $gm = 'get_' . $var;
        if (method_exists($this, $gm)) {
            return $this->$gm();
        }
        
        if (! property_exists($this, $var)) {
            throw new ClassPropertyException($this, $var, null, ClassPropertyException::Missing);
        }
        
        if ($this->$var === null) {
            $this->getVar($var);
        }
        
        return $this->$var;
    }

    function __isset($var)
    {
        $this->checkProperty($var);
        $gm = 'get_' . $var;
        if (method_exists($this, $gm)) {
            return $this->$gm();
        }
        
        if (! property_exists($this, $var)) {
            return null;
        }
        
        if ($this->$var !== null) {
            return true;
        }
        
        $this->getVar($var);
        return $this->$var !== null;
    }

    function __set($var, $value)
    {
        $this->checkProperty($var);
        
        $sm = 'set_' . $var;
        if (method_exists($this, $sm)) {
            return $this->$sm($value);
        }
        if (! property_exists($this, $var)) {
            throw new ClassPropertyException($this, $var, null, ClassPropertyException::Missing);
        }
        
        $fld = $this->getFieldInfo($var);
        if (! $fld or @$fld['read-only']) {
            throw new ClassPropertyException($this, $var, null, ClassPropertyException::ReadOnly);
        }
        if ($value === null) {
            if (@$fld['nullable']) {
                $this->$var = null;
                return;
            } elseif (@$fld['default']) {
                $this->$var = $fld['default'];
                return;
            }
            throw new ClassPropertyException($this, $var, null, ClassPropertyException::InvalidValue);
        }
        
        switch ($fld['type']) {
            case self::T_DbObject:
                if ($fld['class'] != get_class($value)) {
                    throw new ClassPropertyException($this, $var, $value, ClassPropertyException::WrongClass);
                }
                $this->$var = $value;
                return;
            case self::T_Integer:
                $value = (int) $value;
                if ((isset($fld['max']) and $value > $fld['max']) or (isset($fld['min']) and $value < $fld['min'])) {
                    throw new ClassPropertyException($this, $var, $value, ClassPropertyException::OutOfRange);
                }
                $this->$var = $value;
                return;
            case self::T_Float:
                $value = (float) $value;
                if ((isset($fld['max']) and $value > $fld['max']) or (isset($fld['min']) and $value < $fld['min'])) {
                    throw new ClassPropertyException($this, $var, $value, ClassPropertyException::OutOfRange);
                }
                $this->$var = $value;
                return;
            case self::T_Text:
                $value = (string) $value;
                if (isset($fld['maxlength']) and mb_StrLen($value, 'utf-8') > $fld['maxlength']) {
                    throw new ClassPropertyException($this, $var, $value, ClassPropertyException::TooLong);
                }
                $this->$var = $value;
                return;
            case self::T_Timestamp:
            case self::T_DateTime:
                if ($value instanceof \DateTimeInterface) {
                    if ($value instanceof \DateTimeImmutable) {
                        $value = \DateTime::createFromImmutable($value);
                    } else {
                        $value = clone $value;
                    }
                    $value->setTimezone(self::tz());
                    $this->$var = \DateTimeImmutable::createFromMutable($value);
                } elseif (is_numeric($value)) {
                    $this->$var = new \DateTimeImmutable('@' . $value, self::tz());
                } else {
                    $this->$var = new \DateTimeImmutable($value, self::tz());
                }
                return;
            case self::T_Boolean:
                $this->$var = (bool) $value;
                return;
            case self::T_Xml:
                if (! ($value instanceof \SimpleXMLElement)) {
                    $value = new \SimpleXMLElement($value);
                }
                $this->$var = $value;
                return;
            case self::T_Json:
                if (is_scalar($value)) {
                    $this->$var = $value;
                } elseif (! is_array($value)) {
                    throw new ClassPropertyException($this, $var, $value, ClassPropertyException::WrongType);
                }
                return;
        }
    }

    function toXML()
    {
        $cname = str_replace('\\', '-', get_class($this));
        return new \SimpleXMLElement("<$cname/>");
    }

    function __unset($param)
    {
        $this->checkProperty($var);
        
        $sm = 'unset_' . $var;
        if (method_exists($this, $sm)) {
            return $this->$sm($value);
        }
        if (! property_exists($this, $var)) {
            return;
        }
        
        $fld = $this->getFieldInfo($var);
        if (! $fld or @$fld['read-only']) {
            throw new ClassPropertyException($this, $var, null, ClassPropertyException::ReadOnly);
        }
        
        if (@$fld['nullable']) {
            $this->$var = null;
            return;
        } elseif (@$fld['default']) {
            $this->$var = $fld['default'];
            return;
        }
        throw new ClassPropertyException($this, $var, null, ClassPropertyException::CannotUnset);
    }

    function getFieldInfo($field)
    {
        for ($cl = get_Class($this); $cl; $cl = get_Parent_Class($cl)) {
            if (isset($cl::$fieldInfo[$field])) {
                return $cl::$fieldInfo[$field];
            }
        }
        return false;
    }

    protected function getVar($var)
    {}
}