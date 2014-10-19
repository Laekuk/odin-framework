<?php
#CreatedBy;Aaron;11OCT2014;Odin-Framework
class bolt_sql
{
	var $cur_conn;
	var $conns;
	function __construct($conf)
	{
		$this->conn_info	= $conf->connections;
		#if there is a default connection, autoconnect.
		if($conf->default_conn)
			{ $this->connect($conf->default_conn); }
	}

	function connect($conn_name)
	{
		#backup the current connection, then update the current connection.
		$old_conn		= $this->cur_conn;
		$this->cur_conn	= $conn_name;
		#if this is already connected, return true.
		if(isset($this->conns[$conn_name]))			{ return TRUE; }
		#grab the connection information. if there was no connection information, return false.
		if(!$info = $this->conn_info[$conn_name])
		{
			#reverse the current connection.
			$this->cur_conn	= $old_conn;
			return FALSE;
		}
		#build the host paramiters
		$host_params	= NULL;
		if(!empty($info["params"]) && is_array($info["params"]))
			{ $host_params	= implode(';',array_map(function($v,$k){return sprintf("%s=%s",$k,$v);},$info["params"],array_keys($info["params"]))); }
		#connect to the database
		$conn_str					= $info["type"].(empty($host_params)?FALSE:":".$host_params);
		try
		{
			$this->conns[$conn_name]	= new PDO($conn_str,$info["user"],$info["pass"]);
			return TRUE;
		}
		catch(PDOException $e)
		{
			global $odin;
			$this->conn_err[$conn_name]	= $e;
			return $odin->debug->error($e);
		}
	}

	function qry($sql,$params=array(),$key=FALSE,$opts=NULL)
	{
		global $odin;
		$o	= array(
			"return"	=> TRUE,#TRUE will always match the first case in a swtich, which is default. Options for this are: "default","num_rows","qry_obj"
		);
		if($opts)
			{ $o	= $odin->array->ow_merge_r($o,$opts); }
		#if there was a database connection error, do not attempt any queries.
		if(isset($this->conn_err[$this->cur_conn]))
			{ return FALSE; }
		#Default $prepared statement boolean to be false.
		$prepared	= FALSE;

		#parse the sql statement and get its type.
		$sql		= ltrim($sql);
		$qry_type	= preg_split("/[\s]+/",$sql,2)[0];

		#grab a local function-scope version of the active database connection.
		$c	= $this->conns[$this->cur_conn];
		#enable php errors.
		$c->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		#if there are params, run this as a prepared statement, otherwise run as a normal query-string.
		$r	= $c->prepare($sql);
		$e	= $r->execute((is_array($params)?$params:NULL));
		#if $r fails, return sql error
		if(!$e)
		{
			$this->err	= array(
				"statement"		=> $sql,
				"parameters"	=> $params,
				"error"			=> $r->errorInfo(),
			);
			#run debug to give developer information if needed.
			$odin->debug->error($this->err);
			return FALSE;
		}

		switch($o["return"])
		{
			default:		#catch-all so this just always works
			case "default":	#Return normal, default way.
				switch($qry_type)
				{
					case "SELECT":
					case "VIEW":
						if(!$key)
							{ $ret	= $r->fetchAll(); }
						else
						{
							$ret	= NULL;
							for($i=0;$i<=$r->rowCount();$i++)
							{
								$row	= $r->fetch();
								$ret[$row[$key]]	= $row;
							}
						}
					break;
				}
				#catch blank values.
				if(empty($ret))
				{
					$ret	= $r->rowCount();
					if($ret===0)
						{ $ret	= TRUE; }
				}
			break;

			case "qry_obj":
				echo"RETYPE IS:";var_dump($o["return"]);echo "<hr />";
				return $r;
			break;

			case "num_rows":
				$ret	= $r->rowCount();
			break;
		}
		return $ret;
	}
}
