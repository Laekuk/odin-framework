<?php
#Aaron;16OCT2014;Odin-Framework

/*
	Require the odin framework.
	Specifically fury,php loads the odin framework and initalizes it for us, giving us access to the global $odin variable.
*/
require_once("./odin/fury.php");

/*
	Run the frigg class's (aka: bolt's) method called hello_olympus(), and echo out whatever that method is returning.
	Note: The frigg class is located in ./odin/bolts/_frigg.php
*/
echo $odin->_frigg->hello_olympus();

/*
	Thats it. You can write your own php class and follow the same naming scheme, then call your class in the same way we just called _frigg.

	There is also a configurations system in place. All configs are passed to your constructor, if any exist to be loaded.
	Documentation for configs coming later.
*/
