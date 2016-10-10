<?php
#CreatedBy;Weston;12JAN2016;Odin-Framework
class bolt_filesystem
{
	function __construct($conf)
	{
		$this->conf	= $conf;
		$this->root = $_SERVER['DOCUMENT_ROOT'];
	}

	// get the files in this directory
	function get_files($path,$ext=false)
	{
		$f = [];
		$ret = $this->get($path);
		if($ext && !is_array($ext))
			{ $ext = [$ext]; }
		if(!empty($ret))
		{
			foreach($ret as $p)
				{ if(!is_dir($p) && ($ext?in_array(pathinfo($p,PATHINFO_EXTENSION), $ext):true)) { $f[] = $p; } }
		}
		return $f;
	}

	// get the folders in this directory
	function get_dirs($path)
		{ return $this->get_folders($path); }

	function get_folders($path)
	{
		$f = [];
		$ret = $this->get($path);
		if(!empty($ret))
		{
			foreach($ret as $p)
				{ if(is_dir($p)) { $f[] = $p; } }
		}
		return $f;
	}

	// get the files and folders in this directory
	function get($path)
	{
		$f = [];
		$path = $this->fullpath($path);
		if(file_exists($path))
		{
			$dh  = opendir($path);
			if($dh)
			{
				while (false !== ($name = readdir($dh)))
					{ if($name != '..' && $name != '.') { $f[] = $path.$name; } }
				sort($f);
			}
			return $f;
		}
		return false;
	}

	// get the files and folders stack in this directory
/*
	function get_all($path,$ext=false)
	{
		
	}
*/

	function remove_files($files)
	{
		if(!is_array($files))
			{ $files = [$files]; }
		foreach($files as $file)
		{
			if(file_exists($file) && !is_dir($file))
				{ unlink($file); }
		}
	}

	// make sure the path has the document root in the begining
	function fullpath($path)
	{
		return (substr($path, 0, strlen($this->root)) != $this->root) ? $this->root.(substr($path,0,1)=='/' ? '/':'').$path : $path;
	}
}