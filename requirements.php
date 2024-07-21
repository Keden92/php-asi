<?php
$version = phpversion();

if (preg_match('/^7|^8/', $version))
{
	echo "PHP Version: " . $version . " - <span style=\"background-color: #0F0\">OK!</span><br/><br/>";
}
else
{
	echo "PHP Version: " . $version . " - <span style=\"background-color: #FF0\">Check Faild, but maybe OK!</span><br/><br/>";
}

function check_disabled($func)
{
	$disabled =  ini_get('disable_functions');
	if(strpos($disabled, $func) === false)
	{
		echo $func. " should be <span style=\"background-color: #0F0\">Enabled! - OK!</span><br/>";
	}
	else
	{
		die($func. " is <span style=\"background-color: #F00\">Disabled!</span> Please enable and try again.<br/>");
	}
}

check_disabled("eval");
check_disabled("proc_open");
check_disabled("proc_get_status");
check_disabled("proc_close");

$proc = proc_open("php -v", array(array("pipe","r"), array("pipe","w"), array("pipe","w")), $pipes);

$response = stream_get_contents($pipes[1]);
if (preg_match('/PHP [78].+\(cli\)/', $response, $matches))
{
	echo "<br/>CLI: " . $matches[0] . " - <span style=\"background-color: #0F0\">OK!</span><br/>";
}
else
{
	die("<br/>CLI: " . $response . " - <span style=\"background-color: #FF0\">Check Faild!</span> Install php-cli and try again.<br/>");
}

echo "<br/><br/><span style=\"background-color: #0F0\">All cheks passed! - Should working :)</span>"

?>
