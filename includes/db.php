<?php 
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