<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zombies</title>
    <style>
        table {
            width: 50%;
            border-collapse: collapse;
            margin: 25px 0;
            font-size: 18px;
            text-align: left;
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

<?php
$command = 'ps aux | grep \'&& php -r \\"\\\\$ref_a\' | grep -v grep';

$output = shell_exec($command);

if ($output)
{
    echo "<span style=\"background-color: #F00; font-size:30px;\">Possible Zombie-processes: (maybe Zombies / maybe currently Active!)</span><br/><br/>";
    
    echo '<table>
	    <tr>
	        <th>User</th>
	        <th>PID</th>
	        <th>Start Time</th>
	        <th>$instance_name</th>
	    </tr>';
			    
    foreach(array_filter(explode("\n", $output)) as $pzs)
    {
    	$pzs_info = array_values(array_filter(explode(" ", explode('\' && php -r', $pzs)[0])));
    	echo " <tr>
			        <td>".$pzs_info[0]."</td>
			        <td>".$pzs_info[1]."</td>
			        <td>".$pzs_info[8]."</td>
			        <td>".explode("'", explode('\' && php -r', $pzs)[0])[1]."</td>
			    </tr>";
    }
}
else
{
    echo "<span style=\"background-color: #0F0; font-size:30px;\">No Zombie-processes found!</span>";
}

?>
</body>
</html>
