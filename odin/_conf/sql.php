<?php
#11OCT2014;AaronPeloquin;Odin-Framework
$conf	= (object)array(
	"default_conn"	=> NULL,#"local_mysql",
	"connections"	=> array(
		"generic_local_mysql"		=> array(
			"type"				=> "mysql",
			"params"			=> array("host"=>"localhost","dbname"=>"database"),
			"user"				=> "user",
			"pass"				=> "password",
		),
		"oracle"			=> array(
			"protocol"			=> "oci",
			"params"			=> array("dbname"=>"yoursid"),
			"user"				=> "username",
			"pass"				=> "password",
		),
	),
);