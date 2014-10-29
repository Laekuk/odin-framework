<?php
#CreatedBy;Aaron;27OCT2014;Odin-Framework
/*
	Bricks are like bolts but on steroids. Instead of just being one single file, its a small collection of files in a folder.
	Bricks are intended to extend the "brick_mortar" class, then run $this->set_brick_conf("/path/to/brick","brick name");
	See the /odin/bricks/_freyja/_freyja.php & its folder for example code of how these work.
*/
class bolt_brick extends _thunderbolt
{
	var $paths;
	function __construct()
	{
		global $odin;
		$paths	= $odin->conf->paths;
		$this->_odin_set_conf(
			array(
				'paths'				=> array(
					'lib'				=> $paths->bricks,
					'conf'				=> $paths->bricks,
				),
				'prefix'			=> 'brick_',
				'folder_name'		=> TRUE,
			)
		);
	}
}