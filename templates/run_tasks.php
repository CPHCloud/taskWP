<?php

define('TASKWP_FORCE_RUN', true);

$tasks 		= array_map('trim', explode(',', get_query_var('tasks')));
$task_id 	= get_query_var('taskwp_id');

if(!taskwp($task_id)){
	die('ID "'.$task_id.'" has not registered a taskWP instance');
}


function spitname($name){
	echo 'Completed task "'.$name.'"<br/>';
}
add_action('taskWP/task/complete', 'spitname');


foreach($tasks as $task_name){

	if(!taskwp($task_id)->tasks[$task_name]){
		echo 'Error: No task with the name "'.$task_name.'" is registered<br/>';
		continue;
	}

	taskwp($task_id)->run_task($task_name);

}


?>