<?php
namespace mars;

class DatabaseException extends \exception {
	
	
	function __construct(\mysqli $conn,$sql){
		parent::__construct($conn->error,500);
		$this->sql = $sql;
		$this->conn = $conn;
		$this->dbErrNo = $conn->errno;
	}
	
	private $sql;
	function getSql(){
		return $this->sql;
	}
	
	private $conn;
	function getConn(){
		return $this->conn;
	}
	
	private $dbErrNo;
	function getDbErrNo(){
	    return $this->dbErrNo;
	}
}