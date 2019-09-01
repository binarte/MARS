<?php
namespace mars;

class Database
{

    const ERR_MissingTable = 1146;

    const ERR_MissingFields = 1054;

    private $cache = [];

    private $connConfig;

    private $settings;

    private $classPaths = [];

    private $conn;

    private $prefix = '';

    function __construct($configFile)
    {
        $cfg = parse_Ini_File($configFile);
        foreach ([
            'host' => 'mysqli.default_host',
            'username' => 'mysqli.default_user',
            'password' => 'mysqli.default_pw',
            'port' => 'mysqli.default_port',
            'socket' => 'mysqli.default_socket'
        ] as $key => $def) {
            if (! isset($cfg[$key])) {
                $cfg[$key] = ini_get($def);
            }
        }
        $cfg['port'] = (int) $cfg['port'];
        
        $this->conn = new \mysqli($cfg['host'], $cfg['username'], $cfg['password'], $cfg['database'], $cfg['port'], $cfg['socket']);
        if (isset($cfg['prefix'])) {
            $this->prefix = $cfg['prefix'];
        }
        
        $this->connConfig = $cfg;
        unset($this->connConfig['password']);
        if (! $this->conn->set_charset('utf8')) {
            throw new DatabaseException($this->conn);
        }
        $this->query('Set SQL_MODE = ANSI_QUOTES');
        $this->query('Set TIME_ZONE = \'+00:00\'');
        
        $sql = 'Select * From [[*settings]]';
        $res = $this->query($sql);
        while ($row = $res->fetch_Assoc()) {
            $this->settings[$row['data']] = $row['value'];
        }
        
        $sql = 'Select "path" From [[*classPaths]] Order By "order" Asc';
        $res = $this->query($sql);
        while ($row = $res->fetch_Row()) {
            $this->classPaths[] = $row[0];
        }
    }

    private function fieldType($finfo)
    {
        $sql = '';
        switch ($finfo['type']) {
            case DatabaseObject::T_Text:
                if (! isset($finfo['maxlength'])) {
                    $sql .= 'Text';
                } elseif ($finfo['maxlength'] > 0x3fffff) {
                    $sql .= 'LongText';
                } elseif ($finfo['maxlength'] > 0x3fff) {
                    $sql .= 'MediumText';
                } elseif ($finfo['maxlength'] > 0xbf) {
                    $sql .= 'Text';
                } else {
                    $sql .= "VarChar({$finfo['maxlength']})";
                }
                $sql .= ' Character Set utf8mb4 Collate utf8mb4_general_ci';
                break;
            case DatabaseObject::T_Binary:
                $sql .= "Binary({$finfo['length']})";
                break;
            case DatabaseObject::T_DbObject:
                $sql .= 'Int Unsigned';
                break;
            case DatabaseObject::T_Integer:
                $min = @$finfo['min'];
                $max = @$finfo['max'];
                if ($min === null and $max === null) {
                    $sql .= 'Int';
                } else if ($min === null) {
                    if ($max > 0x7fffffff) {
                        $sql .= 'BigInt';
                    } elseif ($max > 0x7fffff) {
                        $sql .= 'MediumInt';
                    } elseif ($max > 0x7fff) {
                        $sql .= 'Int';
                    } elseif ($max > 0x7f) {
                        $sql .= 'SmallInt';
                    } else {
                        $sql .= 'TinyInt';
                    }
                } elseif ($min >= 0) {
                    if ($max > 0xffffffff) {
                        $sql .= 'BigInt Unsigned';
                    } elseif ($max > 0xffffff) {
                        $sql .= 'MediumInt Unsigned';
                    } elseif ($max > 0xffff) {
                        $sql .= 'Int Unsigned';
                    } elseif ($max > 0xff) {
                        $sql .= 'SmallInt Unsigned';
                    } else {
                        $sql .= 'TinyInt Unsigned';
                    }
                } else {
                    if ($max > 0x7fffffff or $min < 0x80000000) {
                        $sql .= 'BigInt';
                    } elseif ($max > 0x7fffff or $min < 0x800000) {
                        $sql .= 'MediumInt';
                    } elseif ($max > 0x7fff or $min < 0x8000) {
                        $sql .= 'Int';
                    } elseif ($max > 0x7f or $min < 0x80) {
                        $sql .= 'SmallInt';
                    } else {
                        $sql .= 'TinyInt';
                    }
                }
                break;
            default:
                var_dump($finfo);
                die();
        }
        if (! @$finfo['nullable']) {
            $sql .= ' Not Null';
        }
        return $sql;
    }

