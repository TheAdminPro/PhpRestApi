<?php 
// class DB {
// 	public $servername;	
// 	public $username;
// 	public $password;
// 	public	$dbname;

// 	public function con(){
// 		$this->servername = 'localhost';
// 		$this->username = 'root';
// 		$this->password = '';
// 		$this->dbname = 'wstest';

// 		$connect = new mysqli($this->servername,
// 		 					  $this->username,
// 		 					  $this->password,
// 		 					  $this->dbname);
// 		return $connect;
// 	}

// }


class Database{
  private $mysqli;
 
  public function __construct() {
    $this->mysqli = new mysqli('localhost', 'root', '', 'wstest');
    // $this->mysqli->query("SET NAMES 'utf8'");
  }
 
  function query($query) {
    return $this->mysqli->query($query);
  }
  function insertID(){
  	return $this->mysqli->insert_id;
  }
}
?>