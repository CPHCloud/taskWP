<?php


/**
* taskWP
* A simple class manager for WordPress
*/
if(!class_exists('taskWP')){

class taskWP {
	
	function __construct($id){
		$this->id 			= $id;
		$this->option_key 	= 'taskwp_completed_'.md5($id);
		$this->hooks();
	}

	function hooks(){

	}

	function add_task($name, $handler){
		$this->tasks[$name] = $handler;
	}

	function complete_task($name){
		
		$completed 		= $this->get_completed_tasks();
		$completed[] 	= $name;

		if($updated = update_option($this->option_key, $completed)){
			$this->completed = $completed;
		}

		return $updated;

	}

	function get_completed_tasks(){

		if(!$this->completed){
			$this->completed = get_option($this->option_key);
		}

		return $this->completed;

	}

}

}


?>