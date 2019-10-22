<?php
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: *");
    header("Access-Control-Allow-Methods: *"); 

    include 'includes/db.php';
    include 'includes/Api.php';

    $absoluteUrl = 'http://'.$_SERVER['SERVER_NAME'].'/WS';
    echo 'http://'.$_SERVER['SERVER_NAME'];
    echo "\n";

if ($conn->connect_error) {
    echo $conn->connect_error;
}

    $db = new Database();
    $api = new Api();

// Регистрация
$api->post("/signup", function ($param, $data){
    global $db;

    $sql = "INSERT INTO `users` (`first_name`, `surname`, `phone`, `password`)
            VALUES ('".$data['first_name']."', '".$data['surname']."', '".$data['phone']."', '".$data['password']."')";
    $result = $db->query($sql);
        // true
    if ($result) {
        http_response_code(201);
        $response = array(
            "status" => http_response_code(),
            "id" => $db->insertID()
        );
        echo json_encode($response);    
    }
    echo "\nCall Back Signup";
});

// Авторизация
$api->post("/login", function ($param, $data){
    global $db;
    echo "Call Back Login\n";
    $sql = "SELECT * FROM `users` WHERE `phone` = '". $data['phone'] ."' AND `password` = '".$data['password']."'";
    $result = $db->query($sql);
    if (mysqli_num_rows($result) > 0)  {
        http_response_code(200);
        $response = array(
            "status" => http_response_code(),
            "token" => 'None'
        );
    }else{
        http_response_code(404);
        $response = array(
            "status" => http_response_code(),
            "login" => "Incorrect login or password"
        );
    }
    echo json_encode($response);    
});


// Загрузка фоторгафии 
$api->post("/photo", function ($param, $data){
    global $db;
    
         // File name
        $filename = $_FILES['file']['name']; // -> file obj - name
        // Valid file extensions
        $valid_extensions = array("jpg","jpeg","png");
        // File extension
        $extension = pathinfo($filename, PATHINFO_EXTENSION); // -> png
        echo $extension;

        // Check extension 
        if(in_array(strtolower($extension),$valid_extensions) ) {
            // Unical id name photo
            $fileNameNew = uniqid('').".". $extension;
            $fileDestination = "photos/" . $fileNameNew;
            // Upload file
        if(move_uploaded_file($_FILES['file']['tmp_name'], $fileDestination )){
            global $absoluteUrl;
                $sql = "INSERT INTO `photos` (`name`, `url`, `owner_id`, `users`)
                VALUES ('$fileNameNew', '$absoluteUrl/$fileDestination', '1', '1')";
                $result = $db->query($sql);
                echo "\n";
                echo $result;
            http_response_code(201);
            $response = array(
                "status" => http_response_code(),
                "name" => $filename,
                "res" => $result,
                "id" => $db->insertID()
            );
        }else{
            echo 0;
        }
        echo json_encode($response);
         $result = $db->query($sql);
         echo json_encode($result);
    }    
});

// Получение фоторгафий
$api->get("/photo", function ($param, $data){
    global $db;
    
    echo "\n";
    echo "Call Photo GET";
    if (count($param) === 0) {
        $sql = "SELECT * FROM `photos`";
    }else{
        $sql = "SELECT * FROM `photos` WHERE `id` = '". $param['id'] ."'"; 
    }
    echo "\n";
    $result = $db->query($sql);
    $users = array();
    while ($row = $result->fetch_assoc()){
        array_push($users, $row);
    }
    echo "\n";
    echo json_encode($users);
    echo "\n";
});

$api->get("/user", function ($param, $data){
    global $db;

    $sql = "SELECT * FROM `users` WHERE `first_name` LIKE '".$param['first_name']."'"; 
     $result = $db->query($sql);
     echo json_encode($result);
});

//Изменение фоторгафии
// $api->post("/photo", function ($param, $data){
//     global $db;
//     if($param['id']){
//         // var_dump($param);
//         echo $param['id'];
//         $sql = "SELECT * FROM `photos` WHERE `name` = 'asdasd'";
        
//         $result = $db->query($sql);
//         echo json_encode($result);
//     }
// });




// Info
    // echo $_SERVER["REQUEST_URI"] -> /WS/index.php/signup;
    // echo $_SERVER['HTTP_HOST'] -> localhost;
    // echo basename($_SERVER['REQUEST_URI']) -> signup
    //$uurl = 'www.mysite.com/category/subcategory?myqueryhash';
    //echo parse_url($uurl, PHP_URL_QUERY); -> myqueryhash
?>