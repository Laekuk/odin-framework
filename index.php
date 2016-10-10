<?php
/*
	Apps ( MVC )
	Example of running an app named myapp
	Note: Apps are located in ./odin/apps/ directory
*/
// Start Odin
require_once("./odin/fury.php");
// start a buffer
ob_start();
// run myapp
echo $odin->app->myapp->start();
