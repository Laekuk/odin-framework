<?php
#CreatedBy;Weston;17OCT2014;Odin-Framework
class bolt_str
{
	function __construct($conf)
		{ $this->conf	= $conf; }

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

	function beautify($str)
	{
		if(is_array($str))
		{
			foreach($str as $k=>$v)
				{ $str[$k]	= $this->beautify($v); }
		}
		elseif(is_string($str))
		{
			if(strpbrk($str, ' _')!==FALSE)
			{
				$str	= strtolower($str);
				$str	= preg_split('/[\s_]/', $str);
				foreach($str as &$word)
				{
					if(in_array($word, $this->conf->uppercase_words))
						{ $word	= strtoupper($word); }
					elseif(!in_array($word, $this->conf->lowercase_words))
						{ $word	= ucfirst($word); }
				}
				$str	= implode(' ', $str);
			}
			else
				{ $str	= ucfirst($str); }
		}
		return $str;
	}
}
