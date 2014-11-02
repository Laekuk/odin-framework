<?php
#CreatedBy;Aaron;11OCT2014;Odin-Framework
 class bolt_html
{
	var $num_instances	= 0;
	#This is an HTML form that allows you to update/edit database data.
	function form($fields=NULL,$opts=NULL)
	{
		global $odin;
		$this->num_instances++;
		$instance	= (isset($opts["instance"])?$opts["instance"]:"inst-".$this->num_instances);
		$o			= array(
			"set_element"		=> "fieldset",			#the "set" element, defaulted to <fieldset>.
			"set_class"			=> "",					#string of all classes you want to put on each set element
			"wrap_element"		=> "ul",				#the wrapping element, defaulted to <ul>
			"item_element"		=> "li",				#each item's element, defaulted to <li>
			"new_set_on"		=> array(),				#an array(field,..) of field names, when the code finds one of them, it will start a new set
															#with that field as the first element in the new set.
			"legends"			=> array(),				#an array(string,..) of all legends to use. once the array ends, any remaining sets will be left with no legend
															#use a null value or blank string to skip fieldsets, if you need to
			"submit_text"		=> "Submit",			#text of the submit button
			"instance"			=> $instance,			#a unique instance (per page load) used in the field's name field to group them together
															#This auto-incriments if left blank.
															#If you leave it blank be careful to not change the number of forms called between each pageload
			"field_types"		=> array(),				#an array(field=>select,field=>texarea) of field types (select, textarea, etc-).
															#This defaults to a type of "text" if unset for any fields
			"field_opts"		=> array(),				#any field options (select <options>, or a checkbox/radio groups options)
			"hide_fields"		=> array(),				#an array(field,..) of fields to hide (they will still be in your $_REQUEST ($_POST+$_GET) vars
			"headings"			=> array(),				#an array(field=>string,..) of headings with 
			"form_attrs"		=> array(				#an array(attr=>value,..) of all form attributes
				"id"				=> $instance,											#gives the form an id of its $instance
				"method"			=> "post",												#post by default
				"action"			=> preg_split("/[\?]+/",$_SERVER["REQUEST_URI"],2)[0],	#by default, use the current url, exlucing any $_GET variables.
			),
		);
		if($opts)
			{ $o	= $odin->array->ow_merge_r($o,$opts); }
		#if fields were not passed, return false
		if(!$fields)
			{ return FALSE; }

		#create the dom object & elements
		$dom		= new DOMDocument();
		$form		= $dom->createElement("form");
		if(!empty($o["form_attrs"]))
		{
			foreach($o["form_attrs"] as $attr=>$value)
			{
				$form_attr			= $dom->createAttribute($attr);
				$form_attr->value	= $value;
				$form->appendChild($form_attr);
			}
		}
		#setup the elements (field)set & wrapper
		$set				= $dom->createElement($o["set_element"]);
		$set_attr			= $dom->createAttribute("class");
		$set_count			= 1;
		$set_attr->value	= "elements set-".$set_count.(!empty($o["set_class"])?" $o[set_class]":FALSE);
		$set->appendChild($set_attr);
		if($o["legends"])
		{
			$legend			= $dom->createElement("legend",htmlentities(is_array($o["legends"])?array_shift($o["legends"]):$o["legends"]));
			$set->appendChild($legend);
		}
		
		$wrapper			= $dom->createElement($o["wrap_element"]);
		$set->appendChild($wrapper);


		#append the submit button into this form, nested inside of its own div.buttons wrapper.
		$submit_wrap		= $dom->createElement("div");
		$attr				= $dom->createAttribute("class");
		$attr->value		= "buttons";
		$submit_wrap->appendChild($attr);

		$submit				= $dom->createElement("button", htmlentities($o["submit_text"]));
		$submit_attr		= $dom->createAttribute("type");
		$submit_attr->value	= "submit";
		$submit->appendChild($submit_attr);

		$hidden_wrap		= $dom->createElement($o["wrap_element"]);
		$attr				= $dom->createAttribute("style");
		$attr->value		= "display:none;";
		$hidden_wrap->appendChild($attr);
		$attr				= $dom->createAttribute("class");
		$attr->value		= "hidden-elements";
		$hidden_wrap->appendChild($attr);

		
		$submit_wrap->appendChild($submit);
		foreach($fields as $name=>$default)
		{
			#do we want to add a new (field)set before this field?
			if(!empty($o["new_set_on"]) && in_array($name, $o["new_set_on"]))
			{
				$form->appendChild($set);

				$set				= $dom->createElement($o["set_element"]);
				$set_attr			= $dom->createAttribute("class");
				$set_count++;
				$set_attr->value	= "elements set-".$set_count.(!empty($o["set_class"])?" $o[set_class]":FALSE);
				$set->appendChild($set_attr);
				if($o["legends"])
				{
					$legend			= $dom->createElement("legend",htmlentities(is_array($o["legends"])?array_shift($o["legends"]):$o["legends"]));
					$set->appendChild($legend);
				}
				
				$wrapper			= $dom->createElement($o["wrap_element"]);
				$set->appendChild($wrapper);
			}
			#get this field's type
			$type		= (isset($o["field_types"][$name])?$o["field_types"][$name]:"text");
			#re/set this flag that says if this is one or many fields we're working with for this one "input".
			$multifields= FALSE;
			$label_last	= FALSE;
			switch($type)
			{
				#catch-all for any field-types.
				default:
					#create a single input field
					$input				= $dom->createElement("input");
				break;
				#build out later
				case "label":
				case "hidden":
				case "disabled":
				break;
				#textareas
				case "textarea":
					$multifields	= TRUE;
					$input	= $dom->createElement("textarea",htmlentities($default));
					#set a field name on the attribute
					$fname				= $dom->createAttribute("name");
					$fname->value		= $instance.'['.$name.']';
					$input->appendChild($fname);
				break;
				case "select":
					#does this input have multiple field options?
					if(empty($o["field_opts"][$name]))
						{ $o["field_opts"][$name]	= array(0=>"Please Select.."); }
#					$multifields	= TRUE;
					#start a ul & give it a class of type-group
					$input	= $dom->createElement("select");
					$class	= $dom->createAttribute("class");
					$class->value	= "$type-group";
					$input->appendChild($class);

					#append name to this field
					$fname			= $dom->createAttribute("name");
					$fname->value	= $instance.'['.$name.']'.($type=="checkbox"?"[]":FALSE);
					$input->appendChild($fname);

					#loop through all options add them to the ul
					foreach($o["field_opts"][$name] as $value=>$label)
					{
						$option	= $dom->createElement("option",htmlentities($label));

						#create the input field itself, then give it name, value, and type attributes.
						#field value
						$val			= $dom->createAttribute("value");
						$val->value		= $value;
						$option->appendChild($val);

						#check it on if this option's value matches the field's default value.
						if($value==$default || (is_array($default) && in_array($value, $default)))
						{
							$checked		= $dom->createAttribute("checked");
							$checked->value	= "checked";
							$option->appendChild($checked);
						}
						$input->appendChild($option);
					}
				break;
				#add radio & checkboxes
				case "radio":
				case "checkbox":
					#does this input have multiple field options?
					if(!empty($o["field_opts"][$name]))
					{
						$multifields	= TRUE;
						#start a ul & give it a class of type-group
						$input	= $dom->createElement("ul");
						$class	= $dom->createAttribute("class");
						$class->value	= "$type-group";
						$input->appendChild($class);

						#loop through all options add them to the ul
						foreach($o["field_opts"][$name] as $value=>$label)
						{
							$li	= $dom->createElement("li");

							#create the input field itself, then give it name, value, and type attributes.
							$field			= $dom->createElement("input");
							#field name
							$fname			= $dom->createAttribute("name");
							$fname->value	= $instance.'['.$name.']'.($type=="checkbox"?"[]":FALSE);
							$field->appendChild($fname);
							#field value
							$val			= $dom->createAttribute("value");
							$val->value		= $value;
							$field->appendChild($val);
							#field type
							$type_attr			= $dom->createAttribute("type");
							$type_attr->value	= $type;
							$field->appendChild($type_attr);

							#check it on if this option's value matches the field's default value.
							if($value==$default || (is_array($default) && in_array($value, $default)))
							{
								$checked		= $dom->createAttribute("checked");
								$checked->value	= "checked";
								$field->appendChild($checked);
							}

							#create a <label> for this option
							$opt_label	= $dom->createElement("label");
							$name_span	= $dom->createElement("span",htmlentities($label));
							#add the input field to the label
							$opt_label->appendChild($field);
							#add the field-name (span) to the label
							$opt_label->appendChild($name_span);
							#add the label to the li
							$li->appendChild($opt_label);
							#add the li to the ul
							$input->appendChild($li);
						}
					}
					else
					{
						#This checkbox (or radio) only has one option
						$label_last		= TRUE;
						#Create a single input field
						$input	= $dom->createElement("input");
						#If there is any default at all, check this field on.
						if(!empty($default))
						{
							$checked		= $dom->createAttribute("checked");
							$checked->value	= "checked";
							$input->appendChild($checked);
						}
					}
				break;
			}
			#create the item-level wrapping element & give it appropriate classes
			$element	= $dom->createElement($o["item_element"]);
			$el_class	= $dom->createAttribute("class");
			$el_class->value = "ft-$type f-$name";
			$element->appendChild($el_class);
			#is this a single-field attribute?
			if(!$multifields)
			{
				#wrap everything in a label
				$label				= $dom->createElement("label");
				#set a field name on the attribute
				$fname				= $dom->createAttribute("name");
				$fname->value		= $instance.'['.$name.']';
				$input->appendChild($fname);
				#add the type attribute to the input field
				$type_attr			= $dom->createAttribute("type");
				$type_attr->value	= $type;
				$input->appendChild($type_attr);

				#if there is a default value, set it here.
				if($default)
				{
					$dval			= $dom->createAttribute("value");
					$dval->value	= $default;
					$input->appendChild($dval);
				}
				#create a span with the field's name in it and add that into the label tag.
				$name_span	= $dom->createElement("span",htmlentities(isset($o["headings"][$name])?:$name));
				if($label_last)
				{
					#add the input to the label
					$label->appendChild($input);
					#add the name-span to the label.
					$label->appendChild($name_span);
				}
				else
				{
					#add the name-span to the label.
					$label->appendChild($name_span);
					#add the input to the label
					$label->appendChild($input);

				}
				$element->appendChild($label);
			}
			else
			{
/*				This is a multi-field attribute, which means that $input is already done.
				Don't modify it or wrap a label around it or anything, just add its name span before you add it to the wrapper.
				Just stick it straight in!!
*/				$name_span	= $dom->createElement("span",htmlentities($name));
				if($label_last)
				{
					$element->appendChild($input);
					$element->appendChild($name_span);
				}
				else
				{
					$element->appendChild($name_span);
					$element->appendChild($input);

				}
			}
			
			if(in_array($name, $o["hide_fields"]))
				{ $hidden_wrap->appendChild($element); }
			else
				{ $wrapper->appendChild($element); }
		}

		#Add the remaining dom elements to the output
		$form->appendChild($set);
		$form->appendChild($hidden_wrap);
		$form->appendChild($submit_wrap);

		#finally, write the form tag into the dom object. Then return the dom as HTML
		$dom->appendChild($form);
		return $dom->saveHTML();
	}
	
