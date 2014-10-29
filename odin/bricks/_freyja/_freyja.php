<?php

class brick__freyja extends _thunderbolt
{
	var $config;
	function __construct($conf)
	{
		$this->config	= $conf;
		$class_name		= str_replace('brick_','',__CLASS__);
		$this->_odin_set_conf(
			array(
				'paths'				=> array(
					'lib'				=> dirname(__FILE__).'/',
				),
				'prefix'			=> 'mortar_'.$class_name.'_',
#				'name_is_prefix'	=> TRUE,
#				'folder_name'		=> $class_name,
			)
		);
	}

	function TOIF($saying=FALSE)
	{
		if(!$saying)
			{ $saying	= "Thank Odin, its Freyja!"; }
		$saying	= "<h2>".$saying."</h2>"
			."<p>".$this->config["p"]."</p>"
			.$this->features->list_them();
		return $saying;
	}
}