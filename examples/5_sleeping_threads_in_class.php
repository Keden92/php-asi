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
	
	// Create Custom Class (defined in example.helper.php)
	$myclass = new test_class(5);
	
	// Pass Class to child Process
	$child[$i]->myclass = $myclass;
	
	// Or directly create the object on the child-thread
	// $child[$i]->myclass = new asi\test_class(5);
	
	// Disable autowait
	asi\async::autowait($child[$i], false);
	
	// Execute the "mysleep()" function on $myclass on the child-thread
	// !!IMPORTANT both, the main and the child must know the class!!
	// and assing it to the childs variable "example"
	$child[$i]->example = $child[$i]->myclass->mysleep();
	
	unset($myclass); // Not needet any more after passing to child
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
