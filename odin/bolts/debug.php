<?php
#CreatedBy;Aaron;17OCT2014;Odin-Framework
class bolt_debug
{
	#setup defaults for all 
	var $developer_ips		= NULL;
	var $force_error		= FALSE;
	var $backtrace_levels	= 3;
	function __construct($conf)
	{
		#Load in all configured settings
		#make a local copy of the config for developer IPs, setting all keys to be what the values used to be.
		$this->developer_ips			= array_flip($conf->developer_ips);
		if($conf->backtrace_levels)
			{ $this->backtrace_levels	= $conf->backtrace_levels; }
		if($conf->force_error)
			{ $this->force_error		= TRUE; }
	}
	
/*	This is intended to be called internally for dynamic-based errors,
	meaning they are errors that are not just caused by the developer setting stuff up wrong.
	they're for when your MySQL server is down, or an API your application is depending on has gone missing.
*/	function error($msg=NULL,$force_error=NULL)
	{
		if($this->is_developer() || $force_error || $this->force_error)
		{
			if(empty($msg))
				{ $msg	= htmlspecialchars('<tone sass="on">No Message Passed..</tone>'); }
			#You are a developer, or this error should display to the general public (meaning EVERYONE).
			echo "<h1>Error</h1>";
			if(is_string($msg))
				{ echo $msg; }
			else
			{
				echo	'<pre style="background:#222;border:2px dotted #000;margin:10px;padding:15px;text-align:left;-moz-border-radius:10px;color:#009047;overflow:auto;opacity:75;font-size:11px;">';
				var_dump($msg);
				echo	'</pre>';
			}
			echo "<h1>Developer Backtrace</h1>";
			echo '<pre style="background:#222;border:2px dotted #000;margin:10px;padding:15px;text-align:left;-moz-border-radius:10px;color:#009047;overflow:auto;opacity:75;font-size:11px;">';
			$debug					= debug_backtrace();
			$debug[0]["args"][0]	= "<em>See Above..</em>";
			$total_lvls				= count($debug);
			$dbg_i					= 0;
			$new_backtrace			= array();
			while(($dbg_i<$this->backtrace_levels || $this->backtrace_levels===0) && !empty($debug))
			{
				$dbg_i++;
				$request	= array_shift($debug);
				$new_backtrace[]	= array(
					"level"		=> $total_lvls-$dbg_i,
					"file"		=> $request["file"],
					"class"		=> $request["class"],
					"method"	=> $request["function"],
					"line"		=> $request["line"],
					"args"		=> $request["args"],
				);
			}
			var_dump($new_backtrace);
			echo '</pre>';

			die();
		}
		#otherwise just return False.
		return FALSE;
	}
	
	function is_developer()
		{ return isset($this->developer_ips[$_SERVER["REMOTE_ADDR"]]); }
}