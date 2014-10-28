<?
class bolt_encryption
{
	function __construct($conf)
	{
		$this->key			= "e193dd5cfbc8fc80609e406f992e05ac";
		$this->salt			= "b82e36b7de3da17d70f00900c2310640";
		$this->hash_type	= "sha256";
		if(!empty($conf))
			{ foreach($conf as $k=>$v) { $this->{$k} = $v; } }
		$this->default_key = sha1($this->key);
		$this->_mcrypt_exists 	= function_exists('mcrypt_encrypt');
		$this->_mcrypt_cypher	= MCRYPT_RIJNDAEL_128;
		$this->_mcrypt_mode		= MCRYPT_MODE_CBC;
	}
	function set_key($key=false,$hash=false)
	{
		if(empty($key))	{ $this->key = $this->default_key; }
		else			{ $this->key = $hash ? $this->hash($key) : $key; }
		return $this->key;
	}
	
	// encryption
	function encrypt($pt)
	{
		// make sure the key is 32 chars
		$key = $this->hash($this->key.$this->salt,'sha1');
        $is = mcrypt_get_iv_size($this->_mcrypt_cypher, $this->_mcrypt_cypher);
        $iv = mcrypt_create_iv($is, MCRYPT_DEV_RANDOM);
        $ct = mcrypt_encrypt($this->_mcrypt_cypher, $key, $pt, $this->_mcrypt_cypher, $iv);
        return base64_encode($iv.$ct);
    }
	function decrypt($ct)
	{
		// make sure the key is 32 chars
		$key = $this->hash($this->key.$this->salt,'sha1');
        $ct = base64_decode($ct);
        $is = mcrypt_get_iv_size($this->_mcrypt_cypher, $this->_mcrypt_cypher);
        if (strlen($ct) < $is)
        	{ throw new Exception('Missing initialization vector'); }
        $iv = substr($ct, 0, $is);
        $ct = substr($ct, $is);
        $pt = mcrypt_decrypt($this->_mcrypt_cypher, $key, $ct, $this->_mcrypt_cypher, $iv);
        return rtrim($pt, "\0");
    }
    
	// hash a password for the database
	// enter a unique key to make sure the same password has a different hash
	// this shouldn't run too fast
	function pass($pass,$key=false)
	{
		$s=false; $l=1000;
		$salt = $this->hash($pass.$this->key).$this->hash($key.$this->salt);
		while($l) { $pass = $this->hash(($l%2?$l.$salt.$pass:$pass.$salt.$l)); $l--; }
		return $pass;
	}
	function hash($data,$type=false)
		{ return hash((empty($type)?$this->hash_type:$type),$data); }
}

