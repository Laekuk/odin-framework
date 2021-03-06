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
	function single($table,$opts=NULL)
	{
		global $odin;
		$this->num_instances++;
		#loose cleanse against possible injections.
		$table	= $odin->str->alpha_num($table);
		$instance	= (isset($o['instance'])?$o['instance']:'crud-inst-'.$this->num_instances);
		$o	= array(
			'form_labels'		=> array('Add'),	#the form's label
			'submit_text'		=> 'Add',			#the copy that goes on the submit button
			'beautify_headings'	=> TRUE,			#a boolean to determine if we'll beautify the headings
			'supress_cols'		=> NULL,			#an array of columns to hide on the insert form
			'locked_vals'		=> NULL,			#an array of columns & their forced values. This also hides the columns by default (overwritten by col_types)
			'defaults'			=> NULL,			#an array of columns & their default values, which overwrites the database-default-guesses
			'id'				=> NULL,			#a primary key of a row you want to edit. If left blank, this form will be an insert, rather than an update
			'col_types'			=> NULL,			#an array of column types, which overwrite the database-default-guesses
			'col_options'		=> NULL,			#an array of column options, which overwrites the database-default-guesses
			'col_rules'			=> NULL,			#set to overwrite the database-default-guesses for validation rules. Acceptable formats are:
/*				This will validate if the posted value is numeric, if it is not it will return the error message 'Must be a Number'
				array('field'=>array('function_name'	=> 'is_numeric',	'error'	=> 'Must be A Number'))

				This will run the alpha_num() method inside of the str bolt (passing the posted value to that method as the first paramiter),
					then replace whatever was posted with the alpha_num
				array(
					'field'=>array(
						'bolt'					=> 'str',
						'method'				=> 'alpha_num',
						'use_returned_value'	=> TRUE,
					)
				)

				This will validate if the posted value is numeric, if it is not it will set the posted value to an int of 0
				array('field'=>array('function_name'	=> 'is_numeric',	'fix_value'	=> 0))

				This will overwrite your posted data's value with 'Tested'
				array('field'=>array('set'				=> 'Tested'))
*/
			'instance'		=> $instance,		#set to manually create the id for this form.
			'headings'		=> NULL,			#set to overwrite the database-default-guesses for column headings
			'new_set_on'	=> NULL,			#an array of fields to break the fieldsets up on.
			'foreign_keys'	=> NULL,			#an array('field'=>array('sql'=>"SELECT * FROM `other_table` WHERE `field`>$value",array(':field'=>5))
				#Pulls data from that other_table and loads it into this field's options.
			'hide_pk'		=> TRUE,		#disable to display the primary key as read-only
			'success_msg'	=> NULL,		#disable to display the primary key as read-only
		);
		if($opts)
			{ $o	= $odin->array->ow_merge_r($o,$opts); }
		$sql	= "DESCRIBE `$table`";
		$fields	= $odin->sql->qry($sql,NULL,'Field');
		$fields	= $this->parse_fields($fields);

		if($o['beautify_headings'])
			{ $fields['headings']	= $odin->str->beautify($fields['headings']); }

		if($o['defaults'])
			{ $fields['fields']	= $odin->array->ow_merge_r($fields['fields'],$o['defaults']); }
		if($o['col_types'])
			{ $fields['types']	= $odin->array->ow_merge_r($fields['types'],$o['col_types']); }
		if($o['headings'])
			{ $fields['headings']	= $odin->array->ow_merge_r($fields['headings'],$o['headings']); }
		if($o['col_options'])
			{ $fields['options']	= $odin->array->ow_merge_r($fields['options'],$o['col_options']); }
		if($o['col_rules'])
			{ $fields['rules']	= $odin->array->ow_merge_r($fields['rules'],$o['col_rules']); }

		
		if(isset($_REQUEST[$instance]))
		{
			$data	= $_REQUEST[$instance];
			$info	= $this->validate_data($data,$fields);
			if(empty($info['errors']))
			{
				$error_hit	= NULL;
				#Insert?
				if(!$opts['id'])
				{
					#insert into database.
					$insert	= $odin->qdb->insert($table,$info['data']);
					if(!$insert)
						{ $error_hit	= TRUE; }
					else
					{
						#set success message
						$info['msg']['_general']	= ($o['success_msg']?:'Successfully Added');
					}
				}
				else#Update
				{
					#update database field.

					#prevent html modification attempts
					if(!isset($info['data'][$fields['primary']]) || $info['data'][$fields['primary']]!==$opts['id'])
						{ $info['data'][$fields['primary']]	= $opts['id']; }
					$update	= $odin->qdb->update($table,$info['data'],$fields['primary']);
					if(!$update)
						{ $error_hit	= TRUE; }
					else
					{
						#set success message
						$info['msg']['_general']	= ($o['success_msg']?:'Successfully Updated');
					}
				}
				#clear post.
				unset($_POST[$instance]);
				#throw a general form error?
				if($error_hit)
					{ $info['errors']['_general']	= 'Unable to complete action.'; }
			}
		}
		if(isset($opts['id']))
		{
			#get this row's record
			$sql		= "SELECT * FROM `$table` WHERE `$fields[primary]`=?";
			$cur_values	= $odin->sql->qry($sql,array($opts['id']));
			if(is_array($cur_values))
			{
				#update base copy on the form, if its still being used
				if($o['form_labels'][0]=='Add')
					{ $o['form_labels'][0]	= 'Edit'; }
				if($o['submit_text']=='Add')
					{ $o['submit_text']	= 'Edit'; }
				$cur_values	= current($cur_values);
				#merge this row with the defaults
				$fields['fields']	= $odin->array->ow_merge_r($fields['fields'],$cur_values);
			}
			else
			{
				#error, no record to update.
				return '<p class="error">Error, record lookup failed</p>';
			}
		}
		$hide_fields	= array();
		if($o['supress_cols'])
			{ $hide_fields	= array_merge($hide_fields,$o['supress_cols']); }
		if($o['hide_pk'])
			{ $hide_fields	= array_merge($hide_fields,array($fields['primary'])); }
		$form_opts	= array(
			'instance'		=> $o['instance'],
			'submit_text'	=> $o['submit_text'],
			'legends'		=> $o['form_labels'],
			'new_set_on'	=> $o['new_set_on'],
			'instance'		=> $o['instance'],
			'errors'		=> (!empty($info['errors'])?$info['errors']:NULL),
			'messages'		=> (!empty($info['msg'])?$info['msg']:NULL),
			'headings'		=> $fields['headings'],
			'field_types'	=> $fields['types'],
			'field_opts'	=> $fields['options'],
			'hide_fields'	=> $hide_fields,
		);

		$form	= $odin->html->form($fields['fields'],$form_opts);
		
		return $form;
	}

