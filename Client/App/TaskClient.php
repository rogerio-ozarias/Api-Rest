<?php
namespace Client\App;

class TaskClient
{	
	private $_request = null;

	public function __construct($request)
	{		
		$this->_request = $request;		
	}

	public function getTask($id)
	{
		return $this->_request->setRequest(API_URL.$id, "GET")
			->getResponse();		
	}

	public function listTask()
	{
		return $this->_request->setRequest(API_URL, "GET")
			->getResponse();		
	}

	public function create(Array $post)
	{
		return $this->_request->setRequest(API_URL, "POST", $post)
			->getResponse();		
	}

	public function update($id, Array $post)
	{
		return $this->_request->setRequest(API_URL.$id, "PUT", $post)
			->getResponse();
	}

	public function delete($id)
	{
		return $this->_request->setRequest(API_URL.$id, "DELETE")
			->getResponse();
	}
}
