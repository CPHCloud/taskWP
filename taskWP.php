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


	/**
	 * Run the tasks that are set up
	 *
	 * @return void
	 **/
	function run_tasks(){
	    
	    if(empty($this->tasks))
	    	return false;

	    /* Loop through each available task */
      	foreach($this->tasks as $name => $handler){

      		if(in_array($name, $this->get_completed_tasks()))
      			continue; // This task is completed so we continue the loop

			if(is_callable($handler)){
				add_action('taskWP/run_task', $handler); // Handler is callable so we hook it to the taskWP/run_task
				do_action('taskWP/run_task'); // Run the action to execute the handler callback
				remove_action('taskWP/run_task', $handler); // Remove the handler again
			}
			else{
				if(file_exists($handler))
					include_once $handler; // Handler is a file. Include it.

			}

      		$did_tasks = true;
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