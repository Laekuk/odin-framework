<?
class cfb_configitation
{
	function __construct()
	{
		global $odin;
		$this->name					= "My App";
		$this->paths 				= new stdClass();
		$this->paths->controllers 	= $odin->conf->paths->apps.'myapp/controller/';
		$this->paths->models 		= $odin->conf->paths->apps.'myapp/model/';
		$this->paths->views 		= $odin->conf->paths->apps.'myapp/view/';
		$this->paths->templates 	= $odin->conf->paths->apps.'myapp/template/';
		$this->paths->confs		 	= $odin->conf->paths->apps.'myapp/_conf/';

	}
	function model_setup($app)
	{
		// set up Model location for MVC
		$app->_odin_set_conf([
			'paths'				=> [
				// model location and model configs (set in app config)
				'lib'	=> $this->paths->models,
				'conf'	=> $this->paths->confs,
			],
			'prefix'			=> 'model_',
			'folder_name'		=> false,
		]);
	}
	function template_setup()
	{
		global $odin;
		// set the template bolts active directories
		$odin->template->template_dir($this->paths->templates);
		$odin->template->view_dir($this->paths->views);
		$odin->template->template_name('default');
	}
}
$conf = new cfb_configitation();