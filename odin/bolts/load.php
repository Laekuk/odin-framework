<?
#CreatedBy;Weston;02NOV2015;Odin-Framework
// This bolt stores js/css files in memory intended to be included in a template
// Usage:

// Basic: 			->js(file with or without extension)
// in your controller:
//		$odin->load->js('jquery');		// loads jquery from the js directory set in conf
// in the template file: 
//		echo $odin->load->write('js');	// writes all loaded js in order

// Advanced: 		->css('path to file.css','group name',sort order);
// in your controller:
// 		$odin->load->css('libs/fontawesome', 'libraries', 2);
// 		$odin->load->css('libs/bootstrap', 'libraries', 1);
// 		$odin->load->css('layout.php', 'default', 1);
//	in the template:
//		echo $odin->load->write('css','libraries');	// will load this group first in order
//		echo $odin->load->write('css');				// loads the remaining groups in order
class bolt_load
{
	function __construct($conf)
	{
		global $odin;
		$this->conf			= $conf;
		$this->loads		= (object)array();
		$this->caches		= array();
		$this->loaded		= array();
		$this->ids 			= array();
		// cleanup cache
		if(isset($this->conf->cache,$this->conf->cache_time))
		{
			$files	= $odin->filesystem->get_files($this->conf->path->cache_files,['js','css']);
			if(!empty($files))
			{
				$time = time();
				foreach($files as $file)
				{
					if(file_exists($file) && ($time-fileatime($file)) > $this->conf->cache_time)
						{ unlink($file); }
				}
			}
		}
		// force remove all cached files
		if(!empty($_GET['clearcache']))
		{
			$files	= $odin->filesystem->get_files($this->conf->path->cache_files,['js','css']);
			$odin->filesystem->remove_files($files);
		}
	}
	
	function js($path,$name="default",$order=10,$template=false)
		{ return $this->load('js',$path,$name,$order,$template); }
	function css($path,$name="default",$order=10,$template=false)
		{ return $this->load('css',$path,$name,$order,$template); }
	function is_inline($path)
		{ return preg_match('/[<>\|\*\{\}\(\)\n\r\;\'\"]/', trim($path)); }
	function is_external($path)
		{ return substr($path,0,2)=='//' || substr($path,0,5)=='http:' || substr($path,0,6)=='https:'; }
	
