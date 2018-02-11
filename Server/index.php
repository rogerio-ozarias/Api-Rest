<?php
try {

	if ($_GET["uri"]) {
		list($uuid) = explode("/", $_GET["uri"]);
	}

	//config db
	require "config.php";

	// autoload
	require __DIR__ . "/../library/autoload.php";	

	//db connection
	$dbConnect =  new Zend_Db_Adapter_Pdo_Mysql(array(
	    'host'     => DB_HOST,
	    'username' => DB_USER,
	    'password' => DB_PASS,
	    'dbname'   => DB_NAME
	));	

	// class response
	$response = new Server\App\Response($_SERVER);

	$service = new Server\App\TaskServer($dbConnect, $uuid);

	$post = $response->getPost();

	$method = $response->getMethod();

	switch (strtoupper($method)) {
		case "GET":
			if ($uuid) {
				$mesage = $service->getTask();
				$code = 200;
			} else {
				$mesage = $service->getList();
				$code = 200;
			}
		break;
		case "PUT":
			$mesage = $service->put($post);			
			$code = 201;		
		break;
		case "POST":
			$mesage = $service->post($post);
			$code = 201;			
		break;
		case "DELETE":
			$mesage = $service->delete();	
			$code = 200;		
		break;
		default:
			$code = 400;
	}
} catch (Exception $e) {
	$code = 304;
	$mesage = $service->error;
}

$response->setResponse($code, $mesage);
