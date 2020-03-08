<?php

require 'vendor/autoload.php';
include 'config.php';
$app = new Slim\App(["settings" => $config]);
//Handle Dependencies
$container = $app->getContainer();

$container['db'] = function ($c) {
   
   try{
       $db = $c['settings']['db'];
       $options  = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
       PDO::ATTR_DEFAULT_FETCH_MODE                      => PDO::FETCH_ASSOC,
       );
       $pdo = new PDO("mysql:host=" . $db['servername'] . ";dbname=" . $db['dbname'],
       $db['username'], $db['password'],$options);
	   //echo "MySQL DB Connection Status: Success";
       return $pdo;
   }
   catch(\Exception $ex){
       return $ex->getMessage();
   }
   
};
/*
Now, expand the index.php file by writing a bunch of code to cover Creating, Reading, Updating, and Deleting (CRUD) records in your database. 
First, add the following code block for creating a new user. This is the code that gets triggered when your API's endpoint receives an API 
call with the HTTP POST verb in it (directed at the API's "user" resource):
*/

$app->post('/user', function ($request, $response) {
   
   try{
       $con = $this->db;
       $sql = "INSERT INTO `users`(`username`, `email`,`password`) VALUES (:username,:email,:password)";
       $pre  = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
       $values = array(
       ':username' => $request->getParam('username'),
       ':email' => $request->getParam('email'),
		//Using hash for password encryption
       'password' => password_hash($request->getParam('password'),PASSWORD_DEFAULT)
       );
       $result = $pre->execute($values);
       return $response->withJson(array('status' => 'User Created'),200);
       
   }
   catch(\Exception $ex){
       return $response->withJson(array('error' => $ex->getMessage()),422);
	   //return $response->withJson(array('error' => 'Failed'),422);
   }
   
});

/*
The above code receives the name, email, and password parameters from the HTTP POST request. When you hash your password, the code saves all the 
information to the database. Because you configured the new database to auto-increment the id for each new user in your table originally (see above),
 the userid is generated automatically. One thing to note about this code is that it involves no error trapping. In a typical scenario where an end 
 user might be registering his or her username and password, the service would double check to make sure that the username isn't already in use.

 Next, create the code for querying the database. This code will be triggered when an HTTP GET request is sent to the API's endpoint. The code for this request is:
*/
$app->get('/user/{id}', function ($request,$response) {
   try{
       $id     = $request->getAttribute('id');
       $con = $this->db;
       $sql = "SELECT * FROM users WHERE id = :id";
       $pre  = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
       $values = array(
       ':id' => $id);
       $pre->execute($values);
       $result = $pre->fetch();
       if($result){ 
		   return $response->withJson(array('status' => 'true','result'=> $result),200);
       }else{
		   return $response->withJson(array('status' => 'User Not Found'),422);
       }
      
   }
   catch(\Exception $ex){
       return $response->withJson(array('error' => $ex->getMessage()),422);
   }
 
});

$app->get('/users', function ($request,$response) {
   try{
	   print(response_raw.headers);
       $con = $this->db;
       $sql = "SELECT * FROM users";
       $result = null;
       foreach ($con->query($sql) as $row) {
           $result[] = $row;
       }
       if($result){
           return $response->withJson(array('status' => 'true','result'=>$result),200);
	   }else{
           return $response->withJson(array('status' => 'Users Not Found'),422);
       }
              
   }
   catch(\Exception $ex){
       return $response->withJson(array('error' => $ex->getMessage()),422);
   }
   
});
$app->run();