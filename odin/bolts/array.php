<?php
#CreatedBy;Aaron;11OCT2014;Odin-Framework
class bolt_array
{
	var $overwrite_merge_recursive_opts	= NULL;

/*	Set options for overwrite_merge_recursive() by passing an array to this method
	All valid options:
	array(
		"preserve_keys"	=> 1,	#This will ALWAYS perserve keys. If the keys are not strings, it will destroy them for efficency unless this is set.
	)
*/	function set_ow_merge_r_opts($opts)
		{ $this->ow_merge_r_opts	= $opts; }

/*	Recusively overwrite an array. It works exactly like array_merge_recursive(), except that when
	it encounters duplicate keys, it overwrites instead of preserving both values & destroying their key.
	This stands for "overwrite-merge, recursive".
*/	function ow_merge_r()
	{
		if(func_num_args() < 2)
		{
			trigger_error(__FUNCTION__ .' needs two or more array arguments', E_USER_WARNING);
			return;
		}
		$arrays	= func_get_args();
		$merged	= array();
		$i		= count($arrays);
		$total	= $i;
		#this flips the order of your array items on its head, but it works faster than a while
		while ($arrays)
		{
        	$array = array_shift($arrays);
			if (!is_array($array))
			{
				trigger_error(__FUNCTION__ .' encountered a non array argument', E_USER_WARNING);
				return;
			}
			if (!$array)
				{ continue; }
			foreach($array as $key => $value)
			{
				#Preserve Keys?
				if(is_string($key) || isset($this->ow_merge_r_opts["preserve_keys"]))
				{
					if (is_array($value) && array_key_exists($key, $merged) && is_array($merged[$key]))
						{ $merged[$key] = call_user_func_array(array($this,__FUNCTION__), array($merged[$key], $value)); }
					else
						{ $merged[$key] = $value; }
				}
				else
					{ $merged[] = $value; }
			}
		}
		#reset options
		$this->overwrite_merge_recursive_opts	= NULL;
		return $merged;
	}
}