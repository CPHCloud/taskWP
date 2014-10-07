<?php


/**
* taskWP
* A simple class manager for WordPress
*/
if(!class_exists('taskWP')){

class taskWP {
	
	function __construct($id){
		$this->path  		= dirname(__FILE__);
		$this->id 			= $id;
		$this->option_key 	= 'taskwp_completed_'.md5($id);
		$this->tasks 		= array();
		$this->hooks();
		$this->update_reference();

	}

	function update_reference(){
		$GLOBALS['taskwp_references'][$this->id] = &$this;
	}

	function hooks(){
		include 'inc/routeWP/routeWP.php';
		$this->router = new routeWP();
		$this->setup_routes();
	}

	function setup_routes(){

		$this->router->add_route(array(
			'pattern' 		=> '~/tasks/([^\/]{1,})/run/([^\/]{1,})/?$~i',
			'template' 		=> $this->path.'/templates/run_tasks.php',
			'query_vars' 	=> array(
				'taskwp_id'		=> '$1',
				'tasks' 	 	=> '$2'
				)
			));

		$this->router->add_route(array(
			'pattern' 		=> '~/tasks/([^\/]{1,})/list/?$~i',
			'template' 		=> $this->path.'/templates/list_tasks.php',
			'query_vars' 	=> array(
				'taskwp_id'		=> '$1'
				)
			));

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
		if(array_search($name, $completed))
			return $completed;

		$completed[] 	= $name;

		if($updated = update_option($this->option_key, $completed)){
			$this->completed = $completed;
			do_action('taskWP/task/complete', $name);
		}

		return $updated;

	}


	/**
	 * Run the tasks that are set up
	 *
	 * @return void
	 **/
	function run_tasks($force = false){
	    
	    if(empty($this->tasks))
	    	return false;

	    /* Loop through each available task */
      	foreach($this->tasks as $name => $task){

      		if(in_array($name, $this->get_completed_tasks()) and !$force and !($_GET['force_tasks'] and is_admin()))
      			continue; // This task is completed so we continue the loop

			$this->run_task($name);

      	}
    
	    $this->clean_completed_tasks();
		
    }


    function run_task($name){

    	$task = $this->tasks[$name];

    	if(is_callable($task['handler'])){

			if($task['hook'] and !defined('TASKWP_FORCE_RUN')){

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

if(!function_exists('taskwp')){
	function taskwp($id){
		return $GLOBALS['taskwp_references'][$id];
	}
}

}


?>