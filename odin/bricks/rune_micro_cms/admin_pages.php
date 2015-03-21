<?php
class mortar_rune_micro_cms_admin_pages
{
	function home()
	{
		global $odin;
		#attempt to get a page_id from the URL
		$page_id	= (isset($_GET['id'])?$_GET['id']:1);
		#create a new page
		if($page_id=='new')
		{
			$page_id	= $this->_create_page();
			header('Location: ?p=content&id='.$page_id);
			die();
		}
		#Show the update form.
		$edit_form	= $odin->crud->single('rune_pages',[
			'id'		=> $page_id,
			'success_msg'	=> 'Page Successfully Saved',
			'col_types'	=> [
				'created'	=> 'label'
			],
			'col_rules'	=> [
				'created'	=> [[
					'skip'=>TRUE
				]],
				'name'		=> [[
					'required'		=> TRUE,
				]],
			]
		]);

		return $edit_form;
	}

	function template($template_file)
	{
		global $odin;
		if(!is_writable($template_file))
			{ return '<p class="error">Your template file ('.$template_file.') is not writable.</p>'; }
		$form_opts	= [
			'instance'		=> 'edit-template',
			'legends'		=> ['Update Template'],
			'submit_text'	=> 'Save Template',
			'field_types'	=> ['HTML'=>'textarea'],
		];
		if(!empty($_POST['edit-template']['HTML']))
		{
			$content	= $_POST['edit-template']['HTML'];
			file_put_contents($template_file, $content);
			$form_opts['messages']['_general']	= 'Template Updated';
			
		}
		$content	= file_get_contents($template_file);
		$form	= $odin->html->form(['HTML'=>$content],$form_opts);
		return $form;
	}

	function snippets()
	{
		global $odin;
		$snippet_id	= (isset($_GET['snippet_id'])?$_GET['snippet_id']:FALSE);
		if($snippet_id=='new')
		{
			$snippet_id	= $odin->qdb->insert('rune_snippets',[
				'name'		=> 'New Snippet',
				'created'	=> date('Y-m-d H:i:s'),
			]);
			header('Location: ?p=snippets&snippet_id='.$snippet_id);
			die();
		}
		if(empty($snippet_id))
			{ $snippet_html	= $this->_list_snippets(); }
		else
		{
			$snippet_html	= $odin->crud->single('rune_snippets',[
				'id'			=> $snippet_id,
				'success_msg'	=> 'Snippet Successfully Saved',
				'col_types'		=> [
					'created'		=> 'label'
				],
				'col_rules'		=> [
					'name'			=> [['required']],
				]
			]);
		}
		return $snippet_html;
	}

	function tools()
	{
		return 'Tools Content';
	}
	
	function _create_page($name='New Page',$sort_order=4)
	{
		global $odin;
		$sort_order		= (int)$sort_order;
		if($sort_order<1)
			{ $sort_order	= 10; }
		return $odin->qdb->insert('rune_pages',[
			'name'		=> $name,
			'created'	=> date('Y-m-d H:i:s'),
			'sort_order'=> $sort_order,
		]);
	}

	function _sidebar()
	{
		global $odin;
		return $this->_list_pages().$this->_list_snippets();
	}

	function _list_pages()
	{
		global $odin;
		$page_id	= (isset($_GET['id'])?$_GET['id']:0);
		$pages_sql	= 'SELECT `content_id`,`name` AS "Name",`active`,`created` AS "Created" FROM `rune_pages` ORDER BY `sort_order`,`name`';
		$pages		= $odin->sql->qry($pages_sql);
		#auto-creation of the homepage
		if(!is_array($pages))
		{
			$page_id	= $this->_create_page('Home',1);
			header('Location: ?p=content&id='.$page_id);
			die();
		}

		$first	= TRUE;
		foreach($pages as &$page)
		{
			$page['Name']		= ($page['active']>0?'<i class="fa fa-file-text-o success"></i>':'<i class="fa fa-ban error"></i>').
				' <a '.($page_id==$page['content_id']?'class="bold" ':NULL).'href="?p=content&id='.$page['content_id'].'">'.$page['Name'].'</a>'.
				($page['active']>0 && $first?' <i class="fa fa-home" title="Homepage"></i>':NULL);
			$page['Created']	= date('jMY, g:ia',strtotime($page['Created']));
			if($page['active']>0)
				{ $first		= FALSE; }
		}
		$page_links	= $odin->html->table($pages,[
			'caption'	=> 'Website Pages',
			'skip_cols'	=> [
				'content_id',
				'active',
			],
		]);

		return '<p><i class="fa fa-plus success"></i> <a href="?p=content&id=new">Create New Page</a></p>'.$page_links;
	}

	function _list_snippets()
	{
		global $odin;
		$page_id	= (isset($_GET['snippet_id'])?$_GET['snippet_id']:0);
		$snippet_str	= '<p><i class="fa fa-plus success"></i> <a href="?p=snippets&snippet_id=new">Create New Snippet</a></p>';
		$snippets		= $odin->qdb->get('rune_snippets');
		if(!is_array($snippets))
			{ return $snippet_str.'<p class="error">No Snippets have been created yet.</p>'; }
		foreach($snippets as &$snip)
		{
			$snip['Usage']	= '{'.$snip['name'].'}';
			$snip['name']	= '<i class="fa fa-pencil-square-o success"></i> <a '.($page_id==$snip['snippet_id']?'class="bold" ':NULL).'href="?p=snippets&snippet_id='.$snip['snippet_id'].'">'.$snip['name'].'</a>';
		}
		$snippet_str	.= $odin->html->table($snippets,[
			'caption'	=> 'Snippets',
			'skip_cols'	=> [
				'snippet_id',
				'created',
				'value',
			],
			'headings'	=> [
				'name'	=> 'Name'
			],
		]);
		return $snippet_str;
	}
}