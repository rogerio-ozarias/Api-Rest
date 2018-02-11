<?php
// autoload
include __DIR__ . "/../library/autoload.php";	

//config db
require "config.php";


$headers = array(
    'Accept: application/json',
    'Content-Type: application/json',    
    'Authorization: Basic '.base64_encode(API_USER.":".API_PASS)
);
$request = new Client\App\Request($headers);

// class webservice
$service = new Client\App\TaskClient($request);


$return = $service->listTask();
echo "<pre>return:"; print_r($return); echo "</pre>";
