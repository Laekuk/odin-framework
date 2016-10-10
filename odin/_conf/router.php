<?php
#CreatedBy;Weston;22OCT2015;Odin-Framework
class odin_config_router
{
	function __construct()
	{
		global $odin;
		$this->controller_prefix	= "controller_";
		$this->default_app	 		= "cfb";
		$this->default_controller 	= "home";
		$this->default_method 		= "index";
		// from the root of the apps directory
		$this->controller_path		= "controller/";
	}
}
$conf = new odin_config_router();