    private function dropIndexes($tbclass)
    {
        $sql = "Show Create Table [[$tbclass]]";
        $res = $this->query($sql)->fetch_Assoc()['Create Table'];
        $match = false;
        preg_match_all('#CONSTRAINT\s*"(.*?)"#i', $res, $match);
        $constraints = $match[1];
        
        $sql = "Show Keys From [[$tbclass]] WHERE Key_name != 'PRIMARY'";
        $res = $this->query($sql);
        $keys = [];
        while ($row = $res->fetch_Assoc()) {
            $keys[] = $row['Key_name'];
        }
        
        $sql = "Alter Table [[$tbclass]] ";
        $start = false;
        foreach ($constraints as $constraint) {
            if ($start) {
                $sql .= ',';
            }
            $start = true;
            $sql .= "Drop Foreign Key \"$constraint\"";
        }
        foreach ($keys as $key) {
            if ($start) {
                $sql .= ',';
            }
            $start = true;
            $sql .= 'Drop Key ' . $this->escapeField($key);
        }
        $this->query($sql);
    }

    private function createindexes($tbclass, Array $unique, Array $idx, Array $foreign)
    {
        $sql = "Alter Table [[$tbclass]] ";
        $start = false;
        foreach ($unique as $index) {
            if ($start) {
                $sql .= ', ';
            }
            $start = true;
            $sql .= 'Add Unique Key (';
            $kstart = false;
            foreach ($index as $fname => $field) {
                unset($idx[$fname]);
                if ($kstart) {
                    $sql .= ',';
                }
                $kstart = true;
                $sql .= $this->escapeField($field);
            }
            $sql .= ')';
        }
        foreach ($idx as $field) {
            if ($start) {
                $sql .= ',';
            }
            $start = true;
            $sql .= 'Add Key (' . $this->escapeField($field) . ')';
        }
        
        foreach ($foreign as $field => $finfo) {
            if ($start) {
                $sql .= ',';
            }
            $start = true;
            $sql .= 'Add Constraint [[' . $tbclass . '-' . $field . ']] ' . 'Foreign Key (' . $this->escapeField($field) . ') ' . 'References [[' . $finfo['class'] . ']] ("id") ' . 'On Delete ';
            if (@$finfo['cascade']) {
                $sql .= 'Cascade ';
            } elseif (@$finfo['nullable']) {
                $sql .= 'Set Null ';
            } else {
                $sql .= 'Restrict ';
            }
            $sql .= 'On Update Cascade';
        }
        
        if ($start) {
            $this->query($sql);
        }
    }

    function createTable($tbclass, Array $fields, Array $unique, $addts = true, $idbytes = 4)
    {
        $idx = [];
        $foreign = [];
        try {
            $sql = "Describe [[{$tbclass}]]";
            $res = $this->query($sql);
            $prev = null;
            $miss = $fields;
            foreach ($miss as $fname => &$field) {
                $field['prev'] = $prev;
                $prev = $fname;
                
                if ($field['type'] == DatabaseObject::T_DbObject) {
                    $idx[$fname] = $fname;
                    $foreign[$fname] = $field;
                }
            }
            unset($field);
            while ($row = $res->fetch_assoc()) {
                unset($miss[$row['Field']]);
            }
            $prev = null;
            $sql = "Alter Table [[$tbclass]] ";
            $start = false;
            foreach ($miss as $fname => $field) {
                if ($start) {
                    $sql .= ',';
                }
                $start = true;
                $sql .= 'Add ' . $this->escapeField($fname) . $this->fieldType($field) . ' After ' . $this->escapeField($field['prev']);
            }
            
            if ($start) {
                $this->query($sql);
            } else {
                return false;
            }
            
            $this->dropIndexes($tbclass);
            $this->createIndexes($tbclass, $unique, $idx, $foreign);
            return true;
        } catch (DatabaseException $ex) {
            if ($ex->getCode() != Database::ERR_MissingTable) {
                throw $ex;
            }
        }
        
        switch ((int) $idbytes){
            case 1:
                $idt = 'TinyInt';
                break;
            case 2:
                $idt = 'SmallInt';
                break;
            case 3:
                $idt = 'MediumInt';
                break;
            case 4:
                $idt = 'Int';
                break;
            default:
                $idt = 'BigInt';
        }
                
        $sql = "Create Table [[$tbclass]] (" . '"id" '.$idt.' Unsigned Not Null Primary Key AUTO_INCREMENT';
        foreach ($fields as $fname => $finfo) {
            $sql .= ',' . $this->escapeField($fname) . $this->fieldType($finfo);
            if ($finfo['type'] == DatabaseObject::T_DbObject) {
                $idx[$fname] = $fname;
                $foreign[$fname] = $finfo;
            }
        }
        if ($addts){
            $sql .= ', "added" Timestamp Not Null Default CURRENT_TIMESTAMP' . ', "updated" Timestamp Not Null Default CURRENT_TIMESTAMP On Update CURRENT_TIMESTAMP' . ') ENGINE=InnoDB Default CharSet=utf8mb4';
        }
        $this->query($sql);
        
        $this->createIndexes($tbclass, $unique, $idx, $foreign);
        
        return true;
    }

