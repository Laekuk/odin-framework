<?php
#CreatedBy;Aaron;11OCT2014;Odin-Framework
class bolt_sql
{
	var $conn_info;
	var $cur_conn;
	var $conns;
	var $conn_cache;
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
		#try to connect, otherwise error.
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
		#default options for this query.
		$o	= array(
			"return"	=> TRUE,#TRUE will always match the first case in a swtich, which is default. Options for this are: "default","num_rows","qry_obj"
		);
		#if they exist, merge the passed options with the defaults.
		if($opts)
			{ $o	= $odin->array->ow_merge_r($o,$opts); }
		#if there was a database connection error, do not attempt any queries.
		if(isset($this->conn_err[$this->cur_conn]))
			{ return FALSE; }
		#Default $prepared statement boolean to be false.
		$prepared	= FALSE;

		#parse the sql statement and get its type.
		$sql		= ltrim($sql);
		$qry_type	= preg_split("/[\s]+/",$sql,2);
		$qry_type	= $qry_type[0];

		#grab a local function-scope version of the active database connection.
		$c	= $this->conns[$this->cur_conn];
		#enable php errors.
		$c->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		#if there are params, run this as a prepared statement, otherwise run as a normal query-string.
		$r	= $c->prepare($sql);
		try{ $e	= $r->execute((is_array($params)?$params:NULL)); }
		catch(Exception $err){ $e=FALSE; }
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

		#check options to see how we want to return this data
		switch($o["return"])
		{
			default:		#catch-all so this just always works
			case "default":	#Return normal, default way.
				switch($qry_type)
				{
					case "SELECT":
					case "SHOW":
					case "DESCRIBE":
						if(!$key)
							{ $ret	= $r->fetchAll(PDO::FETCH_ASSOC); }
						else
						{
							$ret	= NULL;
							for($i=0;$i<$r->rowCount();$i++)
							{
								$row				= $r->fetch(PDO::FETCH_ASSOC);
								$ret[$row[$key]]	= $row;
							}
						}
					break;
					case 'INSERT':
						$ret	= $c->lastInsertId();
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

			#return the query object
			case "qry_obj":
				return $r;
			break;

			#return a count of the # of rows found (or affected) by this statement.
			case "num_rows":
				$ret	= $r->rowCount();
			break;
		}
		return $ret;
	}
	
	function create_tables($tables=[])
	{
		if(!is_array($tables))
			{ return FALSE; }
		$cur_conn	= $this->cur_conn;
		if(!isset($this->conn_cache))
			{ $this->conn_cache[$cur_conn]			= FALSE; }
		elseif(!isset($this->conn_cache[$cur_conn]))
			{ $this->conn_cache[$cur_conn]	= FALSE; }

		if(!isset($this->conn_cache[$cur_conn]['tables']))
		{
			$this->conn_cache[$cur_conn]['tables']	= [];
			$get_tables_sql	= 'SHOW TABLES IN `'.$this->conn_info[$cur_conn]['params']['dbname'].'`';
			$cur_tables	= $this->qry($get_tables_sql);
			if(is_array($cur_tables))
			{
				foreach($cur_tables as $tbl)
					{ $this->conn_cache[$cur_conn]['tables'][]	= current($tbl); }
			}
		}

		foreach($tables as $table=>$query)
		{
			if(!in_array($table, $this->conn_cache[$cur_conn]['tables']))
			{
				$ret	= $this->qry($query);
				$this->conn_cache[$cur_conn]['tables'][]	= $table;
			}
		}
	}
}
