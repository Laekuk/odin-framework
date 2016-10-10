<?php
class app_myapp extends _thunderbolt
{
	function __construct($conf)
	{
		global $odin;
		$this->conf = $conf;
		$this->conf->model_setup($this);
	}
	function start()
	{
		// get route
		global $odin;
		
		// Load Controller Information
		$load		= $odin->router->load_app('myapp');
		$content	= '';
		if($load)
		{
			// setup the template bolts directories
			$this->conf->template_setup();
			
			$odin->template->controller_info = [
				"controller"	=> str_replace($load->controller_prefix,"",$load->controller),
				"method"		=> $load->method,
			];

			// load controller class
			$class				= $load->controller;
			$this->controller	= new $class($this);
			
			
			// call the controller method Via PHP call_user_func_array
			$content	= call_user_func_array(array($this->controller,$load->method), $load->arguments);
		}
		else
			{ $content	= '<p>Not found.</p>'; }

		// return controller content
		return  $content;
	}
} 