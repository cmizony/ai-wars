<?php

set_include_path(get_include_path().PATH_SEPARATOR.dirname(__FILE__)."/src");

require_once('vendor/autoload.php');

function shutdown_simulator ($object)
{
	$object->free();
}

$simulator=new AI_wars\AI_Game_simulator();
register_shutdown_function('shutdown_simulator',$simulator);

$config=parse_ini_file('config.ini',true);
if (!$config)
	exit("Error, unable to load the config file \"$config_file\"\n");

try
{
	$simulator->run($config);
	echo $simulator->render("json");
	$simulator->free();
}
catch (Exception $e)
{
	echo 'Exception : '.$e->getMessage();
	$simulator->free();
}



?>
