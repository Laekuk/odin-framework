<?php
#CreatedBy;Aaron;01NOV2014;Odin-Framework
/*
	Disclaimer: I am just learning couchbase now, so don't trust this script.
*/
class bolt_couchbase
{
	var $server;
	var $login;
	function __construct($conf)
	{
		if($conf['default_port'])
			{ $this->set_server($conf['default_host'],$conf['auth'],$conf['default_port']); }
		elseif($conf['default_host'])
			{ $this->set_server($conf['default_host'],$conf['auth']); }
	}

	function set_server($host,$login,$port=8092)
	{
		$this->server	= $host.':'.$port;
		$this->login	= $login['user'].':'.$login['pass'];
	}

	#See http://docs.couchbase.com/couchbase-manual-2.5/cb-rest-api/#querying-views-with-rest for full spec
	function pull($bucket,$view_path,$opts=NULL)
	{
		global $odin;
		#if they exist, merge the passed options with the defaults.
		$o	= array(
			'debug'	=> NULL,	#If debug is switched to true, it will return information about the cURL request instead of just returning data.
			'limit'	=> NULL,	#Limits the number of results recieved
		);
		if($opts)
			{ $o	= $odin->array->ow_merge_r($o,$opts); }
		if(!$this->server)
			{ return $odin->debug->error('No host set'); }
		$uri	= $this->server.'/'.$bucket.'/'.$view_path.($o['limit']?'?limit='.$o['limit']:'');
		$curl_o		= array(
			CURLOPT_USERPWD 		=> $this->login,
			CURLOPT_CONNECTTIMEOUT	=> 4,
		);
		if($o['debug'])
			{ $o['return_all']	= 1; }
		$ret	= $odin->curl->request($uri,$curl_o);
		return json_decode($ret);
	}
}