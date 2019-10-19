<?php
// $conn = new mysqli('localhost', 'root', '', 'wstest');
// echo count($_GET);
// echo count($_POST);
// if(isset($_GET['id'])){
// 	echo $_SERVER['PATH_INFO'];
// }

// const url = "http://localhost/WS/index.php";
// echo "\n";
// echo url;
// echo "\n";
$request = $_SERVER['PATH_INFO']; 
// echo $request;
// echo "  ---  ";
// echo gettype($request);
// echo "\n";
// echo "\n";
// echo "\n";

class Api {
	// Get
	function get($action, $call) {
		if ($_SERVER['REQUEST_METHOD'] === 'GET') {
			if ($_SERVER['PATH_INFO'] == $action) {
        		if (is_callable($call)){
        			call_user_func($call, $_GET, $this->GetContents($_POST));
        		}
			}
		}
    }
    // Post
    function post($action, $call) {
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			if ($_SERVER['PATH_INFO'] == $action) {
        		if (is_callable($call)){
        			call_user_func($call, $_GET, $this->GetContents($_POST));
        		}
			}
		}
    }
    // Delete
    function delete($action, $call) {
		if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
			if ($_SERVER['PATH_INFO'] == $action) {
        		if (is_callable($call)){
        			call_user_func($call, $_GET, $this->GetContents($_POST));
        		}
			}
		}
    }

    function GetContents($payload){
        if ($payload == NULL) {
            $payload = json_decode(file_get_contents("php://input"), true);
        }
        return $payload;
    }
}
?>