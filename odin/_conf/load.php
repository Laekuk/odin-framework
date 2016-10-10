<?
class conf_bolt_load
{
	function __construct()
	{
		global $odin;
		$this->inline		= false;
		// $this->compress	= false;
		$this->cache		= true;
		// keep unused cache files 1 week
		$this->cache_time	= (7 * 24 * 60 * 60);
		if(!empty($_GET['togglecache']))
			{ $this->cache	= !$this->cache; }
		$this->path = (object)array(
			'root'			=> $_SERVER['DOCUMENT_ROOT'].'/',
			'base'			=> $odin->conf->uri->template,
			'cache_files'	=> $_SERVER['DOCUMENT_ROOT'].'/cache/',
			'js'			=> array(
				$odin->conf->uri->template.'js/',
				$odin->conf->uri->template.'app/',
				$_SERVER['DOCUMENT_ROOT'].'/js/',
			),
			'css'			=>  array(
				$odin->conf->uri->template.'css/',
				$_SERVER['DOCUMENT_ROOT'].'/css/',
			),
		);

		$this->template = (object)array(
			'js'			=> '<script type="text/javascript" src="{path}" data-group="{name}"></script>',
			'js_ltie9'		=> '<!--[if lt IE 9]><script type="text/javascript" src="{path}" data-group="{name}"></script><![endif]-->',
			'js_inline'		=> '<script type="text/javascript" data-group="{name}">{content}</script>',
			'css'			=> '<link rel="stylesheet" type="text/css" href="{path}" data-group="{name}" />',
			'css_inline'	=> '<style type="text/css" data-group="{name}">{content}</style>',
		);
	}
}
$conf = new conf_bolt_load();