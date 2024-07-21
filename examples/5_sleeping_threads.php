<?php

include_once("../asi.class.php");

$loops = 5;
$childs = array();

/* Print DateTime */
asi\async::dts();

for ($i = 0; $i < $loops; $i++)
{
	// Create new child-thread
	$child[$i] = asi\async::new("Example Thread: ".$i);
	
	// Disable autowait
	asi\async::autowait($child[$i], false);
	
	// Execute php's sleep() on the child-thread (sleep 5 seconds)
	// and assing it to the childs variable "example"
	$child[$i]->example = asi\sleep(5);
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
	asi\async::unset($child[$i]);
}

/* Print DateTime */
asi\async::dts();

?>
