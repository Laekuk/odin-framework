<?php
#CreatedBy;Aaron;19OCT2014;Odin-Framework
/*
	A lot of this is going to be MySQL-Specific for now, since that is what I'm most familiar with.
	If anyone wants to chip-in their experteice, it would be greatly appreciated.			<3
	I want this to eventually span any database that the PDO bolt can access.
*/
class bolt_crud
{
	#pull down columns from a table and display them as a form, end-user may then insert a new record.
	function create($table,$opts=NULL)
	{
		global $odin;
		$o	= array(
			"form_labels"	=> array("Add"),	#the form's label
			"supress_cols"	=> NULL,			#an array of columns to hide on the insert form
			"locked_vals"	=> NULL,			#an array of columns & their forced values. This also hides the columns by default (overwritten by col_types)
			"defaults"		=> NULL,			#an array of columns & their default values, which overwrites the database-default-guesses
			"col_types"		=> NULL,			#an array of column types, which overwrite the database-default-guesses
			"col_options"	=> NULL,			#an array of column options, which overwrites the database-default-guesses
			"col_rules"		=> NULL,			#set to overwrite the database-default-guesses for validation rules
			"instance"		=> NULL,			#set to manually create the id for this form.
			"headings"		=> NULL,			#set to overwrite the database-default-guesses for column headings
			"new_set_on"	=> NULL,			#an array of fields to break the fieldsets up on.
			"foreign_keys"	=> NULL,			#an array("field"=>array("sql"=>"SELECT * FROM `other_table` WHERE `field`>$value",array(":field"=>5))
				#Pulls data from that other_table and loads it into this field's options.
		);
		if($opts)
			{ $o	= $odin->array->ow_merge_r($o,$opts); }
		#loose cleanse against possible injections.
		$table	= $odin->str->alpha_num($table);
		$sql	= "DESCRIBE `$table`";
		$fields	= $odin->sql->qry($sql,NULL,"Field");
		$fields	= $this->parse_fields($fields);
		if($o["defaults"])
			{ $fields["fields"]	= $odin->array->ow_merge_r($fields["fields"],$o["defaults"]); }
		if($o["col_types"])
			{ $fields["types"]	= $odin->array->ow_merge_r($fields["types"],$o["col_types"]); }
		if($o["headings"])
			{ $fields["types"]	= $odin->array->ow_merge_r($fields["headings"],$o["headings"]); }
		if($o["col_options"])
			{ $fields["options"]	= $odin->array->ow_merge_r($fields["options"],$o["col_options"]); }

		if($o["col_rules"])
			{ $fields["rules"]	= $odin->array->ow_merge_r($fields["rules"],$o["col_rules"]); }
		/*
			if there is any post, handle it here.
		*/
		$form	= $odin->form->create($fields["fields"],array(
			"submit_text"	=> "Add",
			"legends"		=> $o["form_labels"],
			"new_set_on"	=> $o["new_set_on"],
			"instance"		=> $o["instance"],
			"field_types"	=> $fields["types"],
			"field_opts"	=> $fields["options"],
		));
		
		return $form;
	}

	#pull down rows from a table and display them, options to allow the end-user to edit & delete those rows.
	function manage($table,$opts=NULL)
	{
		$o	= array(
			"form_label"	=> "Data",	#the form's label
			"supress_cols"	=> NULL,	#an array of columns to hide on the table (not hidden on the edit-form)
			"locked_vals"	=> NULL,	#an array of columns & their forced values. This also hides the columns by default (overwritten by col_types)
			"col_types"		=> NULL,	#an array of column types, which overwrite the database-default-guesses
				#For options, se an array("field"=>array("type"=>"select","options"=>array("key"=>"value")))
			"col_rules"		=> NULL,	#set to overwrite the database-default-guesses for validation rules
			"instance"		=> NULL,	#set to manually create the id for this form.
			"headings"		=> NULL,	#set to overwrite the database-default-guesses for column headings
			"foreign_keys"	=> NULL,	#an array("field"=>array("sql"=>"SELECT * FROM `other_table` WHERE `field`>$value",array(":field"=>5))
				#Pulls data from that other_table and loads it into this field's options.
			"edit"			=> TRUE,	#enables the page to be reloaded as an edit with a $_GET
			"delete"		=> NULL,	#set to true to allow the user to delete rows
			"hide_pk"		=> TRUE,	#disable to display the primary key as read-only
			"order_by"		=> NULL,	#string to force a sort-order of the table
			"extra_fields"	=> NULL,	#an array of extra fields you might want to throw in
		);
	}
	
	function parse_fields($fields)
	{
		global $odin;
		$field		= NULL;
		$type		= NULL;
		$rules		= NULL;
		$default	= NULL;
		if(empty($fields))
			{ return FALSE; }

		foreach($fields as $k=>$v)
		{
			$field[$k]		= NULL;
			$heading[$k]	= ucwords($odin->str->alpha_num($k));
			if($v["Key"]=="PRI")
				{ $primary	= $k; }
			#parse the field's data-type & information.
			$field_type	= preg_split('/\(/', $v["Type"],2);
			
			$field_info	= (isset($field_type[1])?preg_split("/\,/", substr($field_type[1], 0,-1)):NULL);
			$field_type	= $field_type[0];
			switch($field_type)
			{
				default:
				break;
				case "int";
					$field_info	= $field_info[0];
					#treat this like a boolean by default.
					if($field_info>1)
					{
						$type[$k]	= "number";
						$rules[$k]	= array("is_numeric");
					}
					else
						{ $type[$k]	= "checkbox"; }
				break;
				case "enum":
					$type[$k]		= "select";
					$field_opts	= NULL;
					foreach($field_info as $info_key=>$info_val)
					{
						$info_val			= substr($info_val, 1,-1);
						$field_opts[$info_val]	= $info_val;
					}
					$options[$k]	= $field_opts;
				break;
				case "text":
				case "longtext":
					$type[$k]		= "textarea";
					$field[$k]		= implode(" ", $lor);
				break;
			}
			if($v["Default"])
				{ $field[$k]	= $v["Default"]; }
		}
		return array(
			"primary"	=> $primary,
			"fields"	=> $field,
			"types"		=> $type,
			"headings"	=> $heading,
			"options"	=> $options,
			"rules"		=> $rules,
		);
	}
}