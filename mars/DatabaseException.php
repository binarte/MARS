<?php
namespace mars;

class DatabaseException extends \exception {
	
	
	function __construct(\mysqli $conn,$sql){
		parent::__construct($conn->error,$conn->errno);
		$this->sql = $sql;
		$this->conn = $conn;
	}
	
	private $sql;
	function getSql(){
		return $this->sql;
	}
	
	private $conn;
	function getConn(){
		return $this->conn;
	}
}