	function table($data,$opts=NULL)
	{
		global $odin;
		$this->num_instances++;
		$instance	= (isset($opts["instance"])?$opts["instance"]:"inst-".$this->num_instances);
		$o			= array(
			"caption"			=> "",
			"skip_cols"			=> array(),			#an array(column,..) of columns to skip
			"add_cols"			=> array(),			#an array(column=>string,..) of fields to tack onto the end of this. You may use replace variables with {}
														#example: "hello {name}" would pull the name column for this row & replace with that value.
														#note: this can also overwrite previous fields too
			"add_replaces"		=> array(),			#an array(variable=>string,..) which replaces into the add_cols
			"headings"			=> array(),			#an array(column=>string,..) of replacement headings for each column
			"tbl_attrs"			=> array(			#an array(attr=>value,..) of all table attributes
				"id"				=> $instance,
			),
		);

		if($opts)
			{ $o	= $odin->array->ow_merge_r($o,$opts); }

		if(!empty($o["skip_cols"]))
			{ $o["skip_cols"]	= array_flip($o["skip_cols"]); }

		if(!empty($o["add_cols"]))
		{
			#loop through all $data, adding in those $add_cols to the end, while running str_replace on them.
			$keys		= array_keys(current((array)$data));
			$wrap_keys	= function(&$val){ $val	= "{".$val."}"; };
			array_walk($keys, $wrap_keys);
			if(!empty($o["add_replaces"]))
			{
				$ar_keys	= array_keys($o["add_replaces"]);
				$ar_vals	= array_values($o["add_replaces"]);
				array_walk($ar_keys, $wrap_keys);
			}
			foreach($data as &$v)
			{
				foreach($o["add_cols"] as $name=>$string)
				{
					#if the column is already set, use its value instead of $string.
					if(isset($v[$name]))
						{ $string	= $v[$name]; }
					#if there are add_replace key/values, run those first so that if they contain {field_name}s, they will be replaced too.
					if(isset($ar_keys))
						{ $string	= str_replace($ar_keys, $ar_vals, $string); }
					#replace this row's field names with field values
					$v[$name]	= str_replace($keys, $v, $string);
				}
			}
		}
		#create the dom object & elements
		$dom		= new DOMDocument();
		$table		= $dom->createElement("table");
		#add on any table attributes.
		if(!empty($o["tbl_attrs"]))
		{
			foreach($o["tbl_attrs"] as $attr=>$value)
			{
				$table_attr			= $dom->createAttribute($attr);
				$table_attr->value	= $value;
				$table->appendChild($table_attr);
			}
		}
		#this is the first itteration through the fields, so do headings.
		$first		= TRUE;
		#create <thead>
		$thead		= $dom->createElement("thead");
		$head_tr	= $dom->createElement("tr");
		#set a class to the <th>'s <tr> tag.
		$head_class	= $dom->createAttribute("class");
		$head_class->value = "headings";
		$head_tr->appendChild($head_class);
		#create <tbody>
		$tbody		= $dom->createElement("thead");
		
		#append children where they need to go.
		if(!empty($o["caption"]))
			{ $table->appendChild($dom->createElement("caption",htmlentities($o["caption"]))); }
		$thead->appendChild($head_tr);
		$table->appendChild($thead);
		$table->appendChild($tbody);

		$row_count	= 0;
		foreach($data as $row_key=>$columns)
		{
			$row_count++;
			#generate new <tr> tag & its classes
			$tr			= $dom->createElement("tr");
			$tr_classes	= $dom->createAttribute("class");
			$tr_classes->value	= ($row_count%2?"odd":"even")." row-".$row_key;
			$tr->appendChild($tr_classes);
			foreach($columns as $name=>$value)
			{
				#skip this column?
				if(!isset($o["skip_cols"][$name]))
				{
					#is this the first row ever?
					if($first)
					{
						$th	= $dom->createElement("th",htmlentities(isset($o["headings"][$name])?$o["headings"][$name]:$name));
						$head_tr->appendChild($th);
					}
					#create the <th> tag & fill it with your values, as long as this value is not blank.
					$td					= $dom->createElement("td");
					if($value)
					{
						$td_val				= $dom->createDocumentFragment();
						if(is_array($value))
							{ $value	= json_encode($value); }
						$value				= str_replace('&', '&amp;', $value);
						$td_val->appendXML($value);
						$td->appendChild($td_val);
					}
					$td_classes			= $dom->createAttribute("class");
					$td_classes->value	= $name;
					$td->appendChild($td_classes);
					$tr->appendChild($td);
				}
			}
			$tbody->appendChild($tr);
			$first	= NULL;
		}
		$dom->appendChild($table);
		return $dom->saveHTML();
	}
}