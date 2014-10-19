<?php
#CreatedBy;Aaron;13OCT2014;Odin-Framework

//!Quick Note
#None of this code has been tested, since the SQL server I use is down right now, and I'm too lazy to setup a local one right now.
class bolt_qdb
{
	function insert($table,$data,$skip_colin_prefix=FALSE)
	{
		global $odin;
		#build up the prepared update statement
		$insert	= "INSERT INTO `$table` ";
		$fields	= "(";
		$values	= " VALUES(";
		#loop through each field & sort it into its proper place, while at the same time reformatting the array.
		foreach($data as $k=>$v)
		{
			if(!$skip_colin_prefix)
			{
				unset($data[$k]);
				$data[":".$k]	= $v;
			}
			$fields	.= "`$k`,";
			$values	.= ":$k,";
		}
		#trim the trailing comas from fields & values, then finish up the sql statement.
		$insert	.= substr($fields, 0,-1).")".substr($values, 0,-1).");";
		#run the query
		return $odin->sql->qry($insert,$data);
	}

	function update($table,$data,$key,$attempt_insert=NULL)
	{
		#write & run insert SQL query
		#if update fails to actually exist ($key not in $data or field isn't in the db), call insert().
		global $odin;
		#build up the prepared update statement
		$update	= "UPDATE `$table` SET ";
		$where	= "";
		#loop through each field & sort it into its proper place, while at the same time reformatting the array.
		foreach($data as $k=>$v)
		{
			unset($data[$k]);
			$data[":".$k]	= $v;
			if($k==$key)
				{ $where	= " WHERE `$k`=:$k"; }
			else
				{ $update	.= "`$k`=:$k,"; }
		}
		#if we don't have something to check against, return false since nothing else here will work.
		if(empty($where))
			{ return FALSE; }
		if($attempt_insert)
		{
			$sql			= "SELECT * FROM `$table`".$where;
			$update_check	= (int)$odin->sql->qry($sql,array(":$key"=>$data[":$key"]),NULL,array("return"=>"num_rows"));
			if($update_check<1)
				{ return $this->insert($table,$data,TRUE); }
		}
		#trim the trailing coma, then add the where condition & return whatever the query gives us.
		return $odin->sql->qry(substr($update, 0,-1).$where,$data);
	}
	
	function get($table,$opts=NULL)
	{
		global $odin;
		$o		= array(
			"key"	=> NULL,
			"id"	=> NULL,
			"limit"	=> NULL,
		);
		if($opts)
			{ $o	= $odin->array->ow_merge_r($o,$opts); }
		$sql	= "SELECT * FROM ?";
		$tbl	= array($table);
		if($o["key"])
		{
			if($o["id"])
			{
				$sql	.= " WHERE `$key` ";
				if(is_array($o["id"]))
				{
					$sql	.= "IN(".substr(str_repeat("?,",count($o["id"])), 0, -1).")";
					$fields	= $tbl+$o["id"];
				}
				else
				{
					$sql	.= "=?";
					$tbl[]	= $o["id"];
				}
			}
			if($o["limit"])
				{ $sql	.= " LIMIT 0,".(int)$o["limit"]; }
			return $odin->sql->qry($sql,$fields);
		}
		if($o["limit"])
			{ $sql	.= " LIMIT 0,".(int)$o["limit"]; }
		return $odin->sql->qry($sql,$tbl);
	}
}