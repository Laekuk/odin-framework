<?php
#CreatedBy;Weston;22OCT2015;Odin-Framework
class odin_config_template
{
	function __construct()
	{
		global $odin;
#		var_dump($odin->conf);die();
		#The default path of the template to use
		$this->template_dir				= $odin->conf->paths->base.'templates';
		#The default path of the view to use
		$this->view_dir					= $odin->conf->paths->base.'views';
		$this->view_data_default		= array(
			'title'			=> 'Page Title',
			'content'		=> '',
		);
		#Name of the primary template to use.
		$this->template_default			= 'default';
		$this->template_data_default	=	array(
			'content'		=> '',
		);
	}
}
$conf = new odin_config_template();
