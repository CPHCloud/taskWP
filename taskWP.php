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
		$this->tasks 		= array();
		$this->hooks();
	}

	function hooks(){

	}

	function add_task($name, $handler, $hook = false, $priority = false, $arguments = false){

		$this->tasks[$name] = array('handler' => $handler);

		if($hook)
			$this->tasks[$name]['hook'] = $hook;

		if($priority)
			$this->tasks[$name]['priority'] = $priority;

		if($arguments)
			$this->tasks[$name]['arguments'] = $arguments;

	}

	function complete_task($name){
		
		$completed 		= $this->get_completed_tasks();
		$completed[] 	= $name;

		if($updated = update_option($this->option_key, $completed)){
			$this->completed = $completed;
		}

		return $updated;

	}


	/**
	 * Run the tasks that are set up
	 *
	 * @return void
	 **/
	function run_tasks(){
	    
	    if(empty($this->tasks))
	    	return false;

	    /* Loop through each available task */
      	foreach($this->tasks as $name => $task){

      		if(in_array($name, $this->get_completed_tasks()) and !($_GET['force_tasks'] and is_admin()))
      			continue; // This task is completed so we continue the loop

			if(is_callable($task['handler'])){

				if($task['hook']){

					if(!$prio = $task['priority'])
						$prio = 10;

					if(!$args = $task['arguments'])
						$args = 1;

					add_action($task['hook'], $task['handler'], $prio, $args);
				
				}
				else {

					add_action('taskWP/run_task', $task['handler']); // Handler is callable so we hook it to the taskWP/run_task
					do_action('taskWP/run_task'); // Run the action to execute the handler callback
					remove_action('taskWP/run_task', $task['handler']); // Remove the handler again

				}

			}
			else{
				if(file_exists($task['handler']))
					include_once $task['handler']; // Handler is a file. Include it.
			}

			$this->complete_task($name);

      	}
    
	    $this->clean_completed_tasks();
		
    }


    function clean_completed_tasks(){

    	$completed = $this->get_completed_tasks();

    	foreach($completed as $k => $task)
    		if(!$this->tasks[$task])
    			unset($completed[$k]);

    	if(empty($completed))
    		delete_option($this->option_key);
    	else
	    	update_option($this->option_key, $completed);
    
    }


	function get_completed_tasks(){

		if(!$this->completed)
			if(!$this->completed = get_option($this->option_key))
				$this->completed = array();


		return $this->completed;

	}

}

}


?>