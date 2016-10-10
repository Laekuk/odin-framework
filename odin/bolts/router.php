<?php
#CreatedBy;Weston;22OCT2015;Odin-Framework
class bolt_router
{
	function __construct($conf)
	{
		$this->conf			= $conf;
		$this->parse();
	}

	function parse()
	{
		$this->uri_string	= !empty($_GET['_uri']) ? $_GET['_uri'] : '';
		if(substr($this->uri_string,-1) == '/')
			{ $this->uri_string = substr($this->uri_string,0,-1); }
		
		$this->stack = array();
		if(!empty($this->uri_string))
			{ $this->stack = explode("/",$this->uri_string); }
	}

	function load_app($app=false,$conf=array())
	{
		global $odin;
		// extend default configuration with your options
		$c = (object)$odin->array->ow_merge_r((array)$this->conf,(array)$conf);
		
		$load 					= new stdClass();
		$load->app 				= empty($app) ? $c->default_app : $app;

		// mvc
		$load->controller_prefix= $c->controller_prefix;
		$load->controller 		= $c->default_controller;
		$load->method 			= $c->default_method;
		$load->arguments		= array();

		// paths
		$load->app_path			= $odin->conf->paths->apps.$load->app.'/';
		$load->controller_path	= $load->app_path.$c->controller_path;
		
		// internal variables
		$class		= '';
		$load_file	= '';
		$controller_path = str_replace("//","/",$load->controller_path.$this->uri_string);
		if(substr($controller_path,-1)=="/")
			{ $controller_path = substr($controller_path,0,-1); }

		if(!empty($this->stack))
		{
			$rev_stack = array_reverse($this->stack);
			//DON'T STOP! <3
			$stop = FALSE;
			foreach($rev_stack as $index=>$dir)
			{
				$raw_dir_value	= $dir;
				if(!$stop)
				{
					$controller_path = substr($controller_path,0,-(strlen($dir)+1));
					if(strpos($dir, '-')!=FALSE)
						{ $dir	= str_replace('-', '_', $dir); }
					$path = $controller_path.'/'.$dir;
					
					// check for controller
					$invalid_filename = preg_match('/[\*\.\"\[\];\|=,]/',$path);
					if(!$invalid_filename && file_exists($path.".php"))
					{
						$underscore			= (strpos($dir,'_') !== false);
						$dir 				= str_replace("-","_",$dir);
						$class  			= $dir;
						$load_file			= $path.".php";
						$stop 				= TRUE;
						if($underscore)
							{ $invalid_name = $underscore; }
					}
					// check for folder name and default controller file
					elseif(!$invalid_filename && file_exists($path.'/'.$load->controller.".php"))
					{
						$class  			= $load->controller;
						$load_file			= $path.'/'.$load->controller.".php";
						$stop 				= TRUE;
					}
					else
					{
						if(!$stop)
							{ $load->arguments[] = $raw_dir_value; }
					}
				}
			}
		}
		// use the default controller if noone found
		if($class == '')
		{
			$class = $load->controller;
			if(file_exists($controller_path.'/'.$class.".php"))
				{ $load_file = $controller_path.'/'.$class.".php"; }
		}
		// if no file found return
		if(empty($load_file))
			{ return false; }
		$class		= $load->controller_prefix.str_replace("-","_",$class);
		
		// load the file - see if that class is defined
		require_once($load_file);
		if(!class_exists($class))
			{ die('Class <strong>'.$class.'</strong> not found in: '.$load_file); }
		
		$method = false;
		if(!empty($load->arguments))
		{
			$load->arguments	= array_reverse($load->arguments);
			$method				= str_replace("-","_",$load->arguments[0]);
		}
		
		// grab variables left over and call function
		if($method && method_exists($class,$method))
			{ unset($load->arguments[0]); }
		elseif(method_exists($class,$load->method))
			{ $method = $load->method; }
		else
			{ $method = false; }
		
		if(!$method)
			{ die('Class <strong>'.$class.'</strong> does not have that method in: '.$load_file); }
		
		$load->controller	= $class;
		$load->method		= $method;
		$load->uri			= implode("/",$load->arguments);
		$load->file			= $load_file;

		$this->loaded = $load;
		return $this->loaded;
	}
	
}
