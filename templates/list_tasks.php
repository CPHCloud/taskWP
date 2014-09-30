<?php

$handler = taskwp(get_query_var('taskwp_id'));

echo '<strong>Total tasks: '.count($handler->tasks).'</strong><br/>';
foreach($handler->tasks as $name => $task){
	echo $name.'<br/>';
}

?>