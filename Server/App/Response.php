<?php
namespace Server\App;
class Response
{	
	private $_status = array(
        200 => '200 OK',
        201 => '201 Created',
        304 => '304 Not Modified',
        400 => '400 Bad Request',
        400 => '401 Unauthorized',
        422 => 'Unprocessable Entity',
        500 => '500 Internal Server Error'
    );

    private $_server = array();

    private $_post = array();

    public function __construct($server)
    {
    	$this->_server = $server;

    	$this->_auth();

    	$this->_post = json_decode(file_get_contents("php://input"), true) ? : [];	    	
    }

    public function getPost()
    {
    	return $this->_post;
    }

    public function getMethod()
    {
    	return $this->_server["REQUEST_METHOD"];
    }

    private function _auth()
    {
    	if(($this->_server["PHP_AUTH_USER"] != API_USER) || ($this->_server["PHP_AUTH_PW"] != API_PASS)){
	    	header('WWW-Authenticate: Basic realm="Sistema de Tarefas"');
		    header('HTTP/1.0 401 Unauthorized');
		    die('Acesso negado.');
		}
    }

    public function setResponse($code = 200, $message = null)
	{
		try{			
		    // clear the old headers
		    header_remove();
		    // set the actual code
		    http_response_code($code);
		    // set the header to make sure cache is forced
		    header("Cache-Control: no-transform,public,max-age=300,s-maxage=900");
		    // treat this as json
		    header('Content-Type: application/json');
		    
		    header('Status: '.$this->_status[$code]);
		    
		    die(json_encode($message));

		}catch(Exception $e){
			return false;
		}
	} 
}