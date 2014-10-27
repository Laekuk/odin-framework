<?php
#CreatedBy;Aaron;11OCT2014;Odin-Framework
class conf_odin
{
#	protected	$paths;
	function __construct($my_dir)
	{
		$this->paths	= (object) array(
			"base"			=> $my_dir."/",
			"configs"		=> $my_dir."/_conf/",
			'bolts'		 	=> $my_dir."/bolts/",		#A single classes of code
			'bricks'	 	=> $my_dir."/bricks/",		#folders of code. usually using multiple bolts, adding their own functionality.
			'apps'		 	=> $my_dir."/apps/",		#The tenative name for groups of bricks.
														#example: forum software, which is using a users brick, as well as a content manipulation brick
		);

	}
}