<?php
#CreatedBy;Aaron;29OCT2014;Odin-Framework
class bolt_datetime
{
	#This will be a wrapper for developers to use the native php datetime class.
	var $default_tz;
	function __construct($conf)
	{
		$this->default_tz	= $conf['timezone'];
		date_default_timezone_set($conf['timezone']);
	}

/*
Example usage:
	$difference	= $odin->datetime->diffs(array(
		"Now",										#This is the datetime to compare all others against.
		"March 18th, 1986",							#This is a basic date format passed
		array("Now","America/Anchorage"),			#DateTime can be an array(DateTime,TimeZone)
		array("+25 minutes","America/Anchorage")	#DateTime can also be a string of time to adjust
	));
*/
	function diffs()
	{
		$dates			= func_get_args();
		$compare_date	= NULL;
		$compare_date	= $this->make_date_obj(array_shift($dates));
		$i				= 0;
		foreach($dates as $date)
		{
			$date			= $this->make_date_obj($date);
			$diffs			= $compare_date->diff($date);
			$filtered_diffs	= array_filter((array)$diffs);
			if(!$filtered_diffs)
			{
				$type			= 'second';
				$diff			= 0;
			}
			else
			{
				$diff			= current($filtered_diffs);
				switch(key($filtered_diffs))
				{
					case 'y':	$type='year';	break;
					case 'm':	$type='month';	break;
					case 'd':	$type='day';	break;
					case 'h':	$type='hour';	break;
					case 'i':	$type='minute';	break;
					case 's':	$type='second';	break;
				}
			}
			$outputs[$i]	= $diff.' '.$type.($diff!==1?'s':'');
			$i++;
		}
		return ($i!==1?$outputs:current($outputs));
	}
	
	function make_date_obj($date)
	{
		if(is_array($date))
			{ list($date,$tz)	= $date; }
		else
			{ $tz	= $this->default_tz; }
		$date	= new DateTime($date, new DateTimeZone($tz));
		return $date;
	}
	
	function date_format($dt,$format)
	{
#		$this->dt->setTimezone(new DateTimeZone($tz));
	}
}