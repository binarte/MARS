<?php
namespace mars;

class User extends DatabaseObject {
	static protected $fieldInfo = [
		'username' => [
			'type' => self::T_Text,
			'maxlength' => 64,
		],
		'auth' => [
			'type' => self::T_Binary,
			'length' => 16,
			'read-only' => true,
		]
	];
	static protected $indexes = [
		'username' => [
			'username'
		]		
	];
	
	protected $username;
	protected $auth;
	
	protected function set_password($pass){
		$pass = (string) $pass;
		if (empty($pass) ){
			throw new WeakPasswordException(WeakPasswordException::Empty);
		}
		$len = mb_strlen($pass,'UTF-8');
		$l = $this->_db->setting('password-min-length',8,6);
		if ($len < $l) {
			throw new WeakPasswordException(WeakPasswordException::TooShort,$l);
		}
		if (is_numeric($pass) ) {
			throw new WeakPasswordException(WeakPasswordException::Numeric);
		}
		$chrs = [];
		for($x = 0; $x < $len; $x++){
			$chrs[mb_substr($pass,$x,1)] = 1;
		}
		$l = $this->_db->setting('password-min-chars',6,4);
		if(count($chrs) < $l ) {
			throw new WeakPasswordException(WeakPasswordException::TooFewChars,$l);
		}
		
		$this->auth = md5(
			$this->username.':'.
			$this->_db->setting('auth-realm','MARS').':'.
			$pass
		,false);
	}
}