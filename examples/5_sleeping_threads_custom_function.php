<?php

include_once("../asi.class.php");
include_once("example.helper.php");

$loops = 5;
$childs = array();

/* Print DateTime */
asi\async::dts();

for ($i = 0; $i < $loops; $i++)
{
	// Create new child-thread and include additional code-files (use full system paths!)
	$child[$i] = asi\async::new("Example Thread: ".$i, [__DIR__."/example.helper.php"]);
	
	// Disable autowait
	asi\async::autowait($child[$i], false);
	
	// Execute custom defined "test_function_1" in (example.helper.php) on the child-thread
	// !!IMPORTANT both, the main and the child must know the function!!
	// and assing it to the childs variable "example"
	$child[$i]->example = asi\test_function_1(3);
}

/* Print DateTime */
asi\async::dts();

for ($i = 0; $i < $loops; $i++)
{
	// Wait for the child-thread to finish execution on the "example" child variable
	asi\async::wait($child[$i]->example);
	
	// Print the value returnd by child-thread
	var_dump($child[$i]->example);
	echo "<br/>";
	
	// shutdown the child-thread
	unset($child[$i]);
}

/* Print DateTime */
asi\async::dts();

?>
