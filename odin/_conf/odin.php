<?php
#AaronPeloquin;11OCT2014;Odin-Framework
class conf_odin
{
#	protected	$paths;
	function __construct($my_dir)
	{
		$this->paths	= (object) array(
			"base"			=> $my_dir."/",
			"configs"		=> $my_dir."/_conf/",
			'bolts'		 	=> $my_dir."/bolts/",
		);

	}
}