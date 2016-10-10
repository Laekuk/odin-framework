<?php
#CreatedBy;Weston;27OCT2015;Odin-Framework
class bolt_template
{
	function __construct($conf)
	{
		$this->conf				= $conf;

		$this->template_dir		= (!empty($this->conf->template_dir)?$this->conf->template_dir:false);
		$this->template_default	= (!empty($this->conf->template_default)?$this->conf->template_default:false);
		$this->view_dir			= (!empty($this->conf->view)?$this->conf->view:false);
		$this->extensions 		= array('php','html','inc');
	}
	
	// define where the templates are located
	#  $odin->template->template_path('path_to_template_file');
	// final render of the page.
	// render will collect the output buffer and append it to the end of $replace['content'];
	// all paramiters are optional.
	#  $odin->template->render($key_value_array,'template_name');
	function render($replace=array(), $template=false, $clear_buffer=true)
	{
		$content = '';
		if(!empty($replace) && is_string($replace))
			{ $replace = array('content'=>$replace); }
		// grab the buffer if you it to
		if($clear_buffer && ob_get_level()>0)
		{
			while(ob_get_level() > 0)
				{ $content .= ob_get_clean(); }
		}
		
		$replace	= array_merge(
			$this->conf->template_data_default,
			$replace
		);
		$replace['content'] .= $content;
		$template	= $this->template_path($template);
		return $this->replace($replace,$template);
	}
	function template_path($name=false)
	{
		$templatepath 	= false;
		$path 			= str_replace("//","/",$this->template_directory.($name?:$this->template_default));
		if(!file_exists($path))
		{
			foreach($this->extensions as $ext)
				{ if(file_exists($path.'.'.$ext)) { $templatepath = $path.'.'.$ext; break; } }
		}
		else
			{ $templatepath = $path; }
		return $templatepath;
	}
	function template_name($name)
	{
		if($this->template_path($name))
			{ $this->template_default = $name; }
	}
	function template_dir($path)
	{
		if(file_exists($path) && is_dir($path))
			{ $this->template_directory = $path; }
		if(substr($this->template_dir,-1)!='/')
			{ $this->template_directory .= '/'; }
	}
	
	// define where the views are located
	#  $odin->template->view_dir('/path/to/views/');
	// array of replace content
	// name of the view file (can include extension and directories if needed)
	#  $odin->template->view($key_value_array, 'view_name');
	#  $odin->template->view($key_value_array, 'view_name.html');
	#  $odin->template->view($key_value_array, 'view_name.php');
	#  $odin->template->view($key_value_array, 'messages/hello');
	#  $odin->template->view($key_value_array, 'messages/hello.inc');
	function view($replace=array(), $view_name=false)
	{
/*
		if(is_array($view_name))
		{
			$temp_view_name	= $view_name;
			$view_name		= $replace;
			$replace		= $temp_view_name;
			unset($temp_view_name);
		}
*/

		if(!$view_name && !is_array($replace))
			{ $view_name = $replace; $replace=array(); }
		$viewpath 	= false;
		$path 		= $this->view_dir.$view_name;
		if(!file_exists($path))
		{
			$replace = array_merge(
				$this->conf->view_data_default,
				$replace
			);
			foreach($this->extensions as $ext)
				{ if(file_exists($path.'.'.$ext)) { $viewpath = $path.'.'.$ext; break; } }
		}
		else
			{ $viewpath = $path; }
		if($viewpath)
			{ return $this->replace($replace,$viewpath); }
		return false;
	}
	function view_dir($path)
	{
		if(file_exists($path) && is_dir($path))
			{ $this->view_dir = $path; }
		if(substr($this->view_dir,-1)!='/')
			{ $this->view_dir .= '/'; }
	}
	function replace($data=array(),$template)
	{
		if(file_exists($template))
		{
			if(substr($template,-4) == ".php")
				{ return $this->php_replace($data,$template); }
			else
				{ $template = file_get_contents($template); }
		}
		global $odin;
		return $odin->str->replace($template,$data);
	}

	function php_replace($data=array(),$template)
	{
		if(file_exists($template))
		{
			if(isset($data['template']))	{ unset($data['template']); }
			if(isset($data['data'])) 		{ unset($data['data']); }
			if(isset($data['odin'])) 		{ unset($data['odin']); }
			extract($data);
			global $odin;
			ob_start();
			include($template);
			return ob_get_clean();
		}
		return false;
	}
}