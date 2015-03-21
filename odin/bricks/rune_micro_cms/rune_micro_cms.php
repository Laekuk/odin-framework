<?php
#CreatedBy;Aaron;18MAR2015;Odin-Framework
class brick_rune_micro_cms extends _thunderbolt
{
	var $config;
	function __construct($conf)
	{
		global $odin;
		$this->config	= $conf;
		$this->config->base_dir	= dirname(__FILE__).'/';
		if(empty($this->config->admin_password))
			{ die('Please set an administrator password in your config file.'); }
		$this->_create_tables();
		$this->_install_cms();

		$class_name		= str_replace('brick_','',__CLASS__);
		$this->_odin_set_conf(
			array(
				'paths'				=> array(
					'lib'				=> $this->config->base_dir,
				),
				'prefix'			=> 'mortar_'.$class_name.'_',
			)
		);
	}

	function show()
	{
		global $odin;
		$page_name		= (isset($_GET['pagepath'])?$_GET['pagepath']:FALSE);
		if(empty($page_name))
		{
			$page	= $odin->qdb->get('rune_pages',[
				'order'		=> '`sort_order`',
				'limit'		=> 1,
				'wheres'	=> ['`active`=1'],
			]);
		}
		else
		{
			$page_name	= str_replace('/', NULL, $page_name);
			$page_sql	= 'SELECT * FROM `rune_pages` WHERE `active`=1 AND `name` LIKE ? ORDER BY LENGTH(`name`) LIMIT 1';
			$page		= $odin->sql->qry($page_sql,['%'.$page_name.'%']);
		}

		if(!is_array($page))
		{
			$page	= [[
				'name'		=> '404',
				'content'	=> '<p><strong>404</strong>, Page not Found.</p>',
			]];
		}
		$page		= current($page);

		$nav_pages		= $odin->qdb->get('rune_pages',[
			'wheres'		=> ['`active`=1'],
			'order'			=> '`sort_order`',
		]);
		$nav	= '';
		if(!empty($nav_pages))
		{
			$nav	.= '<nav><ul>';
			foreach($nav_pages as $item)
				{ $nav	.= '<li><a href="/'.$item['name'].'/">'.$item['name'].'</a>'; }
			$nav	.= '</ul></nav>';
		}

		$template	= file_get_contents($this->config->base_dir.'template/template.html');
		$html		= str_replace(['{content}','{name}','{nav}'], [$page['content'],$page['name'],$nav], $template);
		$snippets	= $odin->sql->qry('SELECT * FROM `rune_snippets` WHERE LENGTH(`value`)>0');
		if(is_array($snippets))
		{
			foreach($snippets as $k=>$v)
			{
				if(strpos($html, '{'.$v['name'].'}')!=FALSE)
					{ $html	= str_replace('{'.$v['name'].'}', $v['value'], $html); }
			}
		}
		die($html);
	}

