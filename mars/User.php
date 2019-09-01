<?php
namespace mars;

class User extends DatabaseObject
{

    protected static $fieldInfo = [
        'username' => [
            'type' => self::T_Text,
            'maxlength' => 64
        ],
        'auth' => [
            'type' => self::T_Binary,
            'length' => 16,
            'read-only' => true,
            'nullable' => true
        ]
    ];

    protected static $indexes = [
        'username' => [
            'username'
        ]
    ];

    static function login($method, $digest, Database $db = null)
    {
        preg_match_all('@([^,\s]*?)=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $digest, $matches, PREG_SET_ORDER);
        $params = [];
        foreach ($matches as $m) {
            if (isset($params[$m[1]])) {
                throw new LoginException(null, LoginException::InvalidAuth);
            }
            $params[$m[1]] = $m[3] ? $m[3] : $m[4];
        }
        if (! isset($params['username'])) {
            throw new LoginException(null, LoginException::InvalidAuth);
        }
        
        if ($db === null) {
            $db = self::$defaultDb;
        }
        $user = new User($db);
        if (! $user->open([
            'username' => $params['username']
        ])) {
            throw new LoginException(null, LoginException::BadCredentials);
        }
        $user->auth($method, $params, true);
        return $user;
    }

    protected $username;

    protected $auth;

    protected function set_password($pass)
    {
        $pass = (string) $pass;
        if (empty($pass)) {
            throw new WeakPasswordException(WeakPasswordException::Empty);
        }
        $len = mb_strlen($pass, 'UTF-8');
        $l = $this->_db->setting('password-min-length', 8, 6);
        if ($len < $l) {
            throw new WeakPasswordException(WeakPasswordException::TooShort, $l);
        }
        if (is_numeric($pass)) {
            throw new WeakPasswordException(WeakPasswordException::Numeric);
        }
        $chrs = [];
        for ($x = 0; $x < $len; $x ++) {
            $chrs[mb_substr($pass, $x, 1)] = 1;
        }
        $l = $this->_db->setting('password-min-chars', 6, 4);
        if (count($chrs) < $l) {
            throw new WeakPasswordException(WeakPasswordException::TooFewChars, $l);
        }
        
        $this->auth = md5($this->username . ':' . $this->_db->setting('auth-realm', 'MARS') . ':' . $pass, false);
    }

    function auth($method, $data, $throwEx = false)
    {
        if ($this->username == '#guest') {
            return true;
        }
        $params = [
            'nonce',
            'nc',
            'cnonce',
            'qop',
            'username',
            'uri',
            'response'
        ];
        foreach ($params as $p) {
            if (! isset($data[$p])) {
                throw new LoginException($this, LoginException::InvalidAuth);
            }
        }
        foreach (array_keys($data) as $p) {
            if (! in_array($p, $params)) {
                throw new LoginException($this, LoginException::InvalidAuth);
            }
        }
        if ($this->username != $data['username']) {
            if ($throwEx) {
                throw new LoginException($this, LoginException::BadCredentials);
            }
            return false;
        }
        
        $A2 = md5($method . ':' . $data['uri']);
        $valid_response = md5($this->auth . ':' . $data['nonce'] . ':' . $data['nc'] . ':' . $data['cnonce'] . ':' . $data['qop'] . ':' . $A2);
        if ($data['response'] == $valid_response) {
            return true;
        }
        if ($throwEx) {
            throw new LoginException($this, LoginException::BadCredentials);
        }
        return false;
    }

    protected $_permissions = [];

    function permissions($param)
    {
        if (! $this->saved) {
            return false;
        }
        if ($this->username == '#root') {
            return 0x7FFFFFFF;
        }
        
        if (empty($this->_permissions)) {
            $sql = 'Select n."name", p."value" From [[' . __CLASS__ . '-permissions]] p Join [[*permission]] n on n."id" = p."permission" Where n."user" = ' . $this->id;
            $res = $this->_db->query($sql);
            while ($row = $res->fetch_assoc()) {
                $this->_permissions[$row['name']] = (int) $row['value'];
            }
        }
        
        if (! isset($this->_permissions[$param])) {
            return 0;
        }
        return $this->_permissions[$param];
    }

    function grant($param, $value)
    {
        if (! $this->saved) {
            return false;
        }
        if ($this->username == '#root') {
            return true;
        }
        
        if (! (System::sessionUser()->permissions('grant') & self::P_Create)) {
            throw new AccessDeniedException('grant', self::P_Create);
        }
        
        $ps = $this->_db->escape($param);
        $perm = 'Select "id" From [[*permission]] Where "name" = ' . $ps;
        $pid = $this->_db->query($sql)->fetch_row();
        if (! $pid) {
            $sql = 'Insert Into [[*permission]] ("name") Values (' . $ps . ')';
            $this->_db->query($sql);
            $pid = $this->_db->lastId();
        }
        $value = (int) $value;
        $value = $value & 0xFFFF;
        
        $sql = 'Update [[' . __CLASS__ . '-permissions]] Set "value" = ' . $value . ' Where "user" = ' . $this->id . ' And "permission" = ' . $pid;
        $this->_db->query($sql);
        if (! $this->_db->affectedRows()) {
            $sql = 'Insert Into [[' . __CLASS__ . '-permissions]] ("user","permission","value") Values (' . $this->id . ',' . $pid . ',' . $permissions . ')';
            $this->_db->query($sql);
        }
        return true;
    }

    protected function createOtherTables()
    {
        $this->_db->createTable('*permission', [
            'name' => [
                'type' => self::T_Text,
                'maxlength' => 64
            ]
        ], [
            [
                'name'
            ]
        ], false, 2);
        $this->_db->createRelTable(__CLASS__, '*permission', 4, 2, 'permissions', [
            'value' => [
                'type' => self::T_Integer,
                'min' => 0,
                'max' => 0xFF
            ]
        ]);
    }
}