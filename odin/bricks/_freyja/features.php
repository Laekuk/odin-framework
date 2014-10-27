<?php
class mortar__freyja_features
{
	function __construct()
	{
		$this->freyjas_associations	= array(
			"love",		"sexuality",
			"beauty",	"fertility",
			"gold",		"sorcory",
			"war",		"death"
		);
	}
	
	function list_them()
		{ return implode(", ", $this->freyjas_associations); }
}