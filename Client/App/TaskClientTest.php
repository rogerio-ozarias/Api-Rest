<?php

require '../config.php';

require 'Request.php';

require 'TaskClient.php';

class TaskClientTest extends PHPUnit_Framework_TestCase
{		

	public function testEmptyList()
	{
		$headers = array(
		    'Accept: application/json',
		    'Content-Type: application/json',    
		    'Authorization: Basic '.base64_encode(API_USER.":".API_PASS)
		);
		$request = new Client\App\Request($headers);

		// class webservice
		$service = new Client\App\TaskClient($request);
		$array = $service->listTask();
		
		// list
		$msgTest = "Wow. You have nothing else to do. Enjoy the rest of your day!";
		$return = $service->listTask();				
		$this->assertEquals($msgTest, $return);
	}


	public function testCreateUpdate()
	{
		$headers = array(
		    'Accept: application/json',
		    'Content-Type: application/json',    
		    'Authorization: Basic '.base64_encode(API_USER.":".API_PASS)
		);
		$request = new Client\App\Request($headers);

		$service = new Client\App\TaskClient($request);

		// faz o teste de inserção
		$post = array(
			"type" => "shopping",
			"content" => "Tarefa alterada",
			"sort_order" => 1,
			"done" => 0
		);	
		$array = $service->create($post);
		$uuid = $array["uuid"];		
		$this->assertNotEmpty($array);	
		
		$post = array(
			"type" => "shopping",
			"content" => "Tarefa alterada",
			"sort_order" => 3,
			"done" => 1
		);
		$array = $service->update($uuid, $post);				
		$this->assertNotEmpty($array);	

		// testa se a ordem foi atualizada
		$this->assertEquals(3, $array["sort_order"]);	

		// testa se foi finalizada
		$this->assertEquals(1, $array["done"]);	

		// testa a consulta dos detalhes da tarefa
		$array = $service->getTask($uuid);			
		$this->assertNotEmpty($array);
		
		// testa a lista de tarefas
		$array = $service->listTask();		
		$this->assertNotEmpty($array);

		// testa a remoção da tarefa
		$return = $service->delete($uuid);				
		$this->assertEquals(true, $return);	
	}

	public function testeUpdateNotExists()
	{		
		$headers = array(
		    'Accept: application/json',
		    'Content-Type: application/json',    
		    'Authorization: Basic '.base64_encode(API_USER.":".API_PASS)
		);
		$request = new Client\App\Request($headers);

		$service = new Client\App\TaskClient($request);

		$post = array(
			"type" => "shopping",
			"content" => "Tarefa alterada",
			"sort_order" => 3,
			"done" => 1
		);
		// testa um uptade em que o uuid não existe
		$msgTest = "Are you a hacker or something? The task you were trying to edit doesn't exist.";
		$return = $service->update("010", $post);				
		$this->assertEquals($msgTest, $return);	
	}

	public function testeDeleteNotExists()
	{		
		$headers = array(
		    'Accept: application/json',
		    'Content-Type: application/json',    
		    'Authorization: Basic '.base64_encode(API_USER.":".API_PASS)
		);
		$request = new Client\App\Request($headers);
		
		$service = new Client\App\TaskClient($request);

		$post = array(
			"type" => "shopping",
			"content" => "Tarefa alterada",
			"sort_order" => 3,
			"done" => 1
		);
		// testa a remoção de uma tarefa que não existe
		$msgTest = "Good news! The task you were trying to delete didn't even exist.";
		$return = $service->delete("010");				
		$this->assertEquals($msgTest, $return);	
	}	
}
