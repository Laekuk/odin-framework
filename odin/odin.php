<?php
#CreatedBy;Aaron;11OCT2014;Odin-Framework
class odin{
	/*
		Odin is a new PHP Framework which is a hybrid between functional & object oriented programming.
		It aims to create a system that will take advantage of both programming styles, giving the
		programmer both power and preformance.

		This can be used to create a stand-alone application, or as a simple way to dynamically load your
		own php libraries/classes) into another application either as its being built or after it was already completed.

		From here on, we will be reffering to all php libraries/classes as "bolts".
	*/

	#holding place for loaded bolts.
	var $bolts			= array();
	var $reserved_names	= array("load_bolt"=>TRUE,"bolts"=>TRUE,"odin"=>TRUE,"reserved_names"=>TRUE);
	function __construct($my_dir,$conf_dir="_conf")
	{
		#load the default configs for $odin.
		require_once("$my_dir/$conf_dir/odin.php");
		$this->conf	= new conf_odin($my_dir);
	}

	function __get($name)
	{
		#block reserved names.
		if(isset($this->reserved_names[$name]))
			{ return FALSE; }
		
		switch(TRUE)
		{
			case (isset($this->{$name})):
			case ($this->load_bolt($name)):
				return $this->{$name};
			break;
		}
		return FALSE;
	}

	#loads a bolt (PHP class/library) from the filesystem into memory
	function load_bolt($name)
	{
		#get a local cache of the configuration for odin's paths.
		$paths	= $this->conf->paths;
		#return false if this bolt does not exist.
		if(!file_exists($paths->bolts.$name.".php"))
			{ return FALSE; }
		#load the bolt file
		require_once($paths->bolts.$name.".php");
/*		set $conf & then load this bolt's config file, if it exists.
			note: if you do have a bolt config file, then whatever you set into a $conf variable in that php script
			will be sent into your bolt file
*/		$conf	= NULL;
		if(file_exists($paths->configs.$name.".php"))
			{ include($paths->configs.$name.".php"); }
		#get the class name for this bolt.
		$cname	= "bolt_".$name;
		#initate the new bolt.
		$this->{$name}	= new $cname($conf);
		return TRUE;
	}

	#return bool if the method exists inside of a specific bolt. Also auto-loads the bolt.
	function bolt_method_exists($bolt,$method)
		{ return method_exists($this->{$bolt},$method); }
}