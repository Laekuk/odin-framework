<?php
#CreatedBy;Aaron;11OCT2014;Odin-Framework
#Some more information or like whatever:
	#hhttps://github.com/aaronpeloquin/odin-frameworkk
class odin extends _thunderbolt
{
	/*
		Odin is a new PHP Framework which is a hybrid between functional & object oriented programming.
		It aims to create a system that will take advantage of both programming styles, giving the
		programmer both power and preformance.

		This can be used to create a stand-alone application, or as a simple way to dynamically load your
		own php libraries/classes) into another application either as its being built or after it was already completed.

		From here on, we will be reffering to all php libraries/classes as 'bolts'.
	*/

	#reserved names that are not allowed to be loaded.
	function __construct($my_dir,$conf_dir='_conf')
	{
		#load the default configs for $odin.
		require_once("$my_dir/$conf_dir/odin.php");
		$this->conf	= new conf_odin($my_dir);
		$this->_odin_set_conf(
			array(
				'paths'	=> array(
					'lib'	=> $this->conf->paths->bolts,
					'conf'	=> $this->conf->paths->confs,
				),
				'prefix'	=> 'bolt_',
#				'reserved'	=> array('load_bolt'=>TRUE,'bolts'=>TRUE,'odin'=>TRUE,'reserved_names'=>TRUE),
			)
		);
	}

	#return bool if the method exists inside of a specific bolt. Also auto-loads the bolt.
	function bolt_method_exists($bolt,$method)
		{ return method_exists($this->{$bolt},$method); }
}


/*	The methods in this class (and the class itself) are named weird to avoid class-name & function-name overlaps.
	This is intended to be extended by things and have its methods never actually called by the developer,
		except when s/he calls the _odin_set_conf() in their construct, but I'm envisioning that as being a copy/paste for them.
*/
class _thunderbolt
{
/*
Usage:
On the class you want to use autoloading with, just do "class {class_name} extends _thunderbolt".
Then in your __construct() method, tun this code, with your own configuration typed in:
	$this->_odin_set_conf(
		array(
			'paths'				=> array(
				'lib'				=> $paths->bricks,
				'conf'				=> $paths->bricks,
			),
			'prefix'			=> 'mortar_'.$class_name.'_',
			'folder_name'		=> TRUE,
			'reserved'			=> array('load_bolt'=>TRUE,'bolts'=>TRUE,'odin'=>TRUE,'reserved_names'=>TRUE),
		)
	);
*/
	var $_odin_reserved_vars;
	var $_odin_paths;
	var $_append_folder_name;
	var $_prefix;

	function __get($name)
	{
		switch(TRUE)
		{
			#block reserved names.
			case (isset($this->_odin_reserved_vars[$name])):
				return FALSE;
			break;
			#if the name is already set, return it
			case (isset($this->{$name})):
			#attempt to load the name.
			case ($this->_odin_autoload($name)):
				return $this->{$name};
			break;
			break;
		}
		return FALSE;
	}

	function _odin_set_conf($o)
	{
		#set paths?
		if(isset($o['paths']))
		{
			#clear (or make) the _odin_paths variable
			$this->_odin_paths	= new stdClass();
			#set the conf path (if there is one)
			if(isset($o['paths']['conf']))
				{ $this->_odin_paths->conf	= $o['paths']['conf']; }
			#set the lib path (if there is one). Note: If this never gets a lib path, this will never load libraries (classes) of code.
			if(isset($o['paths']['lib']))
				{ $this->_odin_paths->lib	= $o['paths']['lib']; }
		}

/*		This option is a boolean that decides how the paths works:
			TRUE	= append the $name as a folder to the end of the lib & configs path,
			and the config files are loaded from the conf dir like this:
				libraries	~/$o[paths][lib]/$name/$name.php
				configs	~/$o[paths][conf]/$name/conf.php
			FALSE	= does not append $name as a folder
				libraries	~/$o[paths][lib]/$name.php
				configs	~/$o[paths][conf]/$name.php
*/
		if(isset($o['folder_name']))
			{ $this->_append_folder_name	= $o['folder_name']; }

		#Whatever prefix you put on your classes that are auto-loaded, if any.. and you really should have one, but its not required
		if(isset($o['prefix']))
			{ $this->_prefix	= $o['prefix']; }

		#set the reserved class-names, if its keys are not strings, array_flip it so we can use the more efficient is_set() on our checks.
		if(isset($o['reserved']))
			{ $this->_odin_reserved_vars	= (isset($o['reserved'][0])?array_flip($o['reserved']):$o['reserved']); }
		return TRUE;
	}

	function _odin_autoload($name)
	{
		#get a local cache of the load settings
		$paths			= $this->_odin_paths;
		$append			= $this->_append_folder_name;
		$class_prefix	= $this->_prefix;
		#setup paths ahead of time
		$lib_path		= $paths->lib.$name.($append?(is_string($append)?"/$append/$name":"/$name"):'').'.php';
		if(isset($paths->conf) && $paths->conf)
			{ $conf_path		= $paths->conf.$name.($append?(is_string($append)?"/$append":"/conf"):'').'.php'; }

		#return false if this file does not exist.
		if(!file_exists($lib_path))
			{ return FALSE; }
		#load the bolt file
		require_once($lib_path);
/*		set $conf & then load this bolt's config file, if it exists.
			note: if you do have a bolt config file, then whatever you set into a $conf variable in that php script
			will be sent into your bolt file
*/		$conf	= NULL;
		if(isset($conf_path) && file_exists($conf_path))
			{ include($conf_path); }
		#get the class name for this bolt.
		$cname	= $class_prefix.$name;

		#initate the new library.
		$this->{$name}	= new $cname($conf);
		return TRUE;
	}
}