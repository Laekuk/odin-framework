<?
class brick_users extends brick_mortar
{
	var $config;
	function __construct($conf)
	{
		$this->config	= $conf;
		$this->set_brick_path(dirname(__FILE__).'/',str_replace('brick_','',__CLASS__));
	}
}