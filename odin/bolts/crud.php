<?php
#CreatedBy;Aaron;19OCT2014;Odin-Framework
/*
	A lot of this is going to be MySQL-Specific for now, since that is what I'm most familiar with.
	If anyone wants to chip-in their experteice, it would be greatly appreciated.			<3
	I want this to eventually span any database that the PDO bolt can access.
*/
class bolt_crud
{
	var $num_instances	= 0;
	#pull down columns from a table and display them as a form, end-user may then insert a new record.
	function create($table,$opts=NULL)
	{
		global $odin;
		$this->num_instances++;
		$instance	= (isset($o["instance"])?$o["instance"]:"crud-inst-".$this->num_instances);
		$o	= array(
			"form_labels"	=> array("Add"),	#the form's label
			"supress_cols"	=> NULL,			#an array of columns to hide on the insert form
			"locked_vals"	=> NULL,			#an array of columns & their forced values. This also hides the columns by default (overwritten by col_types)
			"defaults"		=> NULL,			#an array of columns & their default values, which overwrites the database-default-guesses
			"col_types"		=> NULL,			#an array of column types, which overwrite the database-default-guesses
			"col_options"	=> NULL,			#an array of column options, which overwrites the database-default-guesses
			"col_rules"		=> NULL,			#set to overwrite the database-default-guesses for validation rules. Acceptable formats are:
/*				This will validate if the posted value is numeric, if it is not it will return the error message "Must be a Number"
				array("field"=>array("function_name"	=> "is_numeric",	"error"	=> "Must be A Number"))

				This will run the alpha_num() method inside of the str bolt (passing the posted value to that method as the first paramiter),
					then replace whatever was posted with the alpha_num
				array(
					"field"=>array(
						"bolt"					=> "str",
						"method"				=> "alpha_num",
						"use_returned_value"	=> TRUE,
					)
				)

				This will validate if the posted value is numeric, if it is not it will set the posted value to an int of 0
				array("field"=>array("function_name"	=> "is_numeric",	"fix_value"	=> 0))

				This will overwrite your posted data's value with "Tested"
				array("field"=>array("set"				=> "Tested"))
*/
			"instance"		=> $instance,		#set to manually create the id for this form.
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
		
		if(isset($_REQUEST[$instance]))
		{
			$data	= $_REQUEST[$instance];
			$info	= $this->validate_data($data,$fields);
			var_dump($info);
			die();
		}
		$form_opts	= array(
			"instance"		=> $o["instance"],
			"submit_text"	=> "Add",
			"legends"		=> $o["form_labels"],
			"new_set_on"	=> $o["new_set_on"],
			"instance"		=> $o["instance"],
			"field_types"	=> $fields["types"],
			"field_opts"	=> $fields["options"],
		);

		$form	= $odin->form->create($fields["fields"],$form_opts);
		
		return $form;
	}

	#pull down rows from a table and display them, options to allow the end-user to edit & delete those rows.
	function manage($table,$opts=NULL)
	{
		$this->num_instances++;
		$instance	= (isset($o["instance"])?$o["instance"]:"crud-inst-".$this->num_instances);
		$o	= array(
			"form_label"	=> "Data",		#the form's label
			"supress_cols"	=> NULL,		#an array of columns to hide on the table (not hidden on the edit-form)
			"locked_vals"	=> NULL,		#an array of columns & their forced values. This also hides the columns by default (overwritten by col_types)
			"col_types"		=> NULL,		#an array of column types, which overwrite the database-default-guesses
				#For options, se an array("field"=>array("type"=>"select","options"=>array("key"=>"value")))
			"col_rules"		=> NULL,		#set to overwrite the database-default-guesses for validation rules
			"instance"		=> $instance,	#set to manually create the id for this form.
			"headings"		=> NULL,		#set to overwrite the database-default-guesses for column headings
			"foreign_keys"	=> NULL,		#an array("field"=>array("sql"=>"SELECT * FROM `other_table` WHERE `field`>$value",array(":field"=>5))
				#Pulls data from that other_table and loads it into this field's options.
			"edit"			=> TRUE,		#enables the page to be reloaded as an edit with a $_GET
			"delete"		=> NULL,		#set to true to allow the user to delete rows
			"hide_pk"		=> TRUE,		#disable to display the primary key as read-only
			"order_by"		=> NULL,		#string to force a sort-order of the table
			"extra_fields"	=> NULL,		#an array of extra fields you might want to throw in
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
						$rules[$k]	= array(
							array("func"=>"is_numeric","fix_value"=>0),
						);
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
	
	function validate_data($data,$field_info)
	{
		global $odin;
		//!Fix Checkboxes
		#find all checkbox fields, throw them into the $checkboxes array
		$checkboxes	= array_keys($field_info["types"],"checkbox");
		if($checkboxes)
		{
			#Alter the posted fields the default values to be blank
			foreach($checkboxes as $field)
			{
				if(!isset($data[$field]))
					{ $field_info["fields"][$field]	= ""; }
			}
			#merge back in the original fields, overwritten by the passed $data. This fixes when checkboxes are not checked on submit
			#doing it this way keeps their keys in their proper order, passed data values still passed, and unchecked checkboxes still have keys
			$data	= $odin->array->ow_merge_r($field_info["fields"],$data);
		}

		//!Validate field options to make sure it was correctly passed
		if(!empty($field_info["options"]))
		{
			foreach($field_info["options"] as $opt_field=>$options)
			{
				$data_val	= $data[$field];
				if(!isset($options[$data_val]))
					{ $errors[$field][]	= "Invalid answer"; }
			}
		}

		$errors	= array();
		//!Run validation rules, recording all errors into $errors
		if(!empty($field_info["rules"]))
		{
			foreach($field_info["rules"] as $field=>$rules)
			{
				#loop through the rules array in order of their array position
				$data_val	= $data[$field];
				foreach($rules as $rule)
				{
					switch(TRUE)
					{
						#set the posted value to whatever $rule[set] is
						case isset($rule["set"]):
							$data_val	= $rule["set"];
						break;
						case $is_function=(isset($rule["func"]) && function_exists($rule["func"])):
						case (isset($rule["bolt"],$rule["method"]) && $odin->bolt_method_exists($rule["bolt"],$rule["method"])):
							#this is a function, run it
							if($is_function)
								{ $valid	= $rule["func"]($data_val); }
							else	#this must be a bolt/method, run it
								{ $valid	= call_user_func_array(array($odin->{$rule["bolt"]},$rule["method"]), array($data_val)); }

							#handle failed validations
							if(!$valid && (isset($rule["fix_value"]) || $rule["error"]))
							{
								if(isset($rule["fix_value"]))
									{ $data_val			= $rule["fix_value"]; }
								else
									{ $errors[$field][]	= $rule["error"]; }
							}
							#are we going to overwrite data with the validation value?
							if(!empty($rule["use_returned_value"]))
								{ $data_val	= $valid; }
						break;
					}
				}
				#inject the $data_val back into the $data
				$data[$field]	= $data_val;
			}
		}
		return array(
			"data"		=> $data,
			"errors"	=> $errors,
		);
	}
}