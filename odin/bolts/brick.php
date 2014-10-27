<?php
#CreatedBy;Aaron;27OCT2014;Odin-Framework
/*
	Bricks are like bolts but on steroids. Instead of just being one single file, its a small collection of files in a folder.
	Bricks are intended to extend the "brick_mortar" class, then run $this->set_brick_conf("/path/to/brick","brick name");
	See the /odin/bricks/_freyja/_freyja.php & its folder for example code of how these work.
*/
class bolt_brick
{
	var $paths;
	function __construct()
	{
		global $odin;
		$this->paths	= $odin->conf->paths;
	}

	function __get($name)
	{
		if($name=="mortar")
			{ die("seriously, mortar is the only reserved brick name"); }
		switch(TRUE)
		{
			case (isset($this->{$name})):
			case ($this->load_brick($name)):
				return $this->{$name};
			break;
		}
		return FALSE;
	}

	function load_brick($name)
	{
		global $odin;
		#get a local cache of the brick's path.
		$brick_path	= $this->paths->bricks."/".$name."/";
		#return false if this brick does not exist.
		if(!file_exists($brick_path.$name.".php"))
			{ return FALSE; }
		#load the brick file
		require_once($brick_path.$name.".php");
/*		set $conf & then load this brick's config file, if it exists.
			note: brick config files work exactly like bolt config files.
*/		$conf	= NULL;
		if(file_exists($brick_path."conf.php"))
			{ include($brick_path."conf.php"); }
		#get the class name for this brick.
		$cname	= "brick_".$name;
		#initate the new brick.
		$this->{$name}	= new $cname($conf);
		return TRUE;
	}
}

/*
	This class & its methods are named weird so they don't overlap with your class methods on accident,
		and really it should only ever be calling __get() for you.
	Its basically the brick class, with configs ripped out, since these are just single files.
*/
class brick_mortar
{
	var $brick_path;
	function set_brick_path($path,$class)
	{
		$this->brick_path	= $path;
		$this->class_name	= $class;
	}

	function __get($name)
	{
		switch(TRUE)
		{
			case (isset($this->{$name})):
			case ($this->add_clay_to_brick($name)):
				return $this->{$name};
			break;
		}
		return FALSE;
	}

	function add_clay_to_brick($name)
	{
		if(!file_exists($this->brick_path.$name.".php"))
			{ return FALSE; }
		require_once($this->brick_path.$name.".php");
		#get the class name for this brick.
		$cname	= "mortar_".$this->class_name."_".$name;
		
		#initate the new brick.
		$this->{$name}	= new $cname();
		return TRUE;
	}
}