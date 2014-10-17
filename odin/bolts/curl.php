<?php
#CreatedBy;Aaron;16OCT2014;Odin-Framework
/*
	cURL is a command line tool and library for transferring data with URL syntax
	More information about cURL in PHP:		http://php.net/manual/en/book.curl.php
		*This class is an attempt to have a much simpler cURL experience for developers
	More information about cURL in general:	http://curl.haxx.se/
*/
class bolt_curl
{
	var $err	= NULL;
	function request($url,$opts=NULL)
	{
		global $odin;
		$o	= array(
#			"return_all"			=> 1,			#pass this if you want to return ALL THE DATA. see below for more options

			# For all possible options, see http://php.net/manual/en/function.curl-setopt.php
#			CURLOPT_USERPWD			=> 0,			# Do you need to send a userpwd? (most often often used in API authentication)
			CURLOPT_HEADER			=> 0,			# By default, set to 0 to eliminate header info from response. Set to 1 to get headers in the response.
			CURLOPT_RETURNTRANSFER	=> 1,			# By default, return response data instead of BOOL(TRUE)
			CURLOPT_SSL_VERIFYPEER	=> 1,			# By default, verify that the remote SSL is secure
			CURLOPT_USERAGENT		=> "spider",	# Just your friendly neighborhood spider, man.. you could also use $_SERVER["HTTP_USER_AGENT"].
			CURLOPT_AUTOREFERER		=> 1,			# By default, set the http referer on those redirect
			CURLOPT_CONNECTTIMEOUT	=> 60,			# By default, lower the time it takes to fail on connection.	(in seconds)
			CURLOPT_TIMEOUT			=> 60,			# By default, lower the time it takes to fail on loading.		(in seconds)
			CURLOPT_POST			=> 0,			# By default, do not post data.
#			CURLOPT_POSTFIELDS		=> 0,
/* 				Only set this if you're actually posting data. Set the data as a string in this key of your array.
				It should look like what a $_GET looks like when in the URL. Also, don't forget to to urlencode()
					eg: "test=1&debug=off&id=5"
*/		);
		if($opts)
		{
			$odin->array->set_overwrite_merge_recursive_opts(array("preserve_keys"=>1));
			$o	= $odin->array->overwrite_merge_recursive($o,$opts);
		}
		#Pull out any special return commands, if they were sent
		if(isset($o["return_all"]))
		{
			$return_all	= 1;
			unset($o["return_all"]);
		}
		#initalize the curl request object.
	    $req = curl_init($url);
	    #set all options
		foreach($o as $k=>$v)
			{ curl_setopt($req, intval($k), $v); }
		#execute the curl and grab any errors
	    $response	= curl_exec($req);
		$error 		= curl_error($req);
		#store error locally, if there was one.
		if(!empty($error))
			{ $this->err	= $error; }
		if(isset($return_all))
		{
			$response	= array(
				"response"	=> $response,
				"error"		=> $error,
				"headers"	=> curl_getinfo($req),
			);
		}
		#close curl object
		curl_close($req);
		#return the server's response
		return $response;
	}
}