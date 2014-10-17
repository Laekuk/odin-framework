<?php
#CreatedBy;Aaron;11OCT2014;Odin-Framework
/*
	This is an example framework you can modify and call to learn how it works without messing with any actual "bolts"
	To call it simply run these two lines of code from anywhere on your website:
		require_once("/full/path/to/odin/fury.php");		#This gets you access to the $odin variable.
		$odin->_frigg->hello_olympus();						#This will automatically load the class listed below and run its hello_olympus() method.
*/
class bolt__frigg{
	
	var $children	= array(
		"Baldr",
		"Thor",
	);
	function hello_olympus()
	{
		return "<h1>Hello Asgard</h1>";
	}
}