/*
	#pull down rows from a table and display them, options to allow the end-user to edit & delete those rows.
	function multi($table,$opts=NULL)
	{
		$this->num_instances++;
		$instance	= (isset($o['instance'])?$o['instance']:'crud-inst-'.$this->num_instances);
		$o	= array(
			'form_label'	=> 'Data',		#the form's label
			'supress_cols'	=> NULL,		#an array of columns to hide on the table (not hidden on the edit-form)
			'locked_vals'	=> NULL,		#an array of columns & their forced values. This also hides the columns by default (overwritten by col_types)
			'col_types'		=> NULL,		#an array of column types, which overwrite the database-default-guesses
				#For options, se an array('field'=>array('type'=>'select','options'=>array('key'=>'value')))
			'col_rules'		=> NULL,		#set to overwrite the database-default-guesses for validation rules
			'instance'		=> $instance,	#set to manually create the id for this form.
			'headings'		=> NULL,		#set to overwrite the database-default-guesses for column headings
			'foreign_keys'	=> NULL,		#an array('field'=>array('sql'=>"SELECT * FROM `other_table` WHERE `field`>$value",array(':field'=>5))
				#Pulls data from that other_table and loads it into this field's options.
			'edit'			=> TRUE,		#enables the page to be reloaded as an edit with a $_GET
			'delete'		=> NULL,		#set to true to allow the user to delete rows
			'hide_pk'		=> TRUE,		#disable to display the primary key as read-only
			'order_by'		=> NULL,		#string to force a sort-order of the table
			'extra_fields'	=> NULL,		#an array of extra fields you might want to throw in
		);
	}
	
*/
	function parse_fields($fields)
	{
		global $odin;
		$field		= NULL;
		$type		= NULL;
		$rules		= NULL;
		$default	= NULL;
		$options	= NULL;
		if(empty($fields))
			{ return FALSE; }

		foreach($fields as $k=>$v)
		{
			$field[$k]		= NULL;
			$heading[$k]	= $odin->str->alpha_num($k);
			if($v['Key']=='PRI')
				{ $primary	= $k; }
			#parse the field's data-type & information.
			$field_type	= preg_split('/\(/', $v['Type'],2);
			
			$field_info	= (isset($field_type[1])?preg_split('/\,/', substr($field_type[1], 0,-1)):NULL);
			$field_type	= $field_type[0];
			switch($field_type)
			{
				default:
				break;
				case 'int';
					$field_info	= $field_info[0];
					#treat this like a boolean by default.
					if($field_info>1)
						{ $type[$k]	= 'number'; }
					else
						{ $type[$k]	= 'checkbox'; }
					#force an integer type
					$rules[$k]	= [['callback'=>'is_numeric','fix_value'=>0]];
				break;
				case 'enum':
					$type[$k]		= 'select';
					$field_opts		= NULL;
					foreach($field_info as $info_key=>$info_val)
					{
						$info_val			= substr($info_val, 1,-1);
						$field_opts[$info_val]	= $info_val;
					}
					$options[$k]	= $field_opts;
				break;
				case 'text':
				case 'longtext':
					$type[$k]		= 'textarea';
				break;
			}
			if($v['Default'])
				{ $field[$k]	= $v['Default']; }
		}
		return array(
			'primary'	=> $primary,
			'fields'	=> $field,
			'types'		=> $type,
			'headings'	=> $heading,
			'options'	=> $options,
			'rules'		=> $rules,
		);
	}
	
	function validate_data($data,$field_info)
	{
		global $odin;
		//!Fix Checkboxes
		#find all checkbox fields, throw them into the $checkboxes array
		$checkboxes	= array_keys($field_info['types'],'checkbox');
		if($checkboxes)
		{
			#Alter the posted fields the default values to be blank
			foreach($checkboxes as $field)
			{
				if(!isset($data[$field]))
					{ $field_info['fields'][$field]	= ''; }
			}
			#merge back in the original fields, overwritten by the passed $data. This fixes when checkboxes are not checked on submit
			#doing it this way keeps their keys in their proper order, passed data values still passed, and unchecked checkboxes still have keys
			$data	= $odin->array->ow_merge_r($field_info['fields'],$data);
		}

		//!Validate field options to make sure it was correctly passed
		if(!empty($field_info['options']))
		{
			foreach($field_info['options'] as $opt_field=>$options)
			{
				$data_val	= $data[$field];
				if(!isset($options[$data_val]))
					{ $errors[$field][]	= 'Invalid answer'; }
			}
		}

/*
Valid Rule Options:
	- required					: if set, this is a required field
	- set						: if set, this will be 
	- skip						: if set to true, this field will be removed from data.
	- 'callback'				: either the name of a global function, or a bolt/method combo to call.
		- use_returned_value		: If this is set, replace the data value with whatever your function or method returned.
		- flip_valid				: If this is set, reverse the outcome of $valid so true becomes false, false becomes true.
		- invalid_skip				: If $valid is true, remove this field from the data.
		- error						: If $valid is false, return this error string.
		- fix_value					: If this is set & $valid is false, set the value to this. This overrides the 'error' message from appearing, if both are set.
*/
		$errors	= array();
		//!Run validation rules, recording all errors into $errors
		if(!empty($field_info['rules']))
		{
			foreach($field_info['rules'] as $field=>$rules)
			{
				$valid	= TRUE;
				#loop through the rules array in order of their array position
				$data_val	= (isset($data[$field])?$data[$field]:NULL);
				foreach($rules as $rule)
				{
					$skip	= FALSE;
					switch(TRUE)
					{
						#validate that a field is not empty
						case isset($rule['not_empty']):
						case isset($rule['required']):
							$valid	= !empty(trim($data_val));
							if(!isset($rule['error']) && !$valid)
								{ $rule['error']	= 'Required'; }
						break;
						#set the posted value to whatever $rule[set] is
						case isset($rule['set']):
							$data_val	= $rule['set'];
						break;
						case $rule==='skip' || isset($rule['skip']):
							$skip	= TRUE;
						break;
						case isset($rule['callback']):
							$valid	= FALSE;
							if(is_array($rule['callback']))
							{
								$method	= array_pop($rule['callback']);
								if(method_exists($rule['callback'],$method))
									{ $valid	= call_user_func_array(array($rule['callback'],$method), array($data_val)); }
							}
							else
							{
								if(function_exists($rule['callback']))
									{ $valid	= $rule['callback']($data_val); }
							}
							#run & possibly adjust the validation method.
/*
	$valid	= NULL;
	if($is_function)	#this is a function, run it
		{ $valid	= $rule['func']($data_val); }
	elseif($is_bolt)	#this must be a bolt/method, run it
		{ $valid	= call_user_func_array(array($odin->{$rule['bolt']},$rule['method']), array($data_val)); }
*/
							#flip the validation outcome?
							if(isset($rule['flip_valid']))
								{ $valid	= !$valid; }

							#should we skip this field?
							if(!$valid && isset($rule['invalid_skip']))
								{ $skip	= TRUE; }

							#are we going to overwrite data with the validation value?
							if(!empty($rule['use_returned_value']))
								{ $data_val	= $valid; }
						break;
					}
					#handle failed validations
					if(!$valid)# && (isset($rule['fix_value']) || $rule['error']))
					{
						if(isset($rule['fix_value']))
							{ $data_val			= $rule['fix_value']; }
						elseif(isset($rule['error']))
							{ $errors[$field][]	= $rule['error']; }
						else
							{ $errors[$field][]	= 'Error'; }
					}
				}
				#unset or inject the $data_val back into $data
				if($skip)
					{ unset($data[$field]); }
				else
					{ $data[$field]	= $data_val; }
			}
		}
		return array(
			'data'		=> $data,
			'errors'	=> $errors,
		);
	}
}