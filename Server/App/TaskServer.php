<?php
namespace Server\App;

class TaskServer
{
	private $_uuid;

	private $_task = array();

	public $error = array();

	private $_taskType = array(
		"shopping",
		"work"
	);

	private $_dbConnect;

	/**
	* @param string $uuid
	*/
	public function __construct($dbConnect, $uuid = null)	
	{
		$this->_dbConnect = $dbConnect;	

		if ($uuid !== null) {
			// faz o carregamento da tarefa
			$this->_loadTask($uuid);

			// se existir, seta variavel
			if ($this->_task["uuid"]) {
				$this->_uuid = $this->_task["uuid"];					
			}
		}		
	}

	private function _loadTask($uuid)
	{				
		// consulta a tarefa no banco
		$sql = $this->_dbConnect->select()->from("task")->where("uuid=?", $uuid);
		$this->_task =  $this->_dbConnect->fetchRow($sql);													
	}

	public function getTask()
	{
		return $this->_task;
	}

	public function getList()
	{				
		// consulta a tarefa no banco
		$sql = $this->_dbConnect->select()->from("task")->order("sort_order");
		$task =  $this->_dbConnect->fetchAll($sql);		

		if (!count($task)) {
			return "Wow. You have nothing else to do. Enjoy the rest of your day!";
		}											

		// returna a lista de tarefas
		return $task;				
	}
	

	/**
	* Create Task
	* @param Array $task
	* @return boolean
	*/
	public function post(Array $task)
	{		
		if (!$task["content"]) {
			return "Bad move! Try removing the task instead of deleting its content.";			
		}
		if (!in_array($task["type"], $this->_taskType)) {
			return "The task type you provided is not supported. You can only use shopping or work.";
		}
		if (!$this->_reorder($task["sort_order"])) {
			return "Não foi possivel fazer a reordenação.";
		}		
				
		$uuid = $this->_criateUuid();	

		// insert
		$insertTask = array(
			"uuid" => $uuid,
			"type" => $task["type"],
			"content" => $task["content"],
			"sort_order" => $task["sort_order"],
			"done" => $task["done"],
			"date_created" => date("Y-m-d")
		);
		$this->_dbConnect->insert("task", $insertTask);

		$this->_loadTask($uuid);

		return $this->_task;					
	}

	/**
	* Create Task
	* @param Array $task
	* @return boolean
	*/
	public function put(Array $task)
	{		
		if (!$this->_uuid) {
			return "Are you a hacker or something? The task you were trying to edit doesn't exist.";
		}
		
		//verifica se esta atualizando a ordem
		if ($this->_task["sort_order"] !== $task["sort_order"]) {
			//verifica se a ordem ja existe para outra tarefa, se existir, faz a reordenação
			if(!$this->_reorder($task["sort_order"], $this->_uuid))	
				return "Não foi possivel fazer a reordenação.";
		}		

		// atualiza tarefa
		$updateTask = array(
			"type" => $task["type"],
			"content" => $task["content"],
			"sort_order" => $task["sort_order"],
			"done" => $task["done"]				
		);		
		$where = $this->_dbConnect->quoteInto("uuid=?", $this->_uuid);
		$this->_dbConnect->update("task", $updateTask, $where);

		$this->_loadTask($this->_uuid);

		return $this->_task;
	}

	/**
	* Delete Task
	* @param Array $task
	* @return boolean
	*/
	public function delete()
	{		
		if(!$this->_uuid) { 
			return "Good news! The task you were trying to delete didn't even exist.";			
		}	
		
		$where = $this->_dbConnect->quoteInto("uuid=?", $this->_uuid);
		$this->_dbConnect->delete("task", $where);
			
		return true;						
	}

	private function _reorder($orderTest, $uuid = null)
	{		
		try {
			// consulta a tarefa no banco
			$sql = $this->_dbConnect->select()->from("task")
				->where("sort_order=?", $orderTest)
				->where("done=?", 0);
			
			if ($uuid) {
				$sql = $sql->where("uuid<>?", $uuid);
			}

			$existOrder =  $this->_dbConnect->fetchRow($sql);	

			if ($existOrder["uuid"]) {
				$orderTest++;			
				# atualiza ordem para +1
				$updateTask = array(
					"sort_order" => $orderTest
				);		
				$where = $this->_dbConnect->quoteInto("uuid=?", $existOrder["uuid"]);
				$this->_dbConnect->update("task", $updateTask, $where);	

				return $this->_reorder($orderTest, $existOrder["uuid"]);			
			}	

			return true;	
		} catch (Exception $e) {
			return false;
		}						
	}
	
	/**
	* Create uuid Task
	* @return string
	*/
	private function _criateUuid()
	{
		$uuid = array(
			'time_low'  => 0,
			'time_mid'  => 0,
			'time_hi'  => 0,
			'clock_seq_hi' => 0,
			'clock_seq_low' => 0,
			'node'   => array()
		);

		$uuid['time_low'] = mt_rand(0, 0xffff) + (mt_rand(0, 0xffff) << 16);
		$uuid['time_mid'] = mt_rand(0, 0xffff);
		$uuid['time_hi'] = (4 << 12) | (mt_rand(0, 0x1000));
		$uuid['clock_seq_hi'] = (1 << 7) | (mt_rand(0, 128));
		$uuid['clock_seq_low'] = mt_rand(0, 255);

		for ($i = 0; $i < 6; $i++) {
			$uuid['node'][$i] = mt_rand(0, 255);
		}

		$uuid = sprintf('%08x-%04x-%04x-%02x%02x-%02x%02x%02x%02x%02x%02x',
			$uuid['time_low'],
			$uuid['time_mid'],
			$uuid['time_hi'],
			$uuid['clock_seq_hi'],
			$uuid['clock_seq_low'],
			$uuid['node'][0],
			$uuid['node'][1],
			$uuid['node'][2],
			$uuid['node'][3],
			$uuid['node'][4],
			$uuid['node'][5]
		);

		return $uuid;
	}
}