    function query($sql)
    {
        $sql = preg_Replace_Callback('#\[\[(\\\\)?(.*?)\]\]#', function (Array $match) {
            if ($match[1] == '\\') {
                $out = '"' . $this->prefix . __NAMESPACE__ . '\\' . $match[2] . '"';
                return $out;
            }
            return '"' . $this->prefix . $match[2] . '"';
        }, $sql);
        $res = $this->conn->query($sql);
        if (! $res) {
            throw new DatabaseException($this->conn, $sql);
        }
        return $res;
    }

    /**
     * Creates a new database object
     *
     * @param string $class
     *            name of the class of the object
     * @throws WrongClassException if the object does not inherit the DatabaseObject class
     * @return \mars\DatabaseObject
     */
    function create($class)
    {
        if ($class[0] == '\\') {
            $class = __NAMESPACE__ . $class;
        }
        $out = new $class($this);
        if (! ($out instanceof DatabaseObject)) {
            throw new WrongClassException($class, __NAMESPACE__ . '\\DatabaseObjected');
        }
        return $out;
    }

    /**
     * Opens a database object.
     * The $id parameter can be an integer or an array, if it's an integer it will return an object with the value as
     * an auto-id, if it's an array, it will return an object which the keys match the provided values.
     *
     * @param string $class
     *            class of the object to be opened
     * @param int|array $id
     *            auto id of the object or an array containing an unique key
     * @param boolean $throwex
     *            if true, will throw an exception if data is not found
     * @return \mars\DatabaseObject the resulting object, null of not found
     */
    function open($class, $id, $throwex = false)
    {
        $out = $this->create($class);
        if (! $out->open($id, $throwex)) {
            return null;
        }
        self::$cache[$class][$out->id] = $out;
        return $out;
    }

    function classPaths()
    {
        return $this->classPaths;
    }

    /**
     *
     * @param string $name
     * @param mixed $default
     * @param mixed $min
     * @param mixed $max
     * @return mixed
     */
    function setting($name, $default = '', $min = null, $max = null, $dynamic = false)
    {
        if (! isset($this->settings[$name])) {
            if (is_bool($default) or is_int($default) or is_float($default)) {
                $val = var_export($default,1);
            } else {
                $val = $default = (string) $default;
            }
            if (!$dynamic){
                $this->query('Insert Into [[*settings]] ("data","value") ' . 'Values (' . $this->escape($name) . ',' . $this->escape($val) . ')');
            }
            return $this->settings[$name] = $default;
        }
        $value = $this->settings[$name];
        
        if (is_int($default)) {
            $value = (int) $value;
            if (($min !== null and $value < $min) or ($max !== null and $value > $max)) {
                return $default;
            }
            return $value;
        }
        if (is_float($default)) {
            $value = (float) $value;
            if (($min !== null and $value < $min) or ($max !== null and $value > $max)) {
                return $default;
            }
            return $value;
        }
        if (is_bool($default)) {
            if (is_bool($value) or is_numeric($value)) {
                return (bool) $value;
            }
            switch (StrToLower($value)) {
                case '':
                case 'no':
                case 'false':
                case 'off':
                case 'disabled':
                    return false;
            }
            return true;
        }
        
        $len = mb_strlen($value, 'utf-8');
        
        if (($min !== null and $len < $min) or ($max !== null and $len > $max)) {
            return (string) $default;
        }
        
        return $value;
    }

    function logError(\SimpleXMLElement $error)
    {
        $error = $error->saveXML();
        $error = $this->escape($error);
        $sql = 'Insert Into [[*errorLog]] ("content") Values (' . $error . ')';
        $this->query($sql);
        
        $maxentries = $this->setting('max-log-entries', 100, 5);
        $sql = 'Select "id" From [[*errorLog]] Order By "id" Desc Limit 1 Offset ' . $maxentries;
        $res = $this->query($sql)->fetch_Row();
        if ($res) {
            $res = $res[0];
            $sql = 'Delete From [[*errorLog]] Where "id" <= ' . $res;
            $this->query($sql);
        }
    }

    function escapeField($content)
    {
        return '"' . $this->conn->real_Escape_String($content) . '"';
    }

    function escape($content)
    {
        if ($content === null) {
            return 'NULL';
        }
        if (is_string($content)) {
            return '\'' . $this->conn->real_Escape_String($content) . '\'';
        }
        if (is_bool($content)) {
            return (int) $content;
        }
        if (is_float($content) or is_int($content)) {
            return (int) $content;
        }
    }

    function begin()
    {
        $this->conn->begin_transaction();
    }

    function commit()
    {
        $this->conn->commit();
    }

    function rollback()
    {
        $this->conn->rollback();
    }

    function insertId()
    {
        return $this->conn->insert_id;
    }
}
