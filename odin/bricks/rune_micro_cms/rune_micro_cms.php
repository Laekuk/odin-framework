<?php
#CreatedBy;Aaron;18MAR2015;Odin-Framework
/*
	Basic Setup Example:
		#Create a file on your web server which is already running odin, then include the odin framework, and write the following code onto that page:
		echo $odin->brick->rune_micro_cms->admin_panel();

	Modify this brick's config to set a password (users brick coming in the future to midegate this), then navigate to that page you made.
	The brick will write all of the nesseary template files and database tables to get you started.
*/
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

	function show($page_name=FALSE,$scripts_dir=FALSE)
	{
		global $odin;
		if(empty($page_name))
			{ $page_name	= (isset($_GET['pagepath'])?$_GET['pagepath']:FALSE); }
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
			$page_name	= str_replace('/', NULL, rawurldecode($page_name));
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
			$nav	.= '<ul class="nav">';
			foreach($nav_pages as $item)
				{ $nav	.= '<li class="page-'.$item['content_id'].'"><a href="/'.rawurlencode($item['name']).'/">'.$item['name'].'</a>'; }
			$nav	.= '</ul>';
		}

		$template_dir	= $this->config->base_dir.'template/';
		$template		= file_get_contents($template_dir.'template.html');
		$template_dir	= str_replace($_SERVER['DOCUMENT_ROOT'], '',$template_dir);
		if(empty($scripts_dir))
			{ $scripts_dir	= $template_dir; }
		$html_name		= strtolower(preg_replace('/[^0-9a-zA-Z ]/m', ' ', $page['name']));
		$html_name		= preg_replace("/ /", "-", $html_name);
		$html			= str_replace([
			'{content}',
			'{name}',
			'{html_name}',
			'{nav}',
			'{css}',
			'{js}',
		],[
			$page['content'],
			$page['name'],
			$html_name,
			$nav,
			$scripts_dir.'styles.css',
			$scripts_dir.'scripts.js',
		], $template);
		$snippets	= $odin->sql->qry('SELECT * FROM `rune_snippets` WHERE LENGTH(`value`)>0');
		if(is_array($snippets))
		{
			foreach($snippets as $k=>$v)
			{
				if(strpos($html, '{'.$v['name'].'}')!=FALSE)
					{ $html	= str_replace('{'.$v['name'].'}', $v['value'], $html); }
			}
		}
		return $html;
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
					$template_dir	= $this->config->base_dir.'template/';
					$content		= $this->admin_pages->template($template_dir);
				break;
				case 'snippets':
					$content	= $this->admin_pages->snippets();
				break;
				case 'tools':
					$content	= $this->tools();
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
			*{font-weight:bold;}
			h1{margin:0;text-align: center;}
			a{text-decoration:none;}
			table{width:100%;}
			.ace_editor{border:1px solid;}

		/* color styles */
			body{background-color:#EEDDBB}
			nav,aside{background-color:#A1916F}
			a{color:#003387;}
			.error{color:#95130C;font-weight:bold;}
			.success{color:#008733;font-weight:bold;}
			.bold{font-weight:bold;}

		/* navigation styles */
			nav{border-radius: 3px 3px 0 3px;}
			nav ul{list-style:none;height:24px;margin:0;padding:10px 0 0 0;}
			nav ul li{float:left;padding:0 10px;}

		/* section styles */
			section{float:left;width:68%;margin-top:10px;}

			/* form styles */
				form ul{list-style:none;padding:0;margin:0;}
				.ft-text input,
				.ft-number input,
				textarea{width:100%;}
				textarea{height:120px;}
				form label .title{display:block;font-weight:bold;}
				.ft-checkbox .title{display:inline;}

		/* side styles */
			aside{float:right;width:30%;border-radius:0 0 3px 3px;padding:0 5px 5px 5px}

			/* table styles */
				aside tr{height:24px;}

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

				var css		= $(".f-CSS textarea");
				if(css.length>0)
				{
					css.hide().after("<div id=\"css-editor\"></div>");
					$("#css-editor").css("height","300px");
					var css_editor = ace.edit("css-editor");
					css_editor.getSession().setMode("ace/mode/css");
					css_editor.getSession().setValue(css.val());
					css_editor.getSession().on("change", function(){
						css.val(css_editor.getSession().getValue());
					});
				}

				var js		= $(".f-JavaScript textarea");
				if(js.length>0)
				{
					js.hide().after("<div id=\"js-editor\"></div>");
					$("#js-editor").css("height","300px");
					var js_editor = ace.edit("js-editor");
					js_editor.getSession().setMode("ace/mode/javascript");
					js_editor.getSession().setValue(js.val());
					js_editor.getSession().on("change", function(){
						js.val(js_editor.getSession().getValue());
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

	function tools()
	{
		global $odin;
		if(isset($_GET['export']))
		{
			$template_dir	= $this->config->base_dir.'/template/';
			switch($_GET['export'])
			{
				default:
					return 'Error: that is not a valid export type.';
				break;
				case 'html';
					if(class_exists('ZipArchive'))
					{
						$first			= TRUE;
						$export_file	= $template_dir.'export.zip';
						$pages			= $odin->qdb->get('rune_pages',[
							'order'			=> '`sort_order`',
							'wheres'		=> ['`active`=1'],
						]);
						$zip			= new ZipArchive;
						if(!empty($pages))
						{
							foreach($pages as $page)
							{
								$file_contents	= $this->show($page['name'],'/');
								$tmpfname	= tempnam('/tmp', 'rune');
								$handle		= fopen($tmpfname, "w");
								fwrite($handle, $file_contents);
								fclose($handle);
								if($first)
								{
									#make (or empty) the file.
									file_put_contents($export_file, '');
									if($zip->open($export_file) === FALSE)
										{ die('Error, Cannot write to export file ('.$export_file.')'); }
									$zip->addFile($tmpfname,'index.html');
								}
								$first	= FALSE;
								$zip->addFile($tmpfname,'/'.$page['name'].'/index.html');
								#unlink($tmpfname);
							}

							#add styles.css file
							$zip->addFile($template_dir.'styles.css','/styles.css');

							#add scripts.js
							$zip->addFile($template_dir.'scripts.js','/scripts.js');
						}
						$zip->close();
						header('Location: '.str_replace($_SERVER['DOCUMENT_ROOT'], '', $export_file));
						die();
					}
				break;
				case 'json';
						$export		= [
							'page_content'	=> $odin->qdb->get('rune_pages'),
							'snippets'		=> $odin->qdb->get('rune_snippets'),
							'template'		=> [
								'html'			=> file_get_contents($template_dir.'template.html'),
								'css'			=> file_get_contents($template_dir.'styles.css'),
								'js'			=> file_get_contents($template_dir.'scripts.js'),
							]
						];
						$export			= json_encode($export);
						$export_file	= $template_dir.'export.json';
						file_put_contents($export_file, $export);
				break;
			}
			if(isset($export_file) && !empty($export_file))
			{
				header('Location: '.str_replace($_SERVER['DOCUMENT_ROOT'], '', $export_file));
				die();
			}
			die('Error creating export file.');
		}
		if(class_exists('ZipArchive'))
			{ $zip_text	= 'Export as <a download="Website.zip" href="?p=tools&export=html">HTML</a> (zip file)'; }
		else
			{ $zip_text	= 'Cannot Export, since <a href="https://php.net/manual/en/zip.examples.php">ZipArchive</a> is not supported.'; }
		$tools_html	= '<h2>Export Website</h2><ul>
			<li>'.$zip_text.'</li>
			<li>Export as <a download="Website.json" href="?p=tools&export=json">JSON</a></li>
		</ul>';
		return $tools_html;
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

		#Create CSS File.
		$css_file	= $this->config->base_dir.'template/styles.css';
		if(!file_exists($css_file))
			{ file_put_contents($css_file,'/* CSS File. Use {css} to inject into your HTML template. */
'); }

		#Create JS File.
		$js_file	= $this->config->base_dir.'template/scripts.js';
		if(!file_exists($js_file))
			{ file_put_contents($js_file,'/* JavaScript File. Use {js} to inject into your HTML template. */
'); }

		if(!file_exists($this->config->active_dir.'/.htaccess'))
		{
			if(!is_writable($this->config->active_dir))
				{ die('Base directory ('.$this->config->active_dir.') is not writable. Please fix before proceeding.'); }

			$base_path	= str_replace($_SERVER['DOCUMENT_ROOT'], NULL, $this->config->active_dir);


			file_put_contents($this->config->active_dir.'/.htaccess',
'#Rune Micro-CMS autocreated .htaccess file
#Compress Transfers
<ifModule mod_gzip.c>
mod_gzip_on Yes
mod_gzip_dechunk Yes
mod_gzip_item_include file .(html?|txt|css|js|php|pl)$
mod_gzip_item_include handler ^cgi-script$
mod_gzip_item_include mime ^text/.*
mod_gzip_item_include mime ^application/x-javascript.*
mod_gzip_item_exclude mime ^image/.*
mod_gzip_item_exclude rspheader ^Content-Encoding:.*gzip.*
</ifModule>
<IfModule mod_deflate.c>
AddOutputFilterByType DEFLATE text/text text/html text/plain text/xml text/css application/x-javascript application/javascript
</IfModule>

#URL Redirection
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
echo $odin->brick->rune_micro_cms->show();
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