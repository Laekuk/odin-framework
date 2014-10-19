<?php
#CreatedBy;Weston;17OCT2014;Odin-Framework
class bolt_str
{
	// strip all non alpha numeric characters
	function alpha_num($str,$replace=" ")
	{
		$empty		= FALSE;
		$space		= FALSE;
		if(empty($replace))
			{ $replace = '_'; $empty=true; }
		if($replace == " ")
			{ $replace = '_'; $space=true; }
		$replace	= ($empty?"":$replace);
		$str		= trim(preg_replace(array('/[^0-9a-z]+/i','/\s+/'),' ',$str));
		if($space)
			{ $str	= str_replace(' ',$replace,$str); }
		return $str;
	}

	// make string lower case with no alpha numeric characters
	function tokey($str)
		{ return strtolower($this->alpha_num($str,"_")); }
}
