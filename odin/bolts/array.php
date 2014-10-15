<?php
#AaronPeloquin;11OCT2014;Odin-Framework
class bolt_array
{
/*
	recusively overwrite an array. It works exactly like array_merge_recursive(), except that when
	it encounters duplicate keys, it overwrites instead of preserving both values & destroying their key.
*/
	function overwrite_merge_recursive()
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
				continue;
			foreach($array as $key => $value)
				if (is_string($key))
					if (is_array($value) && array_key_exists($key, $merged) && is_array($merged[$key]))
						{ $merged[$key] = call_user_func_array(array($this,__FUNCTION__), array($merged[$key], $value)); }
					else
						{ $merged[$key] = $value; }
					else
						{ $merged[] = $value; }
		}
		return $merged;
	}
}