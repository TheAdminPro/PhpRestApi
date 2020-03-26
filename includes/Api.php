<?php
class Api{

	// GET
	function get($path, $callback){
		if($path == $_SERVER['PATH_INFO'] && $_SERVER['REQUEST_METHOD'] === "GET"){
			if(is_callable($callback)) {
				call_user_func($callback, $_GET, $this->GetContents($_POST));
			}
		}
	}
	// POST
	function post($path, $callback){
		if($path == $_SERVER['PATH_INFO'] && $_SERVER['REQUEST_METHOD'] === "POST"){
			if(is_callable($callback)) {
				call_user_func($callback, $_GET, $this->GetContents($_POST));
			}
		}
	}
	// PATCH
	function patch($path, $callback){
		if($path == $_SERVER['PATH_INFO'] && $_SERVER['REQUEST_METHOD'] === "PATCH"){
			if(is_callable($callback)) {
				call_user_func($callback, $_GET, $this->GetContents($_POST));
			}
		}
	}
	// DELETE
	function delete($path, $callback){
		if($path == $_SERVER['PATH_INFO'] && $_SERVER['REQUEST_METHOD'] === "DELETE"){
			if(is_callable($callback)) {
				call_user_func($callback, $_GET, $this->GetContents($_POST));
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