	function load($type,$path,$name,$order,$template=false)
	{
/*		$type		: type of file, like js / css
		$path		: the path to, or the file contents
		$name,		: the group name for sorting and grouping files
		$order		: the order of the file in the group
		$template	: the tag template from the config
*/
		#If the $path is an array, attempt to load from all paths individually, then return FALSE if any of them failed.
		if(is_array($path))
		{
			$r = true;
			foreach($path as $p)
				{ if(!$this->load($type,$p,$name,$order,$template)) { $r=false; } }
			return $r;
		}
		#If $path is a snippet of inline string of CSS or JS, handle it as inline.
		if($this->is_inline($path))
		{
			$type = $type.'_inline';
			if(empty($template))
				{ $template = $type; }
			if(!isset($this->loads->{$type}))
				{ $this->loads->{$type} = array(); }
			if(!isset($this->loads->{$type}[$name]))
				{ $this->loads->{$type}[$name] = array(); }
			$this->loads->{$type}[$name][] = array(
				'content'	=> $path,
				'order'		=> $order,
				'name'		=> $name,
				'template'	=> $template,
				'external'	=> false,
			);
			return true;
		}

		#If no template was passed, use $type as the template.
		if(empty($template))
			{ $template = $type; }
		#Find the location of the file, use the config location and the absolute path
		$file		= $this->find($path,$type);
		$external	= $this->is_external($file);
		#If a file can be found to load, handle it, otherwise don't load anything and return false.
		if($file)
		{
			$id = false;
			// if this file is not external look for a load id in the comments
			if(!$external)
				{ $id = $this->load_id($file); }

			#If we have a file id generated & the file id is already set to load, stop here & do not load this file.
			if($id)
			{
				#If already ready to load, return true since it should be good to load.
				if(in_array($id,$this->ids))
					{ return true; }
				$this->ids[] = $id;
			}

			// If this file is NOT already set to be loaded, add it into $this->loads for its ->$type.
			if(!in_array($file,$this->loaded))
			{
				$this->loaded[]	= $file;
				$this->loads->{$type}[$name][] = array(
					'path'		=> str_replace($_SERVER['DOCUMENT_ROOT'],'',$file),
					'order'		=> $order,
					'name'		=> $name,
					'template'	=> $template,
					'external'	=> $external,
				);
			}
			#Successfully load-able, return true.
			return true;
		}
		return false;
	}
	function find($path,$type)
	{
		if($this->is_external($path))
			{ return $path; }
		if(file_exists($path))
			{ $path	= str_replace('\\', '/', $path);}
		else
		{
			$p 			= $path;
			$rootlen	= strlen($this->conf->path->root);
			if(substr($p,0,$rootlen)!=$this->conf->path->root)
				{ $p = str_replace(['//','\\\\'],['/','\\'],$this->conf->path->root.$p);}
			if(!file_exists($p))
			{
				if(substr($path,0,1) == '/')
					{ $path = substr($path,1); }
				$locations = array($this->conf->path->base.$path,"/$type/");
				if(isset($this->conf->path->{$type}))
				{
					if(is_string($this->conf->path->{$type}))
						{ $this->conf->path->{$type} = array($this->conf->path->{$type}); }
					$locations = array_merge($locations,$this->conf->path->{$type});
				}
				$found = false;
				foreach($locations as $p)
				{
					if(substr($p,0,$rootlen)!=$this->conf->path->root)
						{ $p = str_replace('//','/',$this->conf->path->root.$p);}
					if(substr($p,-1)!='/')
						{ $p .= '/'; }
					$p = $p.$path;
					if(file_exists($p))					{ $found = $p; }
					elseif(file_exists($p.'.'.$type)) 	{ $found = $p.'.'.$type; }
					elseif(file_exists($p.'.php')) 		{ $found = $p.'.php'; }
					if($found)
						{ break; }
				}
				$path = $found;
			}
		}
		return $path;
	}

	function item_sort($a,$b)
		{ return (float)$a['order'] >= (float)$b['order']; }

	function order_items($items)
		{ usort($items,array($this,'item_sort')); return $items; }

