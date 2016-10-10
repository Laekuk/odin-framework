<?php
class bolt_app extends _thunderbolt
{
	var $paths;
	function __construct()
	{
		global $odin;
		
		$paths	= $odin->conf->paths;
		$this->_odin_set_conf(
			array(
				'paths'				=> array(
					'lib'				=> $paths->apps,
					'conf'				=> $paths->apps,
				),
				'prefix'			=> 'app_',
				'folder_name'		=> TRUE,
			)
		);
	
	}
}