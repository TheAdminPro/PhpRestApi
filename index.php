<?php 
header("Access-Control-Allow-Origin: *");	
header("Access-Control-Allow-Methods: *");	
header("Access-Control-Allow-Headers: *");
	
	// Include
	include 'includes/Api.php';
	// Init
	$api = new Api();

	$pdo = new PDO('mysql:host=localhost;dbname=photo','root','');
	$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

	$absoluteURL = 'http://'.$_SERVER['SERVER_NAME'].'/html';


	// Signup Logic
	// Handle for '/signup' path
	$api->post('/signup', function ($param, $data){
		global $pdo;
		validate($data); // Validate users data 

		// Select row with this 'phone'
		$sql = "SELECT * FROM `users` WHERE `phone` = :phone";
		$stmt = $pdo->prepare($sql);
		$stmt->execute([':phone' => $data['phone']]);

		// Check on has row with this 'phone'
		if (!$stmt->rowCount()) {
			$sql = "INSERT INTO `users` (`first_name`, `surname`, `phone`, `password`) 
							VALUES (:first_name, :surname, :phone, :password)";
			$stmt = $pdo->prepare($sql);
			$stmt->execute([':first_name' => $data['first_name'],
											':surname' => $data['surname'],
											':phone' => $data['phone'],
											':password' => $data['password']]);
			http_response_code(201);
			$response = (object) [
				'code' => http_response_code(),
				'content' => ['id' => $pdo->lastInsertId()]
			];
		}else{
			http_response_code(422);
			$response = (object) [
				'code' => http_response_code(),
				'content' => ['phone' => 'This phone is used']
			];
		}
		echo json_encode($response);
	});



	// login Logic
	// Handle for '/login' path
	$api->post('/login', function($param, $data)	{
		global $pdo;
		validate($data); // Validate users data

		// Select row with this 'phone' && 'password'
		$sql = "SELECT * FROM `users` WHERE `phone` = :phone && `password` = :password";
		$stmt = $pdo->prepare($sql);
		$stmt->execute([':phone' => $data['phone'],
										':password' => $data['password']]);

		// Check on has row with this 'phone' && 'password'
		if ($stmt->rowCount()) {
			$user = $stmt->fetch();

			// Generate Token
			$token = bin2hex(random_bytes(20));

			// Update user token
			$sql = "UPDATE `users` SET `token` = :token WHERE `id_user` = :id_user";
			$stmt = $pdo->prepare($sql);
			$stmt->execute([':token' => $token,
											':id_user' => $user->id_user]);

			http_response_code(200);

			$response = (object) [
				'code' => http_response_code(),
				'content' => ['token' => $token]
			];
		}
		else{
			http_response_code(404);
			$response = (object) [
				'code' => http_response_code(),
				'content' => ['login' => 'Incorrect login or password']
			];	
		}
		echo json_encode($response);
	});


	// Logout Logic
	// Handle for '/logout' path
	$api->post('/logout', function($param, $data){
		global $pdo;
		$user = authMiddleware();

		$sql = "UPDATE `users` SET `token` = :token WHERE `id_user` = :id_user";
		$stmt = $pdo->prepare($sql);
		$stmt->execute([':token' => '',
										':id_user' => $user->id_user]);

		if ($stmt->rowCount()) {
			http_response_code(200);
			$response = (object) [
				'code' => http_response_code()
			];	
		}
	});


	// Load Images Logic
	// Handle for '/photo' path
	$api->post('/photo', function($param, $data){
		global $pdo;
    global $absoluteURL;
		// validate($_FILES['photo']);

		$user = authMiddleware();

		$filename = basename($_FILES['photo']['name']);
		$tmp_name = $_FILES['photo']['tmp_name'];


		$valid_extensions = array("jpg","jpeg","png");
		$extension = pathinfo($filename, PATHINFO_EXTENSION); // -> .png|jpeg|...

    if(in_array(strtolower($extension), $valid_extensions)){
  		$filenameNew = uniqid('', true).".".$extension;
  		$fileDestination = "photos/".$filenameNew;
  		
  		$loadResult = loadImage($tmp_name, $fileDestination);

  		if ($loadResult) {
  			$imgURL = "$absoluteURL/$fileDestination";

  			$sql = "INSERT INTO `photo` (`name`, `url`, `owner_id`) 
  							VALUES (:name, :url, :owner_id)";
  			$stmt = $pdo->prepare($sql);
  			$stmt->execute([':name' => $filename,
  											':url' => $imgURL,
  											':owner_id' => $user->id_user]);
  			
  			if($stmt->rowCount()){
    			http_response_code(201);
    			$response = (object) [
    				'code' => http_response_code(),
    				'content' => ["id" => $pdo->lastInsertId(),
    											"name" => $filename,
    											"url" => $imgURL]
    			];
  			}
  		}
    }
	  echo json_encode($response);
	});


	// Get Photo|s
	$api->get("/photo", function ($param, $data){
		global $pdo;
		$user = authMiddleware();	

		$sql = "SELECT * FROM `photo`";
		if(!empty($param)) $sql .= " WHERE `id_photo` = '".$param['id']."'";
 
		$stmt = $pdo->query($sql);
		$photos = [];
		while ($photo = $stmt->fetch()) {
			$sql = "SELECT `user_id` FROM `share` WHERE `photo_id` = '".$row->id_photo."'";
			$stmt2 = $pdo->query($sql);
			$photo->users = [];

			while ($r = $stmt2->fetch()) {
				array_push($photo->users, $r->user_id);
			}
			array_push($photos, $photo);
		}
		http_response_code(201);
		$response = (object) [
			"code" => http_response_code(),
			"content" => $photos
		];


		echo json_encode($response);
	});


	// Delete photo
	$api->delete("/photo", function ($param, $data){
		global $pdo;
		$user = authMiddleware();

		$sql = "SELECT * FROM `photo` WHERE `id_photo` = :id_photo AND `owner_id` = :owner_id";
		$stmt = $pdo->prepare($sql);
		$stmt->execute([':id_photo' => $param['id'],
										':owner_id' => $user->id_user]);
		$photo = $stmt->fetch();
		if(!empty($photo)){
			$path = stristr($photo->url, 'photo');
			unlink($path);
			$stmt = $pdo->query("DELETE FROM `photo` WHERE `id_photo` = '".$photo->id_photo."'");

			http_response_code(201);
			$response = (object) [
				'code' => http_response_code()	
			];
		}
		else{
			http_response_code(403);
			$response = (object) [
				'code' => http_response_code()	
			];
		}
		// Response
		echo json_encode($response);
	});


	// Search users
	$api->get("/user", function ($param, $data) {
		global $pdo;
		$user = authMiddleware();

		$search = "%".$param['search']."%";
		echo $search;
		echo "\n";
		$sql = "SELECT * FROM `users` 
						WHERE `first_name` LIKE :search ||
						 			`surname` LIKE :search || 
						 			`phone` LIKE :search";

		$stmt = $pdo->prepare($sql);
		$stmt->execute(['search' => $search]);
		var_dump($stmt->fetchAll());						

	});


	// Share photo
	$api->post("/user", function($param, $data){
		global $pdo;
		$user = authMiddleware();
		echo "string";


	});	





	// Some func
	function loadImage($tmp, $destination){
    return move_uploaded_file($tmp, $destination);
	}



	function authMiddleware(){
		global $pdo;
		$userToken = getBearerToken();

		$sql = "SELECT * FROM `users` WHERE `token` = :token";
		$stmt = $pdo->prepare($sql);
		$stmt->execute([':token' => $userToken]);
		$user = $stmt->fetch();
		// var_dump($user);
		if(empty($user)){
			http_response_code(403);
			$response = (object) [
				'code' => http_response_code(),
				'content' => ['message' => "You need authorization"]
			];
			die(json_encode($response));	
		}
		return $user;

	}
	


	function getAuthorizationHeader(){
    $headers = null; 
    if (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();

        if (isset($requestHeaders['Authorization'])) {
            $headers = trim($requestHeaders['Authorization']);
        }
    }
    return $headers;
  }

	 // get access token from header
	function getBearerToken() {
    $headers = getAuthorizationHeader();
    // HEADER: Get the access token from the header
    if (!empty($headers)) {
        if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            return $matches[1];
        }
    }
    return null;
	}




	// Validate
	function validate($data){
		$error = [];
		foreach ($data as $key => $value) {
			if($value === ''){
				$error[$key] = "Enter value";
			}else{
				if($key === 'phone' && strlen($value) != 11){
					$error[$key] = "Enter 11 numbers";
				} 
			}		
		}
		if (count($error)) {
			http_response_code(422);
			$response = (object) [
				'code' => http_response_code(),
				'content' => $error
			];
			die(json_encode($response));
		}
	}
?>