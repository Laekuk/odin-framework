<?php
class brick__freyja extends brick_mortar
{
	var $config;
	function __construct($conf)
	{
		$this->config	= $conf;
		$this->set_brick_path(dirname(__FILE__).'/',str_replace('brick_','',__CLASS__));
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