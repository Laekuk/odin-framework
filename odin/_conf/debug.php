<?php
#CreatedBy;Aaron;17OCT2014;Odin-Framework
$conf	= (object)array(
	"developer_ips"		=> array(
		#this is just what my home IP looks like on the local network. Its using $_SERVER["REMOTE_ADDR"] to check this.
		"::1",
#		"300.300.300.300",
	),

/*	Fair Warning:
		I'd recommend only flipping this to true if you're working on an intranet or are currently in the development process.
		Once turned on, it will show the debug_backtrace() of your errors, which (in my opinion) should be kept as developer-only information for security.
*/	"force_error"		=> FALSE,
	#how many levels deep should we go on the debug_backtrace() print out? Set to 0 for infinate. Defaulted to 3, since normally I don't personally need more than 2.
	"backtrace_levels"	=> 1,
);