	function write($type,$name=false)
	{
		global $odin;
		$ext = $type;
/*		$type is the same as $this->load(), meaning its something like "js" or "css".
		$name is the grouping. If left blank, we will write all groups.
*/
		$items = array();
		$types = array($type, $type.'_inline');
		#Loop through the types (inline & files). and build up $items array.
		foreach($types as $type)
		{
			#If we have any files in this type, continue
			if(!empty($this->loads->{$type}))
			{
				if($name)
				{
					if(!empty($this->loads->{$type}[$name]))
					{
						$items = array_merge($items,$this->loads->{$type}[$name]);
						unset($this->loads->{$type}[$name]);
					}
				}
				else
				{
					foreach($this->loads->{$type} as $k=>$v)
						{ if(!empty($v)) { $items = array_merge($items,$v); } }
					#Unset all files of this type from the loads object.
					unset($this->loads->{$type});
				}
			}
		}
		if(empty($items))
			{ return false; }
		if(!empty($this->conf->cache))
		{
			$content	= [];
			$ids		= [];
			$group_name = false;
			$group_files= [];
			$externals 	= [];
			foreach($items as $k=>$item)
			{
				if(!empty($item['external']))
				{
					$externals[] = $item;
					continue;
				}
				if(!empty($item['name']))
					{ $group_name = $item['name']; }
				else
					{ $group_name = 'default'; }
				$item['name'] = $group_name;
				if(!empty($item['path']))
				{
					if(!isset($group_files[$group_name]))
						{ $group_files[$group_name] = []; }
					$c 							= $item['path'].filemtime($_SERVER['DOCUMENT_ROOT'].$item['path']);
					$group_files[$group_name][]	= basename($item['path']);
				}
				elseif(!empty($item['content']))
					{ $c = $item['content']; }
				if(!isset($ids[$group_name]))
					{ $ids[$group_name] = []; }
				$ids[$group_name][] = md5($c);
			}
			if(!file_exists($this->conf->path->cache_files))
				{ mkdir($this->conf->path->cache_files,0755,true); }
			#Loop through all IDs.
			$cachepaths		= [];
			$cached_items	= [];
			foreach($ids as $group_name => $hashes)
			{
				$id			= md5(implode(":", $hashes));
				$group_name	= empty($group_name) ? 'inline' : $group_name;
				$cachepath	= $this->conf->path->cache_files.$group_name.'.'.$id.'.'.$ext;
				if(!file_exists($this->conf->path->cache_files))
					{ mkdir($this->conf->path->cache_files,0755,true); }

				if(!file_exists($cachepath))
				{
					$content = '';
					#Loop through items
					foreach($items as $k=>$item)
					{
						if($item['name'] == $group_name)
						{
							if(!empty($item['external']))
								{ continue; }
							if(empty($content) && !empty($group_files[$group_name]))
								{ $content = "/* Contents: \n *    ".implode("\n *    ",$group_files[$group_name])."\n */\n"; }
							
							$c = $this->item_content($item);
							$ids[] = md5($c);
							if(!empty($item['path']))
								{ $content .= "/* File: ".$item['path']." */ \n"; }
							elseif(!empty($item['content']))
								{ $content .= "/* Inline Code */ \n"; }
							$content .= $c;
							$content .= "\n";
							$content .= "\n";
						}
					}
					// generate cache file
					file_put_contents($cachepath, $content);
				}
				// now if that file is there load it as items
				if(file_exists($cachepath))
				{
					$first_item = false;
					foreach($items as $k=>$item)
						{ if($item['name'] == $group_name){ $first_item = $item; break; } }
					$templt = !empty($first_item['template']) ? $first_item['template'] : $ext;
					$templt = str_replace("_inline", "", $templt);
					$cached_items[] = [
						'template'	=> $templt,
						'name'		=> $group_name,
						'order'		=> $first_item['order'],
						'path'		=> str_replace($_SERVER['DOCUMENT_ROOT'], '', $cachepath),
					];
					
					$this->caches[] = $cachepath;
					
				}
			}
			if(!empty($cached_items))
			{
				$items = $cached_items;
			}
			if(!empty($externals))
			{
				$items = array_merge($externals,$items);
			}
			
			
		}
		#Loop through the types of files and build up $items.
		if(!empty($this->conf->inline))
		{
			$content = '';
			$external = [];
			foreach($items as $k=>$item)
			{
				if($item['external'])
					{ $external[] = $this->write_template_string($item); unset($item[$k]); }
				else
				{
					$content .= $this->item_content($item);
					$content .= "\n";
				}
			}
			$type	= str_replace('_inline','',$items[0]['template']).'_inline';
			$new_items = '';
			if(!empty($external))
				{ $new_items .= "\n\t".implode("\n\t",$external)."\n\t"; }
			$new_items	.= "\n".$odin->str->replace($this->conf->template->{$type},array_merge($items[0],array('content' => $content)))."\n";
			$items = $new_items;
		}
		else
		{
			// insert items into templates
			foreach($items as $k=>$item)
				{ $items[$k] = $this->write_template_string($item); }
			// populate template
			$items = "\n\t".implode("\n\t",$items)."\n\t";
		}
		return $items;
	}
	function write_template_string($item)
	{
		global $odin;
		$template = !empty($item['template']) && !empty($this->conf->template->{$item['template']}) ? $this->conf->template->{$item['template']} : $this->conf->template->{$ext};
		return $odin->str->replace($template,$item);
	}
	function item_content($item)
	{
		$content = '';
		
		if(!empty($item['path']) && !$item['external'])
		{
			if(substr($item['path'], -4) == '.php')
			{
				ob_start();
				include($_SERVER['DOCUMENT_ROOT'].$item['path']);
				$content .= ob_get_clean();
			}
			else
				{ $content .= file_get_contents($_SERVER['DOCUMENT_ROOT'].$item['path']); }
		}
		elseif(!empty($item['content']))
			{ $content .= $item['content']; }
		return $content;
	}
	function load_id($path)
	{
		$load_id = false;
		if(file_exists($path))
		{
			$d = file_get_contents($path);
			if(!empty($d) && preg_match('/@loadid[\s+\=]([a-z0-9_\-\.]+)/i', $d, $m))
				{ $load_id = $m[1]; }
			unset($d);
		}
		return $load_id;
	}
}