	function admin_panel()
	{
		if(!isset($_SESSION))
			{ session_start(); }
		#build and return admin html
		if(empty($_GET['p']))
			{ $_GET['p']	= 'content'; }
		if(isset($_POST['login_password']))
			{ $_SESSION['odin_rune_micro_cms_pass']	= $_POST['login_password']; }
		if(isset($_SESSION['odin_rune_micro_cms_pass']) && $_SESSION['odin_rune_micro_cms_pass']===$this->config->admin_password)
		{
			switch($_GET['p'])
			{
				default:
				case 'content':
					$content	= $this->admin_pages->home();
				break;
				case 'template':
					$template_file	= $this->config->base_dir.'template/template.html';
					$content		= $this->admin_pages->template($template_file);
				break;
				case 'snippets':
					$content	= $this->admin_pages->snippets();
				break;
				case 'tools':
					$content	= 'Tools';
				break;
				case 'logout':
					unset($_SESSION['odin_rune_micro_cms_pass']);
					$content	= '<h1>Successfully logged out</h1><meta http-equiv="refresh" content="2; url=?p=content" />';
				break;
			}
			if(!isset($sidebar))
			{
				$sidebar	= $this->admin_pages->_sidebar();
			}
			$nav	=
				'<li><a href="?p=content">Content</a></li>
				<li><a href="?p=template">Template</a></li>
				<li><a href="?p=snippets">Snippets</a></li>
				<li><a href="?p=tools">Tools</a></li>
				<li><a href="?p=logout">Logout</a></li>';
		}
		else
		{
			$nav		= '<li><a href="#">Login</a></li>';
			$content	= (isset($_SESSION['odin_rune_micro_cms_pass'])?'<p>Invalid Password</p>':NULL).'<form action="'.$_SERVER['REQUEST_URI'].'" method="post"><ul><li>Password<input name="login_password" type="password" /></li></ul><button>Login</button></form>';
			$sidebar	= 'Powered by Rune Micro-CMS, a small brick in the <a href="https://github.com/Laekuk/odin-framework">Odin Framework</a>';
		}

		$html	=
'<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Rune Micro-CMS</title>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=Edge">
		<meta name="author" content="Laekuk">
		<meta name="description" content="Rune Micro-CMS Admin Panel">
		<meta name="viewport" content="width=device-width, initial-scale=1">
	</head>
	<body class="admin-content">
		<h1>Admin Panel</h1>
		<nav><ul>'.$nav.'</ul></nav>
		<section>'.$content.'</section>
		<aside>'.$sidebar.'</aside>

		<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
		<style>
		/* general styles */
			a{text-decoration:none;}
			.error{color:red;}
			.success{color:green;}
			.bold{font-weight:bold;}
			table{width:100%;}

		/* navigation styles */
			nav ul{list-style:none;height:24px;margin:0;padding:0;}
			nav ul li{float:left;padding:0 5px;}

		/* section styles */
			section{float:left;width:69%;}

			/* form styles */
				form ul{list-style:none;padding:0;margin:0;}
				.ft-text input,
				.ft-number input,
				textarea{width:100%;}
				textarea{height:120px;}
				form label .title{display:block;font-weight:bold;}
				.ft-checkbox .title{display:inline;}

		/* side styles */
			aside{float:right;width:30%;background-color:#DDD;}
			
		/* responsive styles */
		@media (max-width: 800px) {
			section{width: 100%;}
			aside{width: 100%;}
		}

		</style>

		<script src="//code.jquery.com/jquery-2.1.3.min.js"></script>
		<script src="//tinymce.cachefly.net/4.0/tinymce.min.js"></script>
		
		<script src="//cdnjs.cloudflare.com/ajax/libs/ace/1.1.8/ace.js"></script>

		<script>
			$(document).ready(function(){
				var html	= $(".f-HTML textarea");
				if(html.length>0)
				{
					html.hide().after("<div id=\"editor\"></div>");
					$("#editor").css("height","400px");
					var editor = ace.edit("editor");
					editor.getSession().setMode("ace/mode/html");
					editor.getSession().setValue(html.val());
					editor.getSession().on("change", function(){
						html.val(editor.getSession().getValue());
					});
				}
			});

		</script>

		<script>tinymce.init({
				selector: ".f-content textarea",
				entity_encoding : "raw",
				convert_urls: false,
				plugins: "advlist autolink lists link image charmap print preview anchor save searchreplace visualblocks code fullscreen insertdatetime media table paste",
				toolbar: "undo redo | styleselect | bold italic | fullscreen | bullist numlist | link unlink | code | removeformat",
				style_formats: [
					{"title":"Paragraph","block":"p"},
					{"title":"Heading 1","block":"h1"},
					{"title":"Heading 2","block":"h2"},
					{"title":"Heading 3","block":"h3"},
					{"title":"Heading 4","block":"h4"},
					{"title":"Address","block":"address"},
				],
				extended_valid_elements : "p[class],div[class|id]",
				extended_invalid_elements : "img,br",
				gecko_spellcheck : true,
				relative_urls : 0,
				remove_script_host : 1,
				height: 250,
				menubar: false
		});</script>
	</body>
</html>';
		return $html;
	}

	function _install_cms()
	{
		global $odin;
		$template_file	= $this->config->base_dir.'template/template.html';
		if(!file_exists($template_file))
		{
			if(!file_exists($this->config->base_dir.'template/'))
			{
				if(!is_writable($this->config->base_dir))
					{ die('Plugin directory ('.$this->config->base_dir.') is not writable. Please fix before proceeding.'); }
				mkdir($this->config->base_dir.'template/',0777);
			}
#			die($this->config->base_dir.'template/');
			
			file_put_contents($template_file,'<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Example: {name}</title>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=Edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
	</head>
	<body class="content">
		{content}
		<style></style>
		<script></script>
	</body>
</html>');
		}

		if(!file_exists($this->config->active_dir.'/.htaccess'))
		{
			if(!is_writable($this->config->active_dir))
				{ die('Base directory ('.$this->config->active_dir.') is not writable. Please fix before proceeding.'); }

			$base_path	= str_replace($_SERVER['DOCUMENT_ROOT'], NULL, $this->config->active_dir);


			file_put_contents($this->config->active_dir.'/.htaccess',
'#Rune Micro-CMS autocreated .htaccess file
RewriteEngine On
#Handle Homepage (always the lowest page)
RewriteRule ^()$ '.$base_path.'/rune.php [L,QSA]
RewriteRule ^(lol/)(.*)$ '.$base_path.'/rune.php [L,QSA]

#Handle Inside Pages
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-l
RewriteRule ^(.*)$ '.$base_path.'/rune.php?pagepath=$1 [L,QSA]');
		}
		
		if(!file_exists($this->config->active_dir.'/rune.php'))
		{
			if(!is_writable($this->config->active_dir))
				{ die('Base directory ('.$this->config->active_dir.') is not writable. Please fix before proceeding.'); }
			file_put_contents($this->config->active_dir.'/rune.php',
'<?php
require_once("'.$odin->conf->paths->base.'fury.php");
$odin->brick->rune_micro_cms->show();
');
		}
	}

	function _create_tables()
	{
		global $odin;
/*
		$pages	= $odin->sql->qry('DROP TABLE `rune_pages`');
		$pages	= $odin->sql->qry('DROP TABLE `rune_settings`');
		$pages	= $odin->sql->qry('DROP TABLE `rune_snippets`');
*/
		$tables	= [
			'rune_pages' =>
				'CREATE TABLE IF NOT EXISTS `rune_pages` (
					`content_id` INT(11) AUTO_INCREMENT,
					`name` VARCHAR(255),
					`content` LONGTEXT,
					`active` INT(1),
					`sort_order` INT(11),
					`created` DATETIME,
				
					PRIMARY KEY(`content_id`),
					INDEX(`name`),
					INDEX(`sort_order`),
					INDEX(active)
				) ENGINE=MyISAM;',

/*
			'rune_settings' =>
				'CREATE TABLE IF NOT EXISTS `rune_settings` (
					`setting_id` INT(11) AUTO_INCREMENT,
					`name` VARCHAR(255),
					`value` LONGTEXT,
					`modifiable` INT(1),
					`sort_order` INT(11),
				
					PRIMARY KEY(`setting_id`),
					INDEX(`name`),
					INDEX(`modifiable`),
					INDEX(`sort_order`)
				) ENGINE=MyISAM;',
*/

			'rune_snippets' =>
				'CREATE TABLE IF NOT EXISTS `rune_snippets` (
					`snippet_id` INT(11) AUTO_INCREMENT,
					`name` VARCHAR(255),
					`value` LONGTEXT,
					`created` DATETIME,
				
					PRIMARY KEY(`snippet_id`),
					INDEX(`name`)
				) ENGINE=MyISAM;',
		];
		$odin->sql->create_tables($tables);
	}

}