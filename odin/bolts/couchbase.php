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

	function pull($bucket,$view_path,$debug=NULL)
	{
		global $odin;
		if(!$this->server)
			{ return $odin->debug->error('No host set'); }
		$uri	= $this->server.'/'.$bucket.'/'.$view_path;
		$o		= array(
			CURLOPT_USERPWD 		=> $this->login,
			CURLOPT_CONNECTTIMEOUT	=> 3,
		);
		if($debug)
			{ $o['return_all']	= 1; }
		$ret	= $odin->curl->request($uri,$o);
		return json_decode($ret);
	}
}