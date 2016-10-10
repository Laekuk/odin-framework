<?php
class controller_home
{
	function __construct($app)
		{ $this->app = $app; }

	function index($arg1="",$arg2="")
	{
		global $odin;
		echo $odin->template->view([
			"heading" => "Hello World",
			"arg1" => $arg1,
			"arg2" => $arg2,
		],'test');
		return $odin->template->render();
	}
}
